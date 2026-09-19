<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Survey;
use App\Models\University;
use App\Services\SurveyService;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $query = Survey::with('categories', 'university');

        if ($user && !$user->isSuperAdmin()) {
            $query->where('university_id', $user->university_id);
        }

        $surveys = $query->latest()->get();
        return view('admin.surveys.index', compact('surveys'));
    }

    public function create()
    {
        $user = auth()->user();
        if ($user && !$user->isSuperAdmin()) {
            $universities = University::where('id', $user->university_id)->get();
        } else {
            $universities = University::all();
        }

        return view('admin.surveys.create', compact('universities'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'opening_message' => 'nullable|string',
            'target_respondents' => 'nullable|string',
            'status' => 'required|in:draft,published,paused,closed,archived',
            'university_id' => 'nullable|exists:universities,id',
        ]);

        if ($user && !$user->isSuperAdmin()) {
            $validated['university_id'] = $user->university_id;
        } else {
            $validated['university_id'] = $request->input('university_id') ?? $user->university_id ?? University::first()?->id;
        }

        $validated['estimated_completion_time'] = 15;

        $survey = Survey::create($validated);

        AuditLog::log('created_survey', 'Survey', $survey->id);

        return redirect()->route('admin.surveys.show', $survey->id)->with('success', 'Survey created successfully!');
    }

    public function show(Survey $survey)
    {
        $user = auth()->user();
        if ($user && !$user->isSuperAdmin() && $survey->university_id !== $user->university_id) {
            abort(403, 'Unauthorized. You do not have access to surveys from other institutions.');
        }

        $survey->load(['categories.sections.questions.options', 'psychometricDimensions', 'compositeIndexes']);
        $masterCategories = \App\Models\SurveyCategory::all()->unique('name');
        return view('admin.surveys.show', compact('survey', 'masterCategories'));
    }

    public function edit(Survey $survey)
    {
        $user = auth()->user();
        if ($user && !$user->isSuperAdmin() && $survey->university_id !== $user->university_id) {
            abort(403, 'Unauthorized. You do not have permission to edit surveys from other institutions.');
        }

        return view('admin.surveys.edit', compact('survey'));
    }

    public function update(Request $request, Survey $survey)
    {
        $user = auth()->user();
        if ($user && !$user->isSuperAdmin() && $survey->university_id !== $user->university_id) {
            abort(403, 'Unauthorized. You do not have permission to update surveys from other institutions.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'required|in:draft,published,paused,closed,archived',
        ]);

        $survey->update($validated);
        AuditLog::log('updated_survey', 'Survey', $survey->id);

        return redirect()->route('admin.surveys.show', $survey->id)->with('success', 'Survey updated successfully!');
    }

    public function clone(Survey $survey, SurveyService $surveyService)
    {
        $user = auth()->user();
        if ($user && !$user->isSuperAdmin() && $survey->university_id !== $user->university_id) {
            abort(403, 'Unauthorized. You do not have permission to clone surveys from other institutions.');
        }

        $newSurvey = $surveyService->cloneSurvey($survey, $survey->title . ' (Copy)');
        AuditLog::log('cloned_survey', 'Survey', $newSurvey->id);

        return redirect()->route('admin.surveys.show', $newSurvey->id)->with('success', 'Survey cloned successfully!');
    }

    public function preview(Survey $survey)
    {
        $user = auth()->user();
        if ($user && !$user->isSuperAdmin() && $survey->university_id !== $user->university_id) {
            abort(403, 'Unauthorized. You do not have permission to preview surveys from other institutions.');
        }

        $categories = $survey->categories()->with('sections.questions.options')->get();
        $selectedCategoryId = request('category_id');
        $category = $selectedCategoryId ? $categories->firstWhere('id', $selectedCategoryId) : $categories->first();
        $sections = $category ? $category->sections : collect();
        $university = $survey->university ?? University::first();

        return view('admin.surveys.preview', compact('survey', 'categories', 'category', 'sections', 'university'));
    }
}
