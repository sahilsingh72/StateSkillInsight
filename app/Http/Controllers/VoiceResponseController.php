<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Respondent;
use App\Models\RespondentSurvey;
use App\Models\Response;
use App\Models\VoiceResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class VoiceResponseController extends Controller
{
    /**
     * Store recorded audio file from browser MediaRecorder API.
     */
    public function upload(Request $request, string $token)
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
        $fileName = 'voice_' . $respondent->id . '_' . $questionId . '_' . time() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs('voice_responses', $fileName, 'public');

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
                'duration_seconds' => (int)$request->duration,
                'mime_type' => $file->getClientMimeType() ?? 'audio/webm',
                'file_size' => $file->getSize(),
                'thematic_tags' => ['curriculum_reform', 'voice_feedback'],
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Voice recording uploaded successfully!',
            'file_url' => Storage::url($filePath),
            'voice_id' => $voiceRecord->id,
        ]);
    }
}
