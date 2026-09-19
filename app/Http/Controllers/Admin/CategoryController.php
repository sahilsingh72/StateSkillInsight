<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Survey;
use App\Models\SurveyCategory;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = SurveyCategory::with('survey')->orderBy('order')->get();
        $surveys = Survey::all();

        return view('admin.categories.index', compact('categories', 'surveys'));
    }

    public function store(Request $request)
    {
        // Case 1: Syncing multiple existing categories to a survey (with select/deselect support)
        if ($request->has('category_ids') && $request->filled('survey_id')) {
            $request->validate([
                'survey_id' => 'required|exists:surveys,id',
                'category_ids' => 'nullable|array',
                'category_ids.*' => 'exists:survey_categories,id',
            ]);

            $survey = Survey::findOrFail($request->survey_id);
            $categoryIds = $request->input('category_ids', []);
            $survey->categories()->sync($categoryIds);

            AuditLog::log('synced_survey_categories', 'Survey', $survey->id);
            return back()->with('success', 'Survey categories updated successfully without creating duplicates!');
        }

        // Case 1b: Attaching single existing category (legacy compatibility)
        if ($request->filled('existing_category_id')) {
            $request->validate([
                'survey_id' => 'required|exists:surveys,id',
                'existing_category_id' => 'required|exists:survey_categories,id',
            ]);

            $survey = Survey::findOrFail($request->survey_id);
            $survey->categories()->syncWithoutDetaching([$request->existing_category_id]);

            AuditLog::log('attached_category', 'SurveyCategory', $request->existing_category_id);
            return back()->with('success', 'Category attached to survey successfully!');
        }

        // Case 2: Creating a brand new category
        $validated = $request->validate([
            'survey_id' => 'nullable|exists:surveys,id',
            'code' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'eligibility' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
        ]);

        if (empty($validated['code'])) {
            $validated['code'] = \Illuminate\Support\Str::slug($validated['name']) ?: 'cat_' . time();
        }

        if (empty($validated['survey_id'])) {
            $activeSurvey = Survey::where('status', 'published')->first() ?? Survey::first();
            $validated['survey_id'] = $activeSurvey ? $activeSurvey->id : null;
        }

        $category = SurveyCategory::create($validated);
        AuditLog::log('created_category', 'SurveyCategory', $category->id);

        return back()->with('success', 'New Category ('.$category->name.') created in Category Engine successfully!');
    }

    public function update(Request $request, SurveyCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'code' => 'nullable|string|max:50',
            'description' => 'nullable|string',
            'eligibility' => 'nullable|string',
            'icon' => 'nullable|string|max:50',
            'is_active' => 'boolean',
        ]);

        $category->update($validated);
        AuditLog::log('updated_category', 'SurveyCategory', $category->id);

        return back()->with('success', 'Category updated successfully!');
    }

    public function syncSurveyCategories(Request $request, Survey $survey)
    {
        $request->validate([
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:survey_categories,id',
        ]);

        $categoryIds = $request->input('category_ids', []);
        $survey->categories()->sync($categoryIds);

        AuditLog::log('synced_survey_categories', 'Survey', $survey->id);

        return back()->with('success', 'Survey categories updated successfully!');
    }
}
