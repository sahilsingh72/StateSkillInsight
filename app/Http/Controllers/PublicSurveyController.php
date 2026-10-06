<?php

namespace App\Http\Controllers;

use App\Jobs\SendIncompleteSurveyReminderJob;
use App\Mail\SurveyCompletedMail;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\Respondent;
use App\Models\RespondentSurvey;
use App\Models\Response;
use App\Models\Survey;
use App\Models\SurveyCategory;
use App\Models\SurveyInvitation;
use App\Models\SurveySection;
use App\Models\University;
use App\Services\InterventionEngine;
use App\Services\ScoringService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class PublicSurveyController extends Controller
{
    /**
     * Public landing page displaying university branding, purpose, and 4 categories.
     */
    public function landing(Request $request)
    {
        $survey = Survey::where('status', 'published')->first() ?? Survey::first();
        
        if ($survey && !$survey->isAcceptingResponses()) {
            return view('survey.closed', compact('survey'));
        }

        $categories = $survey ? $survey->categories()->where('is_active', true)->orderBy('order')->get() : collect();

        return view('survey.landing', compact('survey', 'categories'));
    }

    /**
     * Respondent Registration & Category Initialization.
     */
    public function registerCategory(Request $request, string $categoryCode)
    {
        $survey = Survey::where('status', 'published')->first() ?? Survey::first();

        if (!$survey || !$survey->isAcceptingResponses()) {
            return view('survey.closed', compact('survey'));
        }

        $category = SurveyCategory::where('survey_id', $survey->id)->where('code', $categoryCode)->firstOrFail();
        $institutions = University::with(['parent', 'colleges' => function ($q) {
            $q->where('is_active', true)->orderBy('name');
        }])->where('is_active', true)->orderBy('type')->orderBy('name')->get();

        return view('survey.register', compact('survey', 'category', 'institutions'));
    }

    /**
     * Start Survey Session (stores respondent and returns secure token link).
     */
    public function startSurvey(Request $request)
    {
        $survey = Survey::where('status', 'published')->first() ?? Survey::first();

        if (!$survey || !$survey->isAcceptingResponses()) {
            return view('survey.closed', compact('survey'));
        }

        $request->validate([
            'university_id' => 'nullable|exists:universities,id',
            'institution_id' => 'nullable|exists:universities,id',
            'college_id' => 'nullable|string',
            'category_code' => 'required|string',
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'mobile' => 'nullable|string|max:20',
            'programme' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'graduation_year' => 'nullable|string|max:10',
            'consent_given' => 'required|accepted',
        ]);

        $baseInstId = $request->input('institution_id') ?? $request->input('university_id');
        if (!$baseInstId) {
            return back()->withInput()->withErrors(['institution_id' => 'Please select your university or institution.']);
        }

        // Determine final university_id:
        // If an affiliated college was chosen and is not 'main_campus', use that college ID
        $collegeId = $request->input('college_id');
        $finalUniversityId = ($collegeId && $collegeId !== 'main_campus' && is_numeric($collegeId))
            ? (int)$collegeId
            : (int)$baseInstId;

        // Avoid multiple survey responses with the same email
        if ($request->filled('email')) {
            $inputEmail = strtolower(trim($request->email));
            $existingSurvey = RespondentSurvey::where('survey_id', $survey->id)
                ->whereHas('respondent', function ($q) use ($inputEmail) {
                    $q->whereRaw('LOWER(email) = ?', [$inputEmail]);
                })
                ->with(['respondent', 'category', 'currentSection'])
                ->first();

            if ($existingSurvey) {
                if ($existingSurvey->status === 'completed') {
                    return back()->withInput()->withErrors([
                        'email' => "{$request->email} has already completed the survey."
                    ]);
                } else {
                    $resumeToken = $existingSurvey->respondent->token;
                    $targetSectionId = $existingSurvey->current_section_id;
                    $resumeUrl = route('survey.take', array_filter([
                        'token' => $resumeToken,
                        'section' => $targetSectionId,
                    ]));

                    return back()->withInput()->with('resume_data', [
                        'url' => $resumeUrl,
                        'name' => $existingSurvey->respondent->name,
                        'email' => $existingSurvey->respondent->email,
                        'category' => $existingSurvey->category->name ?? $existingSurvey->category_code,
                        'percentage' => (float)$existingSurvey->completion_percentage,
                        'section_title' => $existingSurvey->currentSection?->title ?? 'Remaining Questions',
                    ])->withErrors([
                        'email' => "An incomplete survey session is found for you ({$request->email}), please click on continue to complete the survey from where you left off."
                    ]);
                }
            }
        }

        $category = SurveyCategory::where('survey_id', $survey->id)->where('code', $request->category_code)->firstOrFail();

        $token = Str::random(32);

        $programme = $request->input('programme');
        if ($programme === 'Other' && $request->filled('other_programme')) {
            $programme = trim($request->input('other_programme'));
        }

        $department = $request->input('department');
        if ($department === 'Other' && $request->filled('other_department')) {
            $department = trim($request->input('other_department'));
        }

        $respondent = Respondent::create([
            'university_id' => $finalUniversityId,
            'token' => $token,
            'name' => $request->name,
            'email' => $request->email,
            'mobile' => $request->mobile,
            'gender' => $request->gender,
            'programme' => $programme,
            'department' => $department,
            'graduation_year' => $request->graduation_year,
            'category_code' => $category->code,
            'employment_status' => $request->employment_status,
            'consent_given' => true,
            'consent_at' => now(),
        ]);

        $firstSection = $this->getSectionsForRespondent($category, $respondent->university_id)->first();

        $respondentSurvey = RespondentSurvey::create([
            'respondent_id' => $respondent->id,
            'survey_id' => $survey->id,
            'category_id' => $category->id,
            'status' => 'in_progress',
            'current_section_id' => $firstSection ? $firstSection->id : null,
            'completion_percentage' => 0.00,
            'last_saved_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Dispatch delayed 15-minute incomplete survey reminder job
        if (!empty($respondent->email) && filter_var($respondent->email, FILTER_VALIDATE_EMAIL)) {
            try {
                SendIncompleteSurveyReminderJob::dispatch($respondentSurvey->id)->delay(now()->addMinutes(15));
            } catch (\Throwable $e) {
                Log::warning("Could not dispatch incomplete survey reminder job: " . $e->getMessage());
            }
        }

        return redirect()->route('survey.take', ['token' => $token]);
    }

    /**
     * Start via secure token link (e.g. from email invitation).
     */
    public function startByToken(Request $request, string $token)
    {
        // 1. Check if Respondent session already exists for this token
        $respondent = Respondent::where('token', $token)->first();

        if ($respondent) {
            return redirect()->route('survey.take', ['token' => $token]);
        }

        // 2. Check if token belongs to an unfulfilled SurveyInvitation
        $invitation = SurveyInvitation::where('token', $token)->first();

        if ($invitation) {
            // Update invitation status
            $invitation->update([
                'status' => 'opened',
                'opened_at' => $invitation->opened_at ?? now(),
            ]);

            $survey = Survey::find($invitation->survey_id) ?? Survey::where('status', 'published')->first() ?? Survey::first();

            if (!$survey) {
                abort(404, 'Associated survey not found.');
            }

            // Find matching category or first available category in survey
            $category = SurveyCategory::where('survey_id', $survey->id)
                ->where('code', $invitation->category_code)
                ->first();

            if (!$category) {
                $category = $survey->categories()->orderBy('order')->first();
            }

            if (!$category) {
                abort(404, 'No active category found for this survey.');
            }

            $institutions = University::where('is_active', true)->orderBy('type')->orderBy('name')->get();
            $selectedUniId = $invitation->university_id ?? $survey->university_id ?? $survey->universities()->first()?->id;

            return view('survey.register_invitation', compact('invitation', 'survey', 'category', 'institutions', 'selectedUniId'));
        }

        // 3. If token is invalid / not found anywhere
        abort(404, 'Invalid or expired survey invitation token.');
    }

    /**
     * Process remaining profile details submitted by invited respondent.
     */
    public function completeInvitationProfile(Request $request, string $token)
    {
        $invitation = SurveyInvitation::where('token', $token)->firstOrFail();

        $request->validate([
            'university_id' => 'required|exists:universities,id',
            'consent_given' => 'required|accepted',
        ]);

        $survey = Survey::find($invitation->survey_id) ?? Survey::where('status', 'published')->first() ?? Survey::first();

        if (!$survey || !$survey->isAcceptingResponses()) {
            return view('survey.closed', compact('survey'));
        }

        if (!empty($invitation->email)) {
            $invEmail = strtolower(trim($invitation->email));
            $existingSurvey = RespondentSurvey::where('survey_id', $survey->id)
                ->where('status', 'completed')
                ->whereHas('respondent', function ($q) use ($invEmail) {
                    $q->whereRaw('LOWER(email) = ?', [$invEmail]);
                })
                ->first();

            if ($existingSurvey) {
                return redirect()->route('survey.landing')->with('error', "The email address ({$invitation->email}) associated with this invitation has already completed this survey.");
            }
        }

        $category = SurveyCategory::where('survey_id', $survey->id)
            ->where('code', $invitation->category_code)
            ->first() ?? $survey->categories()->orderBy('order')->first();

        $programme = $request->input('programme');
        if ($programme === 'Other' && $request->filled('other_programme')) {
            $programme = trim($request->input('other_programme'));
        } elseif (!$programme) {
            $programme = $invitation->programme;
        }

        $department = $request->input('department');
        if ($department === 'Other' && $request->filled('other_department')) {
            $department = trim($request->input('other_department'));
        } elseif (!$department) {
            $department = $invitation->department;
        }

        $respondent = Respondent::create([
            'university_id' => $request->university_id,
            'token' => $token,
            'name' => $invitation->name,
            'email' => $invitation->email,
            'mobile' => $request->mobile ?? $invitation->mobile,
            'gender' => $request->gender,
            'programme' => $programme,
            'department' => $department,
            'graduation_year' => $request->graduation_year,
            'category_code' => $category->code,
            'employment_status' => $request->employment_status,
            'consent_given' => true,
            'consent_at' => now(),
        ]);

        $firstSection = $this->getSectionsForRespondent($category, $respondent->university_id)->first();

        $respondentSurvey = RespondentSurvey::create([
            'respondent_id' => $respondent->id,
            'survey_id' => $survey->id,
            'category_id' => $category->id,
            'status' => 'in_progress',
            'current_section_id' => $firstSection ? $firstSection->id : null,
            'completion_percentage' => 0.00,
            'last_saved_at' => now(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        // Dispatch delayed 15-minute incomplete survey reminder job
        if (!empty($respondent->email) && filter_var($respondent->email, FILTER_VALIDATE_EMAIL)) {
            try {
                SendIncompleteSurveyReminderJob::dispatch($respondentSurvey->id)->delay(now()->addMinutes(15));
            } catch (\Throwable $e) {
                Log::warning("Could not dispatch incomplete survey reminder job: " . $e->getMessage());
            }
        }

        $invitation->update(['status' => 'started']);

        return redirect()->route('survey.take', ['token' => $token]);
    }

    /**
     * Main Dynamic Questionnaire View with multi-section layout and live branching.
     */
    public function takeSurvey(Request $request, string $token)
    {
        $respondent = Respondent::where('token', $token)->firstOrFail();
        $respondentSurvey = RespondentSurvey::where('respondent_id', $respondent->id)->firstOrFail();
        $survey = $respondentSurvey->survey;
        $category = $respondentSurvey->category;
        $uniId = $respondent->university_id;

        // Fetch sections & questions assigned to respondent's university or common/global questions
        $sections = $this->getSectionsForRespondent($category, $uniId);

        $university = $respondent->university ?? $survey->university;

        // Current section
        $currentSectionId = $request->query('section') ?? ($respondentSurvey->current_section_id ?? $sections->first()?->id);
        $currentSection = $sections->firstWhere('id', $currentSectionId) ?? $sections->first();

        // Existing responses map
        $existingResponses = Response::where('respondent_survey_id', $respondentSurvey->id)
            ->with('voiceResponse')
            ->get()
            ->keyBy('question_id');

        $locale = session('survey_locale', 'en');

        return view('survey.questionnaire', compact(
            'university',
            'survey',
            'category',
            'sections',
            'currentSection',
            'respondent',
            'respondentSurvey',
            'existingResponses',
            'locale'
        ));
    }

    /**
     * AJAX Auto-Save Endpoint.
     */
    public function autoSave(Request $request, string $token)
    {
        $respondent = Respondent::where('token', $token)->firstOrFail();
        $respondentSurvey = RespondentSurvey::where('respondent_id', $respondent->id)->firstOrFail();

        $answers = $request->input('answers', []);
        $currentSectionId = $request->input('section_id');

        foreach ($answers as $qId => $val) {
            $question = Question::find($qId);
            if (!$question) continue;

            $score = 50; // default score base
            $textVal = null;
            $jsonVal = null;

            if (is_array($val)) {
                $jsonVal = $val;
                $textVal = implode(', ', $val);
            } else {
                $textVal = (string)$val;
                $opt = QuestionOption::where('question_id', $qId)->where('value', $val)->first();
                if ($opt) {
                    $score = $opt->score;
                }
            }

            Response::updateOrCreate(
                [
                    'respondent_survey_id' => $respondentSurvey->id,
                    'question_id' => $qId,
                ],
                [
                    'text_value' => $textVal,
                    'json_value' => $jsonVal,
                    'score' => $score,
                ]
            );
        }

        // Update progress
        $sections = $this->getSectionsForRespondent($respondentSurvey->category, $respondent->university_id);
        $totalQuestions = $sections->pluck('questions')->flatten()->count();
        $answeredCount = Response::where('respondent_survey_id', $respondentSurvey->id)->count();
        $percentage = ($totalQuestions > 0) ? min(100, round(($answeredCount / $totalQuestions) * 100, 1)) : 0;

        $respondentSurvey->update([
            'current_section_id' => $currentSectionId,
            'completion_percentage' => $percentage,
            'last_saved_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Progress auto-saved successfully.',
            'completion_percentage' => $percentage,
            'last_saved' => now()->format('H:i:s'),
        ]);
    }

    /**
     * Review Answers before final submission.
     */
    public function review(string $token)
    {
        $respondent = Respondent::where('token', $token)->firstOrFail();
        $respondentSurvey = RespondentSurvey::where('respondent_id', $respondent->id)->firstOrFail();
        $survey = $respondentSurvey->survey;
        $category = $respondentSurvey->category;
        $uniId = $respondent->university_id;
        $university = $respondent->university ?? $survey->university;

        $sections = $this->getSectionsForRespondent($category, $uniId);

        $responses = Response::where('respondent_survey_id', $respondentSurvey->id)->get()->keyBy('question_id');

        return view('survey.review', compact('university', 'survey', 'category', 'respondent', 'respondentSurvey', 'sections', 'responses'));
    }

    /**
     * Submit Survey & Trigger Scoring + Intervention Engine.
     */
    public function submit(Request $request, string $token, ScoringService $scoringService, InterventionEngine $interventionEngine)
    {
        $respondent = Respondent::where('token', $token)->firstOrFail();
        $respondentSurvey = RespondentSurvey::where('respondent_id', $respondent->id)->firstOrFail();
        $uniId = $respondent->university_id;

        // Verify all required questions have answers
        $category = $respondentSurvey->category;
        $sections = $this->getSectionsForRespondent($category, $uniId, true);

        $requiredQuestions = $sections->pluck('questions')->flatten();
        $responses = Response::where('respondent_survey_id', $respondentSurvey->id)->get()->keyBy('question_id');

        $missingQuestions = [];
        foreach ($requiredQuestions as $rq) {
            $resp = $responses->get($rq->id);
            if (!$resp || (trim($resp->text_value ?? '') === '' && empty($resp->json_value))) {
                $missingQuestions[] = $rq;
            }
        }

        if (count($missingQuestions) > 0) {
            $firstMissingSecId = $missingQuestions[0]->section_id;
            return redirect()->route('survey.take', ['token' => $token, 'section' => $firstMissingSecId])
                ->with('error', 'Please answer all required questions marked with * before submitting the survey.');
        }

        $respondentSurvey->update([
            'status' => 'completed',
            'completion_percentage' => 100.00,
            'completed_at' => now(),
        ]);

        // Send automatic 100% completion email to registered email
        if (!empty($respondent->email) && filter_var($respondent->email, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::to($respondent->email)->send(new SurveyCompletedMail($respondentSurvey));
                Log::info("Survey completion email sent to: {$respondent->email} (RespondentSurvey ID: {$respondentSurvey->id})");
            } catch (\Throwable $e) {
                Log::error("Failed sending survey completion email to {$respondent->email}: " . $e->getMessage());
            }
        }

        // Calculate scores & composite indexes
        $scores = $scoringService->calculateAndPersistScores($respondentSurvey);

        // Assign interventions
        $interventions = $interventionEngine->evaluateAndAssign($respondentSurvey, $scores);

        return redirect()->route('survey.thankyou', ['token' => $token]);
    }

    /**
     * Thank You & Confirmation Page.
     */
    public function thankYou(string $token)
    {
        $respondent = Respondent::where('token', $token)->firstOrFail();
        $respondentSurvey = RespondentSurvey::where('respondent_id', $respondent->id)->firstOrFail();
        $university = $respondentSurvey->survey->university;

        return view('survey.thankyou', compact('university', 'respondent', 'respondentSurvey'));
    }

    /**
     * Switch Language (Locale).
     */
    public function setLocale(Request $request, string $locale)
    {
        if (in_array($locale, ['en', 'hi', 'or'])) {
            session(['survey_locale' => $locale]);
        }
        return back();
    }

    /**
     * API Endpoint: Fetch available academic programmes for a selected university.
     */
    public function getUniversityProgrammes(University $university)
    {
        return response()->json([
            'success' => true,
            'university_id' => $university->id,
            'university_name' => $university->name,
            'programmes' => $university->available_programmes,
        ]);
    }

    /**
     * API Endpoint: Fetch available departments/disciplines for a selected university and programme.
     */
    public function getUniversityDepartments(Request $request, University $university)
    {
        $programme = $request->query('programme');
        $departments = $university->getDepartmentsForProgramme($programme);

        return response()->json([
            'success' => true,
            'university_id' => $university->id,
            'programme' => $programme,
            'departments' => $departments,
        ]);
    }

    /**
     * API Endpoint: Fetch affiliated colleges for a selected university.
     */
    public function getUniversityColleges(University $university)
    {
        $colleges = $university->colleges()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'short_name']);

        return response()->json([
            'success' => true,
            'has_colleges' => $colleges->isNotEmpty(),
            'university_id' => $university->id,
            'university_name' => $university->name,
            'colleges' => $colleges,
        ]);
    }

    /**
     * Get all applicable sections and questions for a respondent based on their university.
     */
    private function getSectionsForRespondent($category, ?int $uniId, bool $requiredOnly = false)
    {
        if (!$category) {
            return collect();
        }

        return $category->sections()
            ->where(function ($q) use ($uniId) {
                // 1. Common / Global section
                $q->where(function ($gq) {
                    $gq->whereNull('university_id')->whereDoesntHave('universities');
                });

                // 2. Section explicitly assigned to this university
                if ($uniId) {
                    $q->orWhere('university_id', $uniId)
                      ->orWhereHas('universities', function ($uq) use ($uniId) {
                          $uq->where('universities.id', $uniId);
                      });
                }

                // 3. Section contains at least one active question assigned to this university
                if ($uniId) {
                    $q->orWhereHas('questions', function ($subQ) use ($uniId) {
                        $subQ->where('is_active', true)
                            ->where(function ($qScope) use ($uniId) {
                                $qScope->where('university_id', $uniId)
                                       ->orWhereHas('universities', function ($uq) use ($uniId) {
                                           $uq->where('universities.id', $uniId);
                                       });
                            });
                    });
                }
            })
            ->with(['questions' => function ($q) use ($uniId, $requiredOnly) {
                $q->where(function ($sub) use ($uniId) {
                    $sub->where(function ($gq) {
                        $gq->whereNull('university_id')->whereDoesntHave('universities');
                    });
                    if ($uniId) {
                        $sub->orWhere('university_id', $uniId)
                            ->orWhereHas('universities', function ($uq) use ($uniId) {
                                $uq->where('universities.id', $uniId);
                            });
                    }
                })->where('is_active', true);

                if ($requiredOnly) {
                    $q->where('is_required', true);
                }

                $q->orderBy('order');
            }, 'questions.options', 'questions.conditions', 'questions.translations'])
            ->orderBy('order')
            ->get()
            ->filter(function ($sec) {
                return $sec->questions->isNotEmpty();
            })
            ->values();
    }
}
