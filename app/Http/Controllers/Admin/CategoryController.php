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
        return view('admin.categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'survey_id' => 'required|exists:surveys,id',
            'code' => 'nullable|string|max:50',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'eligibility' => 'nullable|string',
            'estimated_minutes' => 'nullable|integer|min:1',
        ]);

        if (empty($validated['code'])) {
            $validated['code'] = \Illuminate\Support\Str::slug($validated['name']) ?: 'cat_' . time();
        }

        $category = SurveyCategory::create($validated);
        AuditLog::log('created_category', 'SurveyCategory', $category->id);

        return back()->with('success', 'Category created successfully!');
    }

    public function update(Request $request, SurveyCategory $category)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'eligibility' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $category->update($validated);
        AuditLog::log('updated_category', 'SurveyCategory', $category->id);

        return back()->with('success', 'Category updated successfully!');
    }
}
