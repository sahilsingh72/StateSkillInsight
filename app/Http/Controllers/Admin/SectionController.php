<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SurveySection;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:survey_categories,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'integer|min:1',
        ]);

        $section = SurveySection::create($validated);
        AuditLog::log('created_section', 'SurveySection', $section->id);

        return back()->with('success', 'Section added successfully!');
    }

    public function update(Request $request, SurveySection $section)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $section->update($validated);
        AuditLog::log('updated_section', 'SurveySection', $section->id);

        return back()->with('success', 'Section updated successfully!');
    }
}
