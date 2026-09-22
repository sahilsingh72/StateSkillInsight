<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VoiceResponse;
use Illuminate\Http\Request;

class VoiceManagerController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = VoiceResponse::with(['response.question', 'response.respondentSurvey.respondent']);

        if ($user && !$user->isSuperAdmin()) {
            $uniId = $user->university_id;
            $query->whereHas('response.respondentSurvey.respondent', function ($q) use ($uniId) {
                $q->where('university_id', $uniId);
            });
        }

        $voiceResponses = $query->latest()->paginate(15);

        return view('admin.responses.voice', compact('voiceResponses'));
    }
}
