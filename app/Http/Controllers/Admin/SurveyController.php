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
        $query = Survey::with('categories', 'university', 'universities');

        if ($user && !$user->isSuperAdmin()) {
            $query->where(function ($q) use ($user) {
                $q->where(function ($gq) {
                    $gq->whereNull('university_id')
                       ->whereDoesntHave('universities');
                })
                ->orWhere('university_id', $user->university_id)
                ->orWhereHas('universities', function ($uq) use ($user) {
                    $uq->where('universities.id', $user->university_id);
                });
            });
        }

        $surveys = $query->latest()->get();
        return view('admin.surveys.index', compact('surveys'));
    }

    public function create()
    {
        $user = auth()->user();
        if ($user && !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized. Only Super Administrators can create new survey campaigns.');
        }

        $universities = University::all();
        return view('admin.surveys.create', compact('universities'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if ($user && !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized. Only Super Administrators can create new survey campaigns.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'opening_message' => 'nullable|string',
            'target_respondents' => 'nullable|string',
            'status' => 'required|in:draft,published,paused,closed,archived',
            'scope_type' => 'required|in:global,specific',
            'university_ids' => 'nullable|array',
            'university_ids.*' => 'exists:universities,id',
        ]);

        if ($validated['scope_type'] === 'specific' && !empty($request->input('university_ids'))) {
            $selectedIds = $request->input('university_ids');
            $validated['university_id'] = count($selectedIds) === 1 ? $selectedIds[0] : null;
        } else {
            $validated['university_id'] = null;
        }

        $validated['estimated_completion_time'] = 15;

        $survey = Survey::create($validated);

        if ($request->input('scope_type') === 'specific' && $request->has('university_ids')) {
            $survey->universities()->sync($request->input('university_ids'));
        } else {
            $survey->universities()->sync([]);
        }

        AuditLog::log('created_survey', 'Survey', $survey->id);

        return redirect()->route('admin.surveys.show', $survey->id)->with('success', 'Survey created successfully!');
    }

    public function show(Survey $survey)
    {
        $user = auth()->user();
        if ($user && !$user->isSuperAdmin()) {
            $isAssigned = (is_null($survey->university_id) && $survey->universities->isEmpty())
                       || $survey->university_id === $user->university_id
                       || $survey->universities->contains('id', $user->university_id);
            if (!$isAssigned) {
                abort(403, 'Unauthorized. You do not have access to surveys from other institutions.');
            }
        }

        $survey->load(['categories.sections.questions.options', 'psychometricDimensions', 'compositeIndexes', 'universities']);
        $masterCategories = \App\Models\SurveyCategory::all()->unique('name');
        return view('admin.surveys.show', compact('survey', 'masterCategories'));
    }

    public function edit(Survey $survey)
    {
        $user = auth()->user();
        if ($user && !$user->isSuperAdmin()) {
            $isAssigned = (is_null($survey->university_id) && $survey->universities->isEmpty())
                       || $survey->university_id === $user->university_id
                       || $survey->universities->contains('id', $user->university_id);
            if (!$isAssigned) {
                abort(403, 'Unauthorized. You do not have permission to edit this global or super admin survey campaign.');
            }
        }

        $universities = University::all();
        $selectedUniversityIds = $survey->universities->pluck('id')->toArray();
        if ($survey->university_id && !in_array($survey->university_id, $selectedUniversityIds)) {
            $selectedUniversityIds[] = $survey->university_id;
        }
        $currentScopeType = (!empty($selectedUniversityIds) || $survey->university_id) ? 'specific' : 'global';

        return view('admin.surveys.edit', compact('survey', 'universities', 'selectedUniversityIds', 'currentScopeType'));
    }

    public function update(Request $request, Survey $survey)
    {
        $user = auth()->user();
        if ($user && !$user->isSuperAdmin()) {
            $isAssigned = (is_null($survey->university_id) && $survey->universities->isEmpty())
                       || $survey->university_id === $user->university_id
                       || $survey->universities->contains('id', $user->university_id);
            if (!$isAssigned) {
                abort(403, 'Unauthorized. You do not have permission to update this global or super admin survey campaign.');
            }
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'subtitle' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'target_respondents' => 'nullable|string',
            'status' => 'required|in:draft,published,paused,closed,archived',
            'scope_type' => 'required|in:global,specific',
            'university_ids' => 'nullable|array',
            'university_ids.*' => 'exists:universities,id',
        ]);

        if ($validated['scope_type'] === 'specific' && !empty($request->input('university_ids'))) {
            $selectedIds = $request->input('university_ids');
            $validated['university_id'] = count($selectedIds) === 1 ? $selectedIds[0] : null;
        } else {
            $validated['university_id'] = null;
        }

        $survey->update($validated);

        if ($request->input('scope_type') === 'specific' && $request->has('university_ids')) {
            $survey->universities()->sync($request->input('university_ids'));
        } else {
            $survey->universities()->sync([]);
        }

        AuditLog::log('updated_survey', 'Survey', $survey->id);

        return redirect()->route('admin.surveys.index')->with('success', 'Survey updated successfully!');
    }

    public function destroy(Survey $survey)
    {
        $user = auth()->user();
        if ($user && !$user->isSuperAdmin()) {
            if (is_null($survey->university_id) || $survey->university_id !== $user->university_id) {
                abort(403, 'Unauthorized. You do not have permission to delete this global or super admin survey campaign.');
            }
        }

        $survey->delete();
        AuditLog::log('deleted_survey', 'Survey', $survey->id);

        return redirect()->route('admin.surveys.index')->with('success', 'Survey deleted successfully!');
    }

    public function clone(Survey $survey, SurveyService $surveyService)
    {
        $user = auth()->user();
        if ($user && !$user->isSuperAdmin()) {
            if (is_null($survey->university_id) || $survey->university_id !== $user->university_id) {
                abort(403, 'Unauthorized. You do not have permission to clone this global or super admin survey campaign.');
            }
        }

        $newSurvey = $surveyService->cloneSurvey($survey, $survey->title . ' (Copy)');
        AuditLog::log('cloned_survey', 'Survey', $newSurvey->id);

        return redirect()->route('admin.surveys.show', $newSurvey->id)->with('success', 'Survey cloned successfully!');
    }

    public function preview(Survey $survey)
    {
        $user = auth()->user();
        $isSuperAdmin = $user ? $user->isSuperAdmin() : false;

        $targetUniId = null;
        $filterApplied = false;

        if (request()->filled('university_id')) {
            $targetUniId = request('university_id') === 'global' ? null : (int)request('university_id');
            $filterApplied = true;
        } elseif ($user && !$isSuperAdmin) {
            $targetUniId = $user->university_id;
            $filterApplied = true;
        } elseif ($user && $isSuperAdmin && $user->university_id) {
            $targetUniId = $user->university_id;
            $filterApplied = true;
        }

        $categories = $survey->categories()->with(['sections' => function ($sq) use ($filterApplied, $targetUniId) {
            if ($filterApplied) {
                $sq->where(function ($q) use ($targetUniId) {
                    $q->whereNull('university_id');
                    if ($targetUniId) {
                        $q->orWhere('university_id', $targetUniId);
                    }
                    $q->orWhereHas('questions', function ($qq) use ($targetUniId) {
                        $qq->whereNull('university_id');
                        if ($targetUniId) {
                            $qq->orWhere('university_id', $targetUniId);
                        }
                    });
                });
            }
            $sq->with(['questions' => function ($qq) use ($filterApplied, $targetUniId) {
                if ($filterApplied) {
                    $qq->where(function ($q) use ($targetUniId) {
                        $q->whereNull('university_id');
                        if ($targetUniId) {
                            $q->orWhere('university_id', $targetUniId);
                        }
                    });
                }
                $qq->with('options');
            }]);
        }])->get();

        $selectedCategoryId = request('category_id');
        $category = $selectedCategoryId ? $categories->firstWhere('id', $selectedCategoryId) : $categories->first();
        $sections = $category ? $category->sections : collect();

        $universities = University::all();
        $university = null;
        if ($targetUniId) {
            $university = University::find($targetUniId);
        }
        if (!$university && $user && $user->university) {
            $university = $user->university;
        }
        if (!$university) {
            $university = $survey->university ?? University::first();
        }

        return view('admin.surveys.preview', compact('survey', 'categories', 'category', 'sections', 'university', 'universities', 'targetUniId', 'isSuperAdmin', 'filterApplied'));
    }
}
