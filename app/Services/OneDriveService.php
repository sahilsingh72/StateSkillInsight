<?php

namespace App\Services;

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Http\Client\Response;

class OneDriveService
{
    protected $clientId;
    protected $clientSecret;
    protected $tenantId;
    protected $redirectUri;
    protected $scopes;
    protected $tokenFile;

    public function __construct()
    {
        $this->clientId     = config('services.onedrive.client_id');
        $this->clientSecret = config('services.onedrive.client_secret');
        $this->tenantId     = config('services.onedrive.tenant_id');
        $this->redirectUri  = config('services.onedrive.redirect_uri');
        $this->scopes       = 'offline_access Files.ReadWrite User.Read';
        $this->tokenFile    = storage_path('app/onedrive_token.json');
    }

    protected function getAuthority()
    {
        $tenant = $this->tenantId ?: 'common';
        return "https://login.microsoftonline.com/" . ltrim($tenant, '/');
    }

    protected function http()
    {
        $caPath = env('CURL_CA_BUNDLE', 'C:/xampp/php/extras/ssl/cacert.pem');
        if (file_exists($caPath)) {
            return Http::withOptions(['verify' => $caPath]);
        }

        return Http::withOptions([
            'verify' => config('services.onedrive.ssl_verify', !app()->isLocal()),
        ]);
    }

    // Get authorization URL to log in first time
    public function getAuthUrl()
    {
        return rtrim($this->getAuthority(), '/') . "/oauth2/v2.0/authorize?" . http_build_query([
            'client_id'     => $this->clientId,
            'response_type' => 'code',
            'redirect_uri'  => $this->redirectUri,
            'response_mode' => 'query',
            'scope'         => $this->scopes,
        ]);
    }

    // Exchange code for token
    public function getTokenFromCode($code)
    {
        /** @var Response $response */
        $response = $this->http()->asForm()->post(rtrim($this->getAuthority(), '/') . '/oauth2/v2.0/token', [
            'client_id'     => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code'          => $code,
            'redirect_uri'  => $this->redirectUri,
            'grant_type'    => 'authorization_code',
            'scope'         => $this->scopes,
        ]);

        $tokens = $response->json();

        if (!isset($tokens['access_token'])) {
            throw new \Exception("Failed to get token: " . $response->body());
        }

        $tokens['expires_at'] = time() + ($tokens['expires_in'] ?? 3600) - 60;
        file_put_contents($this->tokenFile, json_encode($tokens, JSON_PRETTY_PRINT));

        return $tokens;
    }

    public function isConnected(): bool
    {
        return file_exists($this->tokenFile);
    }

    // Get access token (refresh if expired)
    protected function getAccessToken()
    {
        if (!file_exists($this->tokenFile)) {
            if (request()->expectsJson()) {
                throw new \Exception("OneDrive is not connected. Please authenticate by visiting /onedrive/login first.");
            }
            throw new HttpResponseException(
                Redirect::to(url('/onedrive/login'))
            );
        }

        $tokens = json_decode(file_get_contents($this->tokenFile), true);
        if (!is_array($tokens)) {
            throw new \Exception("Token file is invalid JSON. Re-authenticate to regenerate token file.");
        }

        // If expires_at not present, compute it from expires_in (if available) or force refresh.
        if (!isset($tokens['expires_at'])) {
            if (isset($tokens['expires_in'])) {
                $tokens['expires_at'] = time() + intval($tokens['expires_in']) - 60;
            } else {
                // Force refresh by setting expiry in the past
                $tokens['expires_at'] = 0;
            }
            // persist the updated structure
            file_put_contents($this->tokenFile, json_encode($tokens, JSON_PRETTY_PRINT));
        }

        // If access_token missing or expired -> refresh
        if (!isset($tokens['access_token']) || time() >= intval($tokens['expires_at'])) {
            if (!isset($tokens['refresh_token'])) {
                // nothing to refresh with — must re-authenticate
                throw new \Exception("Refresh token missing. Please re-authenticate using the OAuth flow.");
            }
            /** @var Response $response */
            $response = $this->http()->asForm()->post(rtrim($this->getAuthority(), '/') . '/oauth2/v2.0/token', [
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
                'refresh_token' => $tokens['refresh_token'],
                'grant_type'    => 'refresh_token',
                'scope'         => $this->scopes,
            ]);

            $newTokens = $response->json();

            if (!is_array($newTokens) || isset($newTokens['error'])) {
                // Try to show helpful error
                $msg = is_array($newTokens) && isset($newTokens['error_description'])
                    ? $newTokens['error_description']
                    : $response->body();
                throw new \Exception("Refresh failed: " . $msg);
            }

            // If API did not return a refresh_token, keep old one (some responses omit it)
            if (!isset($newTokens['refresh_token']) && isset($tokens['refresh_token'])) {
                $newTokens['refresh_token'] = $tokens['refresh_token'];
            }

            // set expires_at and persist
            $newTokens['expires_at'] = time() + ($newTokens['expires_in'] ?? 3600) - 60;
            file_put_contents($this->tokenFile, json_encode($newTokens, JSON_PRETTY_PRINT));

            return $newTokens['access_token'];
        }

        // token is valid
        return $tokens['access_token'];
    }
    public function uploadDirect($file, $folder = "MyMedia", $customFilename = null)
    {
        $accessToken = $this->getAccessToken();

        if (is_string($file)) {
            $filePath = $file;
            $filename = $customFilename ?? basename($filePath);
            $mimeType = function_exists('mime_content_type') ? (@mime_content_type($filePath) ?: 'application/octet-stream') : 'application/octet-stream';
            $stream   = fopen($filePath, 'r');
        } else {
            $filename = $customFilename ?? $file->getClientOriginalName();
            $mimeType = $file->getMimeType() ?? 'application/octet-stream';
            $stream   = fopen($file->getRealPath(), 'r');
        }

        $folder = trim($folder, '/');
        $onedrivePath = "{$folder}/{$filename}";

        // URL-encode each path segment so spaces/special chars work in Graph API URLs
        $encodedPath = implode('/', array_map('rawurlencode', explode('/', $onedrivePath)));
        $uploadUrl = "https://graph.microsoft.com/v1.0/me/drive/root:/{$encodedPath}:/content";

        /** @var Response $response */
        $response = $this->http()->withHeaders([
            'Authorization' => "Bearer {$accessToken}",
            'Content-Type'  => $mimeType,
        ])
            ->timeout(120)
            ->send('PUT', $uploadUrl, [
                'body' => $stream,
            ]);

        fclose($stream);

        if (!$response->successful()) {
            throw new \Exception("OneDrive upload failed: " . $response->body());
        }

        // Create shareable link
        /** @var Response $linkResponse */
        $linkResponse = $this->http()->withHeaders([
            'Authorization' => "Bearer {$accessToken}",
            'Content-Type' => 'application/json',
        ])->post("https://graph.microsoft.com/v1.0/me/drive/root:/{$encodedPath}:/createLink", [
            'type' => 'view',
            'scope' => 'anonymous',
        ]);

        $linkData = $linkResponse->json();
        $shareableUrl = $linkData['link']['webUrl'] ?? null;

        return [
            'path' => $onedrivePath,
            'url'  => $shareableUrl,
        ];
    }
    public function renameFolder($folderPath, $newName)
    {
        $info = $this->getFileInfo($folderPath);

        $folderId = $info['id'] ?? null;
        if (!$folderId) {
            throw new \Exception("Folder not found for rename.");
        }

        $accessToken = $this->getAccessToken();
        /** @var Response $response */
        $response = $this->http()->withToken($accessToken)
            ->patch("https://graph.microsoft.com/v1.0/me/drive/items/{$folderId}", [
                "name" => $newName
            ]);

        return $response->json();
    }

    public function getFileInfo($path)
    {
        $accessToken = $this->getAccessToken();

        // URL-encode each segment so spaces/special chars work correctly
        $encodedPath = implode('/', array_map('rawurlencode', explode('/', $path)));

        /** @var Response $response */
        $response = $this->http()->withToken($accessToken)
            ->get("https://graph.microsoft.com/v1.0/me/drive/root:/{$encodedPath}");

        if ($response->successful()) {
            return $response->json();
        }

        if ($response->status() == 404) {
            return [
                'status' => false,
                'message' => "The requested file does not exist on OneDrive.",
            ];
        }

        // Any other OneDrive/Graph API error
        return [
            'status' => false,
            'message' => "Unable to fetch file info from OneDrive. Please try again later.",
            'error' => $response->json(),
        ];
    }
    public function deleteFile($path)
    {
        $accessToken = $this->getAccessToken();

        // URL-encode each segment so spaces/special chars work correctly
        $encodedPath = implode('/', array_map('rawurlencode', explode('/', $path)));

        /** @var Response $response */
        $response = $this->http()->withToken($accessToken)
            ->delete("https://graph.microsoft.com/v1.0/me/drive/root:/{$encodedPath}");

        if ($response->successful()) {
            return true;
        }

        throw new \Exception('Failed to delete file from OneDrive: ' . $response->body());
    }

    /**
     * Move any locally stored voice recordings to OneDrive and delete them from local disk.
     */
    public function syncPendingVoiceResponses(): array
    {
        if (!$this->isConnected()) {
            return [
                'success' => false,
                'message' => 'OneDrive is not authenticated yet.',
                'synced'  => 0,
                'failed'  => 0,
            ];
        }

        $pending = \App\Models\VoiceResponse::where('storage_driver', 'local')
            ->with('response.respondentSurvey')
            ->get();

        $synced = 0;
        $failed = 0;

        foreach ($pending as $voice) {
            $localRelativePath = str_replace('local:', '', $voice->file_path);

            if (!\Illuminate\Support\Facades\Storage::disk('public')->exists($localRelativePath)) {
                $voice->update([
                    'sync_error' => 'Local file does not exist on disk: ' . $localRelativePath,
                ]);
                continue;
            }

            $absolutePath = \Illuminate\Support\Facades\Storage::disk('public')->path($localRelativePath);
            $respondentId = $voice->response?->respondentSurvey?->respondent_id ?? 'general';
            $folder = "Surveygrid/{$respondentId}";
            $filename = basename($localRelativePath);

            try {
                $uploadResult = $this->uploadDirect($absolutePath, $folder, $filename);

                $voice->update([
                    'storage_driver' => 'onedrive',
                    'file_path'      => $uploadResult['path'],
                    'sync_error'     => null,
                ]);

                // Delete local file now that it is safely stored in OneDrive
                \Illuminate\Support\Facades\Storage::disk('public')->delete($localRelativePath);

                // Clean up empty directory if empty
                $dir = dirname($localRelativePath);
                if ($dir !== '.' && empty(\Illuminate\Support\Facades\Storage::disk('public')->files($dir))) {
                    \Illuminate\Support\Facades\Storage::disk('public')->deleteDirectory($dir);
                }

                $synced++;
            } catch (\Throwable $e) {
                $failed++;
                $voice->update([
                    'sync_error' => $e->getMessage(),
                ]);
                \Illuminate\Support\Facades\Log::warning("Failed to sync voice response ID {$voice->id} to OneDrive: " . $e->getMessage());
            }
        }

        return [
            'success' => true,
            'synced'  => $synced,
            'failed'  => $failed,
        ];
    }
}
