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
use Illuminate\Http\Request;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $query = Question::with(['section.category', 'dimension', 'options']);

        if ($request->has('tag') && $request->tag) {
            $query->whereJsonContains('tags', $request->tag);
        }
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }
        if ($request->has('search') && $request->search) {
            $query->where('question_text', 'LIKE', '%' . $request->search . '%');
        }

        $questions = $query->paginate(20);
        $categories = SurveyCategory::all();

        return view('admin.questions.index', compact('questions', 'categories'));
    }

    public function create(Request $request)
    {
        $sections = SurveySection::with('category')->get();
        $categories = SurveyCategory::all();
        $dimensions = PsychometricDimension::all();
        $selectedSectionId = $request->query('section_id');

        return view('admin.questions.create', compact('sections', 'categories', 'dimensions', 'selectedSectionId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'section_id' => 'required|exists:survey_sections,id',
            'question_text' => 'required|string',
            'help_text' => 'nullable|string',
            'type' => 'required|string',
            'is_required' => 'nullable|boolean',
            'dimension_id' => 'nullable|exists:psychometric_dimensions,id',
        ]);

        $validated['is_required'] = $request->has('is_required') ? (bool)$request->input('is_required') : true;

        $question = Question::create($validated);

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
        $sections = SurveySection::with('category')->get();
        $dimensions = PsychometricDimension::all();
        $question->load(['options', 'translations']);

        return view('admin.questions.edit', compact('question', 'sections', 'dimensions'));
    }

    public function update(Request $request, Question $question)
    {
        $validated = $request->validate([
            'section_id' => 'required|exists:survey_sections,id',
            'question_text' => 'required|string',
            'help_text' => 'nullable|string',
            'type' => 'required|string',
            'is_required' => 'nullable|boolean',
            'dimension_id' => 'nullable|exists:psychometric_dimensions,id',
        ]);

        $validated['is_required'] = $request->has('is_required') ? (bool)$request->input('is_required') : false;

        $question->update($validated);

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
