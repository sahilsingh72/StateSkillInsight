<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VoiceResponse;
use Illuminate\Http\Request;

class VoiceManagerController extends Controller
{
    public function index(Request $request)
    {
        $query = VoiceResponse::with(['response.question', 'response.respondentSurvey.respondent']);
        $voiceResponses = $query->latest()->paginate(15);

        return view('admin.responses.voice', compact('voiceResponses'));
    }
}
