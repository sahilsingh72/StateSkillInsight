<?php
 
namespace App\Http\Controllers;
 
use Illuminate\Http\Request;
use App\Services\OneDriveService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
 
class OneDriveController extends Controller
{
    protected $oneDrive;
 
    public function __construct(OneDriveService $oneDrive)
    {
        $this->oneDrive = $oneDrive;
    }
    public function redirectToProvider(OneDriveService $oneDrive)
    {
        return redirect($oneDrive->getAuthUrl());
    }
 
    public function handleCallback(Request $request, OneDriveService $oneDrive)
    {
        if ($request->has('error')) {
            return redirect('/')
                ->with('error', $request->get('error_description'));
        }
 
        if (!$request->has('code')) {
            return redirect('/')
                ->with('error', 'Authorization code missing. Please login again.');
        }
 
        $oneDrive->getTokenFromCode($request->get('code'));

        // Auto move any pending local voice recordings to OneDrive and delete them locally
        $syncResult = $oneDrive->syncPendingVoiceResponses();
        $statusMsg = '✅ Tokens saved, you can now upload files!';
        if (!empty($syncResult['synced']) && $syncResult['synced'] > 0) {
            $statusMsg .= " ({$syncResult['synced']} pending local voice recording(s) moved to OneDrive)";
        }

        return redirect()->route('admin.dashboard')
            ->with('status', $statusMsg);
    }
    public function previewFiles(Request $request)
    {
        $path = $request->query('path');
        if (empty($path)) {
            return response('Path is required', 400);
        }
        $path = preg_replace('#/+#', '/', $path);
        $filename = $request->query('filename', 'document');
        $filename = preg_replace('/[^A-Za-z0-9_\-]/', '_', $filename);
       
        $downloadUrl = Cache::remember("onedrive_download_" . md5($path), 300, function () use ($path) {
            $fileInfo = $this->oneDrive->getFileInfo($path);
            return $fileInfo['@microsoft.graph.downloadUrl'] ?? null;
        });
 
        if (!$downloadUrl) {
            return response('Download URL not found', 404);
        }
        // Fetch the file content
        $caPath = env('CURL_CA_BUNDLE', 'C:/xampp/php/extras/ssl/cacert.pem');
        $verify = file_exists($caPath)
            ? $caPath
            : config('services.onedrive.ssl_verify', !app()->isLocal());
        $response = Http::withOptions(['verify' => $verify])->get($downloadUrl);
        $contentType = $response->header('Content-Type', 'application/octet-stream');
 
        // Infer mime type from extension if stream is generic
        $fileExt = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($contentType === 'application/octet-stream' && !empty($fileExt)) {
            $contentType = match ($fileExt) {
                'webm' => 'audio/webm',
                'mp3' => 'audio/mpeg',
                'wav' => 'audio/wav',
                'ogg' => 'audio/ogg',
                'm4a' => 'audio/m4a',
                'pdf' => 'application/pdf',
                'jpg', 'jpeg' => 'image/jpeg',
                'png' => 'image/png',
                'mp4' => 'video/mp4',
                default => $contentType,
            };
        }

        // Voice recordings stored in Surveygrid or VoiceResponses should be streamed as audio/webm
        if (str_contains($path, 'Surveygrid') || str_contains($path, 'VoiceResponses') || str_contains($path, 'voice_')) {
            if ($fileExt === 'webm' || str_contains($contentType, 'webm')) {
                $contentType = 'audio/webm';
            }
        }

        // Determine extension and validate supported type
        $extension = null;
        if (str_contains($contentType, 'pdf')) {
            $extension = 'pdf';
        } elseif (str_contains($contentType, 'image')) {
            $extension = match (true) {
                str_contains($contentType, 'jpeg') => 'jpg',
                str_contains($contentType, 'png')  => 'png',
                str_contains($contentType, 'webp') => 'webp',
                default => 'jpg',
            };
        } elseif (str_contains($contentType, 'audio')) {
            $extension = match (true) {
                str_contains($contentType, 'webm') => 'webm',
                str_contains($contentType, 'mp3') || str_contains($contentType, 'mpeg') => 'mp3',
                str_contains($contentType, 'wav')  => 'wav',
                str_contains($contentType, 'ogg')  => 'ogg',
                str_contains($contentType, 'm4a')  => 'm4a',
                default => (!empty($fileExt) ? $fileExt : 'webm'),
            };
        } elseif (str_contains($contentType, 'video')) {
            $extension = match (true) {
                str_contains($contentType, 'mp4')  => 'mp4',
                str_contains($contentType, 'webm') => 'webm',
                str_contains($contentType, 'ogg')  => 'ogg',
                str_contains($contentType, 'mov')  => 'mov',
                default => 'mp4',
            };
        } else {
            return response('Unsupported file type', 415);
        }

        $body = $response->body();
        $size = strlen($body);

        // Support HTTP Range requests (crucial for browser audio/video seeking and duration detection)
        $range = $request->header('Range');
        if ($range && preg_match('/bytes=(\d+)-(\d+)?/', $range, $matches)) {
            $start = intval($matches[1]);
            $end = !empty($matches[2]) ? intval($matches[2]) : ($size - 1);
            if ($end >= $size) {
                $end = $size - 1;
            }
            $length = $end - $start + 1;
            $chunk = substr($body, $start, $length);

            return response($chunk, 206)
                ->header('Content-Type', $contentType)
                ->header('Content-Range', "bytes {$start}-{$end}/{$size}")
                ->header('Content-Length', (string) $length)
                ->header('Accept-Ranges', 'bytes')
                ->header('Content-Disposition', 'inline; filename="' . $filename . '.' . $extension . '"');
        }

        return response($body, 200)
            ->header('Content-Type', $contentType)
            ->header('Accept-Ranges', 'bytes')
            ->header('Content-Length', (string) $size)
            ->header('Content-Disposition', 'inline; filename="' . $filename . '.' . $extension . '"');
    }
   
}
 
 