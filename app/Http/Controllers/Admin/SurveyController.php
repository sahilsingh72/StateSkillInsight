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
        $surveys = Survey::with('categories')->latest()->get();
        return view('admin.surveys.index', compact('surveys'));
    }

    public function create()
    {
        $universities = University::all();
        return view('admin.surveys.create', compact('universities'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'opening_message' => 'nullable|string',
            'target_respondents' => 'nullable|string',
            'estimated_completion_time' => 'required|integer|min:1',
            'status' => 'required|in:draft,published,paused,closed,archived',
        ]);

        $university = University::first();
        $validated['university_id'] = $university->id;

        $survey = Survey::create($validated);

        AuditLog::log('created_survey', 'Survey', $survey->id);

        return redirect()->route('admin.surveys.show', $survey->id)->with('success', 'Survey created successfully!');
    }

    public function show(Survey $survey)
    {
        $survey->load(['categories.sections.questions.options', 'psychometricDimensions', 'compositeIndexes']);
        return view('admin.surveys.show', compact('survey'));
    }

    public function edit(Survey $survey)
    {
        return view('admin.surveys.edit', compact('survey'));
    }

    public function update(Request $request, Survey $survey)
    {
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
        $newSurvey = $surveyService->cloneSurvey($survey, $survey->title . ' (Copy)');
        AuditLog::log('cloned_survey', 'Survey', $newSurvey->id);

        return redirect()->route('admin.surveys.show', $newSurvey->id)->with('success', 'Survey cloned successfully!');
    }

    public function preview(Survey $survey)
    {
        $categories = $survey->categories()->with('sections.questions.options')->get();
        $selectedCategoryId = request('category_id');
        $category = $selectedCategoryId ? $categories->firstWhere('id', $selectedCategoryId) : $categories->first();
        $sections = $category ? $category->sections : collect();
        $university = $survey->university ?? University::first();

        return view('admin.surveys.preview', compact('survey', 'categories', 'category', 'sections', 'university'));
    }
}
