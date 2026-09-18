<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Survey;
use App\Models\SurveyInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InvitationController extends Controller
{
    public function index()
    {
        $invitations = SurveyInvitation::with('survey')->latest()->paginate(20);
        $surveys = Survey::all();

        return view('admin.invitations.index', compact('invitations', 'surveys'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'survey_id' => 'required|exists:surveys,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'category_code' => 'required|string',
        ]);

        $validated['token'] = Str::random(32);
        $validated['status'] = 'invited';
        $validated['sent_at'] = now();

        $invitation = SurveyInvitation::create($validated);
        AuditLog::log('created_invitation', 'SurveyInvitation', $invitation->id);

        return back()->with('success', 'Invitation link created successfully!');
    }
}
