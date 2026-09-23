<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\SurveyInvitationMail;
use App\Models\AuditLog;
use App\Models\Survey;
use App\Models\SurveyInvitation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class InvitationController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $invQuery = SurveyInvitation::with(['survey', 'university']);
        $surveyQuery = Survey::query();

        if ($user && !$user->isSuperAdmin()) {
            $uniId = $user->university_id;
            
            // Only show invitations created by/for this specific university
            $invQuery->where('university_id', $uniId);

            $surveyQuery->where(function ($q) use ($uniId) {
                $q->where(function ($gq) {
                    $gq->whereNull('university_id')
                       ->whereDoesntHave('universities');
                })
                ->orWhere('university_id', $uniId)
                ->orWhereHas('universities', function ($uq) use ($uniId) {
                    $uq->where('universities.id', $uniId);
                });
            });
        }

        $invitations = $invQuery->latest()->paginate(20);
        $surveys = $surveyQuery->get();

        if ($surveys->isEmpty()) {
            $surveys = Survey::all();
        }

        return view('admin.invitations.index', compact('invitations', 'surveys'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $validated = $request->validate([
            'survey_id' => 'required|exists:surveys,id',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'category_code' => 'required|string',
        ]);

        $survey = Survey::findOrFail($validated['survey_id']);

        // Bind invitation to sender university
        $validated['university_id'] = ($user && !$user->isSuperAdmin())
            ? $user->university_id
            : ($survey->university_id ?? $survey->universities()->first()?->id);

        $validated['token'] = Str::random(32);
        $validated['status'] = 'invited';
        $validated['sent_at'] = now();

        $invitation = SurveyInvitation::create($validated);
        AuditLog::log('created_invitation', 'SurveyInvitation', $invitation->id);

        $mailSent = false;
        $mailError = null;

        try {
            Mail::to($invitation->email)->send(new SurveyInvitationMail($invitation));
            $mailSent = true;
        } catch (\Exception $e) {
            Log::error("Failed to send survey invitation email to {$invitation->email}: " . $e->getMessage());
            $mailError = $e->getMessage();
        }

        if ($mailSent) {
            return back()->with('success', "Invitation token created and email sent successfully to {$invitation->email}!");
        }

        return back()->with('warning', "Invitation created, but email sending failed: {$mailError}");
    }

    public function resend($id)
    {
        $invitation = SurveyInvitation::with('survey')->findOrFail($id);

        try {
            Mail::to($invitation->email)->send(new SurveyInvitationMail($invitation));
            $invitation->update(['sent_at' => now()]);
            AuditLog::log('resent_invitation', 'SurveyInvitation', $invitation->id);

            return back()->with('success', "Invitation email resent successfully to {$invitation->email}!");
        } catch (\Exception $e) {
            Log::error("Failed to resend survey invitation email to {$invitation->email}: " . $e->getMessage());

            return back()->with('error', "Failed to send email: " . $e->getMessage());
        }
    }
}
