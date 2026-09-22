<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\PsychometricDimension;
use App\Models\Question;
use App\Models\QuestionOption;
use App\Models\QuestionTranslation;
use App\Models\SurveyCategory;
use App\Models\SurveySection;
use App\Models\University;
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Question::with(['section.category', 'dimension', 'options', 'university', 'universities']);

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

        if ($request->has('tag') && $request->tag) {
            $query->whereJsonContains('tags', $request->tag);
        }
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }
        if ($request->has('search') && $request->search) {
            $query->where('question_text', 'LIKE', '%' . $request->search . '%');
        }

        $questions = $query->paginate(20)->withQueryString();
        $categories = SurveyCategory::all();
        $universities = University::all();

        return view('admin.questions.index', compact('questions', 'categories', 'universities'));
    }

    public function create(Request $request)
    {
        $user = auth()->user();
        $secQuery = SurveySection::with('category');
        if ($user && !$user->isSuperAdmin()) {
            $secQuery->where(function ($q) use ($user) {
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
        $sections = $secQuery->get();
        $categories = SurveyCategory::all();
        $dimensions = PsychometricDimension::all();
        $universities = University::all();
        $selectedSectionId = $request->query('section_id');

        return view('admin.questions.create', compact('sections', 'categories', 'dimensions', 'universities', 'selectedSectionId'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        $validated = $request->validate([
            'section_id' => 'required|exists:survey_sections,id',
            'question_text' => 'required|string',
            'help_text' => 'nullable|string',
            'type' => 'required|string',
            'is_required' => 'nullable|boolean',
            'dimension_id' => 'nullable|exists:psychometric_dimensions,id',
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

        $validated['is_required'] = $request->has('is_required') ? (bool)$request->input('is_required') : true;

        $question = Question::create($validated);

        if ($scopeType === 'specific' && !empty($selectedIds)) {
            $question->universities()->sync($selectedIds);
        } else {
            $question->universities()->sync([]);
        }

        if ($section = SurveySection::find($validated['section_id'])) {
            if ($scopeType === 'global' || empty($selectedIds)) {
                $section->update(['university_id' => null]);
                $section->universities()->sync([]);
            } else {
                if ($section->university_id !== null && count($selectedIds) === 1) {
                    $section->update(['university_id' => $selectedIds[0]]);
                } else {
                    $section->update(['university_id' => null]);
                }
                $existingSecUniIds = $section->universities()->pluck('universities.id')->toArray();
                $mergedUniIds = array_unique(array_merge($existingSecUniIds, $selectedIds));
                $section->universities()->sync($mergedUniIds);
            }
        }

        $rawOptions = $request->input('options_text') ?? $request->input('options', []);
        $lines = is_array($rawOptions) ? implode("\n", $rawOptions) : $rawOptions;
        $optList = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string)$lines)));

        $idx = 1;
        foreach ($optList as $optText) {
            if ($optText === '') continue;
            QuestionOption::create([
                'question_id' => $question->id,
                'option_text' => $optText,
                'value' => \Illuminate\Support\Str::slug($optText) ?: 'opt_' . $idx,
                'score' => $idx * 20,
                'order' => $idx,
            ]);
            $idx++;
        }

        AuditLog::log('created_question', 'Question', $question->id);

        return redirect()->route('admin.questions.index')->with('success', 'Question created successfully!');
    }

    public function edit(Question $question)
    {
        $user = auth()->user();
        $question->load(['options', 'translations', 'universities']);

        $isAssigned = (is_null($question->university_id) && $question->universities->isEmpty())
                   || $question->university_id === $user?->university_id
                   || $question->universities->contains('id', $user?->university_id);

        if ($user && !$user->isSuperAdmin() && !$isAssigned) {
            abort(403, 'Unauthorized access to question belonging to another institution.');
        }

        $secQuery = SurveySection::with('category');
        if ($user && !$user->isSuperAdmin()) {
            $secQuery->where(function ($q) use ($user) {
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
        $sections = $secQuery->get();

        $dimensions = PsychometricDimension::all();
        $universities = University::all();

        $selectedUniversityIds = $question->universities->pluck('id')->toArray();
        if ($question->university_id && !in_array($question->university_id, $selectedUniversityIds)) {
            $selectedUniversityIds[] = $question->university_id;
        }
        $currentScopeType = (!empty($selectedUniversityIds) || $question->university_id) ? 'specific' : 'global';

        return view('admin.questions.edit', compact('question', 'sections', 'dimensions', 'universities', 'selectedUniversityIds', 'currentScopeType'));
    }

    public function update(Request $request, Question $question)
    {
        $user = auth()->user();
        $question->load('universities');

        $isAssigned = (is_null($question->university_id) && $question->universities->isEmpty())
                   || $question->university_id === $user?->university_id
                   || $question->universities->contains('id', $user?->university_id);

        if ($user && !$user->isSuperAdmin() && !$isAssigned) {
            abort(403, 'Unauthorized access to update question belonging to another institution.');
        }

        $validated = $request->validate([
            'section_id' => 'required|exists:survey_sections,id',
            'question_text' => 'required|string',
            'help_text' => 'nullable|string',
            'type' => 'required|string',
            'is_required' => 'nullable|boolean',
            'dimension_id' => 'nullable|exists:psychometric_dimensions,id',
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

        $validated['is_required'] = $request->has('is_required') ? (bool)$request->input('is_required') : false;

        $question->update($validated);

        if ($scopeType === 'specific' && !empty($selectedIds)) {
            $question->universities()->sync($selectedIds);
        } else {
            $question->universities()->sync([]);
        }

        if ($section = SurveySection::find($validated['section_id'])) {
            if ($scopeType === 'global' || empty($selectedIds)) {
                $section->update(['university_id' => null]);
                $section->universities()->sync([]);
            } else {
                if ($section->university_id !== null && count($selectedIds) === 1) {
                    $section->update(['university_id' => $selectedIds[0]]);
                } else {
                    $section->update(['university_id' => null]);
                }
                $existingSecUniIds = $section->universities()->pluck('universities.id')->toArray();
                $mergedUniIds = array_unique(array_merge($existingSecUniIds, $selectedIds));
                $section->universities()->sync($mergedUniIds);
            }
        }

        if ($request->has('options') || $request->has('options_text')) {
            $question->options()->delete();
            $rawOptions = $request->input('options_text') ?? $request->input('options', []);
            $lines = is_array($rawOptions) ? implode("\n", $rawOptions) : $rawOptions;
            $optList = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string)$lines)));

            $idx = 1;
            foreach ($optList as $optText) {
                if ($optText === '') continue;
                QuestionOption::create([
                    'question_id' => $question->id,
                    'option_text' => $optText,
                    'value' => \Illuminate\Support\Str::slug($optText) ?: 'opt_' . $idx,
                    'score' => $idx * 20,
                    'order' => $idx,
                ]);
                $idx++;
            }
        }

        AuditLog::log('updated_question', 'Question', $question->id);

        return redirect()->route('admin.questions.index')->with('success', 'Question updated successfully!');
    }
}
