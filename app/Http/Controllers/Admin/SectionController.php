<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SurveyCategory;
use App\Models\SurveySection;
use App\Models\University;
use Illuminate\Http\Request;

class SectionController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = SurveySection::with(['category', 'university', 'universities'])->withCount('questions');

        if ($user && !$user->isSuperAdmin()) {
            $uniId = $user->university_id;
            $query->where(function ($q) use ($uniId) {
                $q->where(function ($gq) {
                    $gq->whereNull('university_id')
                       ->whereDoesntHave('universities');
                })
                ->orWhere('university_id', $uniId)
                ->orWhereHas('universities', function ($uq) use ($uniId) {
                    $uq->where('universities.id', $uniId);
                });
            });
        } elseif ($request->filled('university_id')) {
            if ($request->university_id === 'global') {
                $query->whereNull('university_id')->whereDoesntHave('universities');
            } else {
                $uId = $request->university_id;
                $query->where(function ($q) use ($uId) {
                    $q->where('university_id', $uId)
                      ->orWhereHas('universities', function ($uq) use ($uId) {
                          $uq->where('universities.id', $uId);
                      });
                });
            }
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('search')) {
            $query->where('title', 'LIKE', '%' . $request->search . '%');
        }

        $sections = $query->orderBy('order')->paginate(20)->withQueryString();
        $categories = SurveyCategory::all();
        $universities = University::all();

        return view('admin.sections.index', compact('sections', 'categories', 'universities'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $validated = $request->validate([
            'category_id' => 'required|exists:survey_categories,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'order' => 'nullable|integer|min:1',
            'scope_type' => 'nullable|in:global,specific',
            'university_ids' => 'nullable|array',
            'university_ids.*' => 'exists:universities,id',
        ]);

        $scopeType = $request->input('scope_type', 'global');
        $selectedIds = [];

        if ($user && !$user->isSuperAdmin()) {
            $scopeType = 'specific';
            $selectedIds = [$user->university_id];
        } elseif ($scopeType === 'specific' && $request->has('university_ids')) {
            $selectedIds = array_map('intval', $request->input('university_ids', []));
        }

        if ($scopeType === 'specific' && !empty($selectedIds)) {
            $validated['university_id'] = count($selectedIds) === 1 ? $selectedIds[0] : null;
        } else {
            $validated['university_id'] = null;
        }

        $section = SurveySection::create($validated);

        if ($scopeType === 'specific' && !empty($selectedIds)) {
            $section->universities()->sync($selectedIds);
        } else {
            $section->universities()->sync([]);
        }

        $categoryName = $section->category->name ?? 'Category';
        AuditLog::log('created_section', 'SurveySection', $section->id);

        return back()->with('success', 'New section ("'.$section->title.'") created under category "'.$categoryName.'" successfully!');
    }

    public function update(Request $request, SurveySection $section)
    {
        $user = auth()->user();

        if ($user && !$user->isSuperAdmin()) {
            $section->load('universities');
            $isMultiUni = $section->universities->count() > 1;
            $isGlobal = is_null($section->university_id) && $section->universities->isEmpty();
            $isExclusiveToUser = $section->university_id === $user->university_id && $section->universities->count() <= 1;

            if ($isGlobal || $isMultiUni || !$isExclusiveToUser) {
                abort(403, 'Unauthorized. This section is shared across multiple institutions or global, so it can only be modified by Super Administrators.');
            }
        }

        $validated = $request->validate([
            'category_id' => 'required|exists:survey_categories,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'scope_type' => 'nullable|in:global,specific',
            'university_ids' => 'nullable|array',
            'university_ids.*' => 'exists:universities,id',
        ]);

        $scopeType = $request->input('scope_type', 'global');
        $selectedIds = [];

        if ($user && !$user->isSuperAdmin()) {
            $scopeType = 'specific';
            $selectedIds = [$user->university_id];
        } elseif ($scopeType === 'specific' && $request->has('university_ids')) {
            $selectedIds = array_map('intval', $request->input('university_ids', []));
        }

        if ($scopeType === 'specific' && !empty($selectedIds)) {
            $validated['university_id'] = count($selectedIds) === 1 ? $selectedIds[0] : null;
        } else {
            $validated['university_id'] = null;
        }

        $section->update($validated);

        if ($scopeType === 'specific' && !empty($selectedIds)) {
            $section->universities()->sync($selectedIds);
        } else {
            $section->universities()->sync([]);
        }

        AuditLog::log('updated_section', 'SurveySection', $section->id);

        return back()->with('success', 'Section updated successfully!');
    }

    public function destroy(SurveySection $section)
    {
        $user = auth()->user();

        if ($user && !$user->isSuperAdmin()) {
            $section->load('universities');
            $isMultiUni = $section->universities->count() > 1;
            $isGlobal = is_null($section->university_id) && $section->universities->isEmpty();
            $isExclusiveToUser = $section->university_id === $user->university_id && $section->universities->count() <= 1;

            if ($isGlobal || $isMultiUni || !$isExclusiveToUser) {
                abort(403, 'Unauthorized. This section is shared across multiple institutions or global, so it can only be modified by Super Administrators.');
            }
        }

        $section->delete();
        AuditLog::log('deleted_section', 'SurveySection', $section->id);

        return back()->with('success', 'Section deleted successfully!');
    }
}
