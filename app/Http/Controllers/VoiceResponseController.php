<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Respondent;
use App\Models\RespondentSurvey;
use App\Models\Response;
use App\Models\VoiceResponse;
use App\Services\OneDriveService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VoiceResponseController extends Controller
{
    /**
     * Store recorded audio file from browser MediaRecorder API.
     */
    public function upload(Request $request, string $token, OneDriveService $oneDrive)
    {
        $request->validate([
            'question_id' => 'required|exists:questions,id',
            'audio_file' => 'required|file|mimes:webm,mp3,wav,ogg,m4a|max:20480', // 20MB limit
            'duration' => 'nullable|numeric',
        ]);

        $respondent = Respondent::where('token', $token)->firstOrFail();
        $respondentSurvey = RespondentSurvey::where('respondent_id', $respondent->id)->firstOrFail();
        $questionId = $request->question_id;

        $file = $request->file('audio_file');
        $extension = $file->getClientOriginalExtension() ?: ($file->guessExtension() ?: 'webm');
        $fileName = 'voice_' . $respondent->id . '_' . $questionId . '_' . time() . '.' . $extension;

        $filePath = null;
        $fileUrl = null;

        $folder = "Surveygrid/{$respondent->id}";
        $driver = 'onedrive';
        $syncError = null;

        // 1. Upload to OneDrive if authenticated
        if ($oneDrive->isConnected()) {
            try {
                $uploadResult = $oneDrive->uploadDirect($file, $folder, $fileName);
                $filePath = $uploadResult['path'];
                $fileUrl = route('admin.onedrive.preview', ['path' => $filePath, 'filename' => 'voice_' . $respondent->id . '_' . $questionId]);
                $driver = 'onedrive';
            } catch (\Throwable $e) {
                $syncError = $e->getMessage();
                Log::warning('OneDrive upload failed, falling back to local storage: ' . $syncError);
            }
        } else {
            $syncError = 'OneDrive is not authenticated yet.';
        }

        // 2. Fallback to local storage disk if OneDrive is not connected or upload failed
        if (!$filePath) {
            $filePath = $file->storeAs($folder, $fileName, 'public');
            $fileUrl = Storage::disk('public')->url($filePath);
            $driver = 'local';
        }

        $responseRecord = Response::updateOrCreate(
            [
                'respondent_survey_id' => $respondentSurvey->id,
                'question_id' => $questionId,
            ],
            [
                'text_value' => '[Voice Recording Uploaded: ' . $fileName . ']',
                'score' => 85.00,
            ]
        );

        $voiceRecord = VoiceResponse::updateOrCreate(
            ['response_id' => $responseRecord->id],
            [
                'file_path' => $filePath,
                'storage_driver' => $driver,
                'sync_error' => $syncError,
                'duration_seconds' => (int)$request->duration,
                'mime_type' => $file->getClientMimeType() ?? 'audio/webm',
                'file_size' => $file->getSize(),
                'thematic_tags' => ['curriculum_reform', 'voice_feedback'],
            ]
        );

        // 3. If OneDrive upload succeeded, auto-sync any prior pending files in the background
        if ($driver === 'onedrive') {
            try {
                $oneDrive->syncPendingVoiceResponses();
            } catch (\Throwable $e) {
                // Background sync failure should not interrupt the respondent
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Voice recording uploaded successfully!',
            'file_url' => $fileUrl,
            'voice_id' => $voiceRecord->id,
        ]);
    }
}
