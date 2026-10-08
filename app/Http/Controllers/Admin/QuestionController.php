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
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\NamedRange;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class QuestionController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Question::with(['section.category', 'dimension', 'options', 'university', 'universities', 'creator']);

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

        if ($request->filled('section_id')) {
            $query->where('section_id', $request->section_id);
        }
        if ($request->filled('category_id')) {
            $query->whereHas('section', function ($sq) use ($request) {
                $sq->where('category_id', $request->category_id);
            });
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

        $secQuery = SurveySection::with('category')->orderBy('title');
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
        $universities = University::with(['colleges', 'parent'])->withCount('colleges')->orderBy('name')->get();

        return view('admin.questions.index', compact('questions', 'categories', 'universities', 'sections'));
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
        $universities = University::with(['colleges', 'parent'])->withCount('colleges')->orderBy('name')->get();
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
            'is_active' => 'nullable|boolean',
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

        $targetSection = SurveySection::with('category')->findOrFail($request->section_id);
        $trimmedText = trim($request->question_text);

        $dupQuery = Question::whereHas('section', function ($sq) use ($targetSection) {
            $sq->where('category_id', $targetSection->category_id);
        })->whereRaw('LOWER(TRIM(question_text)) = ?', [strtolower($trimmedText)]);

        if ($dupQuery->exists()) {
            $catName = $targetSection->category->name ?? 'selected category';
            return back()->withInput()->withErrors([
                'question_text' => "A question with this statement already exists in the '{$catName}' category. Duplicate questions in the same category are not allowed."
            ]);
        }

        $question = Question::create($validated);

        if ($scopeType === 'specific' && !empty($selectedIds)) {
            $question->universities()->sync($selectedIds);
        } else {
            $question->universities()->sync([]);
        }

        $rawOptions = $request->input('options_text') ?? $request->input('options', []);
        $lines = is_array($rawOptions) ? implode("\n", $rawOptions) : $rawOptions;
        $optList = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string)$lines)));

        if ($question->type === 'likert' && empty($optList)) {
            $optList = [
                'Strongly Disagree',
                'Disagree',
                'Neutral',
                'Agree',
                'Strongly Agree',
            ];
        }

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
        $question->load(['options', 'translations', 'universities', 'creator']);

        if (!$question->canBeEditedBy($user)) {
            abort(403, 'Questions created by superadmin or assigned globally cannot be edited by university admins.');
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
        $universities = University::with(['colleges', 'parent'])->withCount('colleges')->orderBy('name')->get();

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
        $question->load(['universities', 'creator']);

        if (!$question->canBeEditedBy($user)) {
            abort(403, 'Questions created by superadmin or assigned globally cannot be updated by university admins.');
        }

        $validated = $request->validate([
            'section_id' => 'required|exists:survey_sections,id',
            'question_text' => 'required|string',
            'help_text' => 'nullable|string',
            'type' => 'required|string',
            'is_required' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
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

        $targetSection = SurveySection::with('category')->findOrFail($request->section_id);
        $trimmedText = trim($request->question_text);

        $dupQuery = Question::where('id', '!=', $question->id)
            ->whereHas('section', function ($sq) use ($targetSection) {
                $sq->where('category_id', $targetSection->category_id);
            })->whereRaw('LOWER(TRIM(question_text)) = ?', [strtolower($trimmedText)]);

        if ($dupQuery->exists()) {
            $catName = $targetSection->category->name ?? 'selected category';
            return back()->withInput()->withErrors([
                'question_text' => "A question with this statement already exists in the '{$catName}' category. Duplicate questions in the same category are not allowed."
            ]);
        }

        $validated['is_required'] = $request->boolean('is_required');
        if ($request->has('is_active')) {
            $validated['is_active'] = $request->boolean('is_active');
        }

        $question->update($validated);

        if ($scopeType === 'specific' && !empty($selectedIds)) {
            $question->universities()->sync($selectedIds);
        } else {
            $question->universities()->sync([]);
        }

        if ($request->has('options') || $request->has('options_text') || $question->type === 'likert') {
            $rawOptions = $request->input('options_text') ?? $request->input('options', []);
            $lines = is_array($rawOptions) ? implode("\n", $rawOptions) : $rawOptions;
            $optList = array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', (string)$lines)));

            if ($question->type === 'likert' && empty($optList)) {
                $optList = [
                    'Strongly Disagree',
                    'Disagree',
                    'Neutral',
                    'Agree',
                    'Strongly Agree',
                ];
            }

            if (!empty($optList) || $question->options()->count() > 0) {
                $question->options()->delete();
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
        }

        AuditLog::log('updated_question', 'Question', $question->id);

        if ($request->input('return_to') === 'section_modal' || $request->filled('section_id_return')) {
            $secId = $request->input('section_id_return') ?? $question->section_id;
            return redirect()->route('admin.sections.index', ['open_section_modal' => $secId])->with('success', 'Question updated successfully!');
        }

        return redirect()->route('admin.questions.index')->with('success', 'Question updated successfully!');
    }

    public function destroy(Request $request, Question $question)
    {
        $user = auth()->user();
        $question->load('creator');

        if (!$question->canBeEditedBy($user)) {
            abort(403, 'Questions created by superadmin or assigned globally cannot be deleted by university admins.');
        }

        $secId = $request->input('section_id') ?? $question->section_id;
        $returnTo = $request->input('return_to');

        AuditLog::log('deleted_question', 'Question', $question->id);

        $question->options()->delete();
        $question->translations()->delete();
        $question->universities()->detach();
        $question->delete();

        if ($returnTo === 'section_modal' && $secId) {
            return redirect()->route('admin.sections.index', ['open_section_modal' => $secId])->with('success', 'Question deleted successfully!');
        }

        return redirect()->route('admin.questions.index')->with('success', 'Question deleted successfully!');
    }

    public function toggleStatus(Request $request, Question $question)
    {
        $user = auth()->user();
        if (!$question->canBeEditedBy($user)) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json(['success' => false, 'message' => 'Unauthorized action.'], 403);
            }
            abort(403, 'Unauthorized to modify this question.');
        }

        $question->is_active = !$question->is_active;
        $question->save();

        AuditLog::log('toggled_question_status', 'Question', $question->id);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'is_active' => (bool)$question->is_active,
                'message' => 'Question status ' . ($question->is_active ? 'enabled' : 'disabled') . ' successfully.'
            ]);
        }

        return back()->with('success', 'Question ' . ($question->is_active ? 'enabled' : 'disabled') . ' successfully!');
    }

    public function downloadSampleExcel()
    {
        $spreadsheet = new Spreadsheet();

        // Main Sheet
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Questions Import');

        // Lookup Sheet for Excel Data Validations
        $lookupSheet = $spreadsheet->createSheet();
        $lookupSheet->setTitle('Options');

        $user = auth()->user();
        $isSuperAdmin = $user ? $user->isSuperAdmin() : true;
        $uniId = $user ? $user->university_id : null;

        // 1. Categories
        $categories = SurveyCategory::orderBy('order')->get();
        $categoryOptions = [];
        foreach ($categories as $cat) {
            $categoryOptions[] = ["{$cat->code}: {$cat->name}"];
        }
        if (empty($categoryOptions)) {
            $categoryOptions = [
                ['cat_1: Working Alumni'],
                ['cat_2: Job-Seeking Alumni'],
                ['cat_3: Current Students'],
                ['cat_4: Dropped-out Students'],
            ];
        }

        // 2. Question Types
        $typeOptions = [
            ['Single Choice (Radio)'],
            ['Multiple Choice (Checkboxes)'],
            ['Dropdown Select'],
            ['Likert Scale (1-5)'],
            ['Rating (1-5 Stars)'],
            ['Short Text'],
            ['Long Text'],
            ['Voice Answer'],
        ];

        // 3. Psychometric Dimensions
        $dimensions = PsychometricDimension::orderBy('id')->get();
        $dimensionOptions = [
            ['None'],
        ];
        foreach ($dimensions as $dim) {
            $dimensionOptions[] = ["{$dim->code}: {$dim->name}"];
        }

        // 4. Required Flags
        $requiredOptions = [
            ['Yes'],
            ['No'],
        ];

        // 5. Status
        $statusOptions = [
            ['Active'],
            ['Disabled'],
        ];

        // 6. Accessible Sections Query
        $secQuery = SurveySection::with('category')->orderBy('category_id')->orderBy('order');
        if ($user && !$isSuperAdmin) {
            $secQuery->where(function ($q) use ($uniId) {
                $q->where(function ($gq) {
                    $gq->whereNull('university_id')
                       ->whereDoesntHave('universities');
                })
                ->orWhere('university_id', $uniId)
                ->orWhereHas('universities', function ($uq) use ($uniId) {
                    $uq->where('universities.id', $uniId);
                });
            });
        }
        $allSections = $secQuery->get();

        $allSectionTitles = [];
        foreach ($allSections as $sec) {
            $allSectionTitles[] = [$sec->title];
        }
        if (empty($allSectionTitles)) {
            $allSectionTitles = [['Section A: Education & Professional Journey']];
        }

        // Populate Common Lookup Columns
        $lookupSheet->fromArray($categoryOptions, null, 'A1');
        $lookupSheet->fromArray($typeOptions, null, 'B1');
        $lookupSheet->fromArray($dimensionOptions, null, 'C1');
        $lookupSheet->fromArray($requiredOptions, null, 'D1');
        $lookupSheet->fromArray($statusOptions, null, 'E1');
        $lookupSheet->fromArray($allSectionTitles, null, 'F1');

        $catCount = count($categoryOptions);
        $typeCount = count($typeOptions);
        $dimCount = count($dimensionOptions);
        $allSecCount = count($allSectionTitles);

        $spreadsheet->addNamedRange(new NamedRange('CategoryList', $lookupSheet, "\$A\$1:\$A\${$catCount}"));
        $spreadsheet->addNamedRange(new NamedRange('TypeList', $lookupSheet, "\$B\$1:\$B\${$typeCount}"));
        $spreadsheet->addNamedRange(new NamedRange('DimensionList', $lookupSheet, "\$C\$1:\$C\${$dimCount}"));
        $spreadsheet->addNamedRange(new NamedRange('RequiredList', $lookupSheet, '$D$1:$D$2'));
        $spreadsheet->addNamedRange(new NamedRange('StatusList', $lookupSheet, '$E$1:$E$2'));
        $spreadsheet->addNamedRange(new NamedRange('AllSections', $lookupSheet, "\$F\$1:\$F\${$allSecCount}"));

        // 7. Create Category-Specific Named Ranges for Dependent Section Dropdowns
        // Start putting category-specific sections at Column G onwards (G, H, I, J, ...)
        $colIndex = 7; // Column G is index 7
        foreach ($categories as $cat) {
            $catSecs = $allSections->where('category_id', $cat->id);
            $catSecTitles = [];
            foreach ($catSecs as $s) {
                $catSecTitles[] = [$s->title];
            }
            if (empty($catSecTitles)) {
                $catSecTitles = [["{$cat->name} - General Section"]];
            }

            $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
            $lookupSheet->fromArray($catSecTitles, null, "{$colLetter}1");
            $secCountInCat = count($catSecTitles);

            $cleanCode = preg_replace('/[^a-zA-Z0-9_]/', '', strtolower($cat->code));
            if (!empty($cleanCode)) {
                $spreadsheet->addNamedRange(new NamedRange($cleanCode, $lookupSheet, "\${$colLetter}\$1:\${$colLetter}\${$secCountInCat}"));
            }
            $colIndex++;
        }

        $lookupSheet->setSheetState(Worksheet::SHEETSTATE_HIDDEN);

        $headers = [
            'Target Category (Code or Name) *',
            'Section Title *',
            'Question Statement *',
            'Help Text / Instructions',
            'Question Type *',
            'Options (Separated by ; for Choice/Dropdown )',
            'Psychometric Dimension (Code or Name)',
            'Must to Answer (Required) *',
            'Status *',
            'Tags (Comma-separated)'
        ];

        $sheet->fromArray([$headers], null, 'A1');

        $headerRange = 'A1:J1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setColor(new Color(Color::COLOR_WHITE));
        $sheet->getStyle($headerRange)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('4F46E5');
        $sheet->freezePane('A2');

        // Sample Data with category-matched sections
        $cat1Secs = $allSections->where('category_id', $categories->firstWhere('code', 'cat_1')->id ?? 1)->values();
        $cat3Secs = $allSections->where('category_id', $categories->firstWhere('code', 'cat_3')->id ?? 3)->values();

        $sampleSec1 = $cat1Secs[0]->title ?? ($allSectionTitles[0][0] ?? 'Section A: Education & Professional Journey');
        $sampleSec2 = $cat1Secs[1]->title ?? $sampleSec1;
        $sampleSec3 = $cat1Secs[2]->title ?? $sampleSec1;
        $sampleSec4 = $cat1Secs[3]->title ?? $sampleSec1;
        $sampleSec5 = $cat3Secs[0]->title ?? $sampleSec1;

        $sampleDim1 = $dimensionOptions[1][0] ?? 'None';
        $sampleDim2 = $dimensionOptions[2][0] ?? 'None';
        $sampleDim3 = $dimensionOptions[3][0] ?? 'None';
        $sampleDim4 = 'None';
        $sampleDim5 = $dimensionOptions[7][0] ?? ($dimensionOptions[1][0] ?? 'None');

        $sampleData = [
            [
                $categoryOptions[0][0] ?? 'cat_1: Working Alumni',
                $sampleSec1,
                'What was your highest qualification from the university / college?',
                'Please select your highest completed degree',
                'Single Choice (Radio)',
                'B.Tech / B.E.; B.Sc; B.Com; BCA; M.Tech; MBA; MCA; Ph.D.; Other',
                $sampleDim1,
                'Yes',
                'Active',
                'academic, qualification'
            ],
            [
                $categoryOptions[0][0] ?? 'cat_1: Working Alumni',
                $sampleSec2,
                'How effectively did your academic coursework align with practical industry requirements?',
                'Rate on a scale from 1 (Not effective) to 5 (Extremely effective)',
                'Likert Scale (1-5)',
                'Strongly Disagree; Disagree; Neutral; Agree; Strongly Agree',
                $sampleDim2,
                'Yes',
                'Active',
                'technical, industry_alignment'
            ],
            [
                $categoryOptions[0][0] ?? 'cat_1: Working Alumni',
                $sampleSec3,
                'Rate the overall quality of laboratory facilities, toolkits, and experimental sessions provided during your study.',
                '1 to 5 star rating',
                'Rating (1-5 Stars)',
                '',
                $sampleDim3,
                'No',
                'Active',
                'practical, infrastructure'
            ],
            [
                $categoryOptions[0][0] ?? 'cat_1: Working Alumni',
                $sampleSec4,
                'What specialized technical certifications or emerging tech tools would you recommend adding to the modern curriculum?',
                'Write your brief suggestions below',
                'Short Text',
                '',
                $sampleDim4,
                'No',
                'Active',
                'curriculum, recommendations'
            ],
            [
                $categoryOptions[2][0] ?? 'cat_3: Current Students',
                $sampleSec5,
                'Which advanced technological domains are you actively developing skills in?',
                'Select all domains that apply to you',
                'Multiple Choice (Checkboxes)',
                'Artificial Intelligence & Machine Learning; Cloud Computing & DevOps; Cybersecurity; Data Science; Full Stack Web Development; Mobile App Development',
                $sampleDim5,
                'Yes',
                'Active',
                'ai, digital_skills, career'
            ],
        ];

        $sheet->fromArray($sampleData, null, 'A2');

        // Category Validation (Col A)
        $valCat = $sheet->getCell('A2')->getDataValidation();
        $valCat->setType(DataValidation::TYPE_LIST);
        $valCat->setErrorStyle(DataValidation::STYLE_STOP);
        $valCat->setAllowBlank(false);
        $valCat->setShowDropDown(true);
        $valCat->setShowErrorMessage(true);
        $valCat->setErrorTitle('Invalid Category');
        $valCat->setError('Please select a valid Survey Category from the list.');
        $valCat->setFormula1('CategoryList');

        // Question Type Validation (Col E)
        $valType = $sheet->getCell('E2')->getDataValidation();
        $valType->setType(DataValidation::TYPE_LIST);
        $valType->setErrorStyle(DataValidation::STYLE_STOP);
        $valType->setAllowBlank(false);
        $valType->setShowDropDown(true);
        $valType->setShowErrorMessage(true);
        $valType->setErrorTitle('Invalid Question Type');
        $valType->setError('Please select a valid Question Type from the dropdown.');
        $valType->setFormula1('TypeList');

        // Psychometric Dimension Validation (Col G)
        $valDim = $sheet->getCell('G2')->getDataValidation();
        $valDim->setType(DataValidation::TYPE_LIST);
        $valDim->setErrorStyle(DataValidation::STYLE_STOP);
        $valDim->setAllowBlank(true);
        $valDim->setShowDropDown(true);
        $valDim->setShowErrorMessage(true);
        $valDim->setErrorTitle('Invalid Psychometric Dimension');
        $valDim->setError('Please select a valid Dimension or None from the dropdown.');
        $valDim->setFormula1('DimensionList');

        // Required Validation (Col H)
        $valReq = $sheet->getCell('H2')->getDataValidation();
        $valReq->setType(DataValidation::TYPE_LIST);
        $valReq->setErrorStyle(DataValidation::STYLE_STOP);
        $valReq->setAllowBlank(false);
        $valReq->setShowDropDown(true);
        $valReq->setFormula1('RequiredList');

        // Status Validation (Col I)
        $valStat = $sheet->getCell('I2')->getDataValidation();
        $valStat->setType(DataValidation::TYPE_LIST);
        $valStat->setErrorStyle(DataValidation::STYLE_STOP);
        $valStat->setAllowBlank(false);
        $valStat->setShowDropDown(true);
        $valStat->setFormula1('StatusList');

        // Apply Validations across rows (including dependent Section dropdown in Col B)
        for ($r = 2; $r <= 500; $r++) {
            $sheet->getCell("A{$r}")->setDataValidation(clone $valCat);
            $sheet->getCell("E{$r}")->setDataValidation(clone $valType);
            $sheet->getCell("G{$r}")->setDataValidation(clone $valDim);
            $sheet->getCell("H{$r}")->setDataValidation(clone $valReq);
            $sheet->getCell("I{$r}")->setDataValidation(clone $valStat);

            // Dependent Section Validation: dynamically loads category's sections based on cell A{$r}
            $valSec = new DataValidation();
            $valSec->setType(DataValidation::TYPE_LIST);
            $valSec->setErrorStyle(DataValidation::STYLE_STOP);
            $valSec->setAllowBlank(false);
            $valSec->setShowDropDown(true);
            $valSec->setShowErrorMessage(true);
            $valSec->setErrorTitle('Invalid Section');
            $valSec->setError('Please select a section that belongs to the selected Target Category.');
            $valSec->setFormula1("=INDIRECT(IF(\$A{$r}=\"\",\"AllSections\",LEFT(\$A{$r},FIND(\":\",\$A{$r}&\":\")-1)))");
            $sheet->getCell("B{$r}")->setDataValidation($valSec);
        }

        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);
        $fileName = 'bulk_questions_import_template.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function bulkImport(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            abort(403, 'Unauthorized.');
        }

        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls,csv,txt|max:10240',
        ]);

        $file = $request->file('import_file');
        $filePath = $file->getRealPath();

        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);
        } catch (\Exception $e) {
            return back()->with('error', 'Unable to parse spreadsheet file: ' . $e->getMessage());
        }

        if (empty($rows) || count($rows) < 2) {
            return back()->with('error', 'The uploaded file contains no data rows to import.');
        }

        $headerRow = array_shift($rows);
        $cleanHeader = [];
        foreach ($headerRow as $colLetter => $headerName) {
            $raw = (string)$headerName;
            $cleaned = preg_replace('/[^a-zA-Z0-9\s_]/', '', $raw);
            $cleaned = strtolower(trim(preg_replace('/\s+/', '_', $cleaned)));
            $cleaned = trim($cleaned, '_');
            $cleanHeader[$colLetter] = $cleaned;
        }

        $categories = SurveyCategory::all();
        $dimensions = PsychometricDimension::all();

        $isSuperAdmin = $user->isSuperAdmin();
        $targetUniversityId = $isSuperAdmin ? null : $user->university_id;

        $imported = 0;
        $skipped = 0;
        $rowErrors = [];
        $importedQuestionTexts = [];

        foreach ($rows as $rowNum => $row) {
            $data = [];
            foreach ($cleanHeader as $colLetter => $key) {
                if (!empty($key)) {
                    $data[$key] = isset($row[$colLetter]) ? trim((string)$row[$colLetter]) : '';
                }
            }

            // Skip completely blank rows
            if (empty(array_filter($data))) {
                continue;
            }

            // 1. Identify Question Text
            $questionText = $data['question_statement'] ?? $data['question_text'] ?? $data['question'] ?? $data['statement'] ?? '';
            if (empty($questionText)) {
                $skipped++;
                $rowErrors[] = "Row {$rowNum}: Question statement is required and was empty.";
                continue;
            }

            // 2. Identify Category
            $rawCat = $data['target_category_code_or_name'] ?? $data['target_category'] ?? $data['category'] ?? $data['category_code'] ?? '';
            $matchedCategory = null;

            if (!empty($rawCat)) {
                $codePart = strtolower(trim(explode(':', $rawCat)[0]));
                $matchedCategory = $categories->first(function ($c) use ($codePart, $rawCat) {
                    return strtolower($c->code) === $codePart 
                        || strcasecmp($c->name, $rawCat) === 0 
                        || stripos($rawCat, $c->code) !== false 
                        || stripos($rawCat, $c->name) !== false;
                });
            }

            if (!$matchedCategory) {
                $matchedCategory = $categories->first();
            }

            if (!$matchedCategory) {
                $skipped++;
                $rowErrors[] = "Row {$rowNum}: Could not find or assign a valid target survey category for '{$rawCat}'.";
                continue;
            }

            // 3. Identify Existing Section (Strictly validated under selected category)
            $sectionTitle = trim($data['section_title'] ?? $data['section'] ?? $data['section_name'] ?? '');
            if (empty($sectionTitle)) {
                $skipped++;
                $rowErrors[] = "Row {$rowNum}: Section Title is required. Please select a section belonging to '{$matchedCategory->name}'.";
                continue;
            }

            // Check if section exists specifically under the matched category
            $sectionQuery = SurveySection::where('category_id', $matchedCategory->id)
                ->where('title', $sectionTitle);

            if (!$isSuperAdmin && $targetUniversityId) {
                $sectionQuery->where(function ($q) use ($targetUniversityId) {
                    $q->whereNull('university_id')
                      ->orWhere('university_id', $targetUniversityId)
                      ->orWhereHas('universities', function ($uq) use ($targetUniversityId) {
                          $uq->where('universities.id', $targetUniversityId);
                      });
                });
            }

            $section = $sectionQuery->first();

            // If not found in the selected category, check if it belongs to a different category
            if (!$section) {
                $otherCatSec = SurveySection::where('title', $sectionTitle)->with('category')->first();
                if ($otherCatSec && $otherCatSec->category) {
                    $skipped++;
                    $rowErrors[] = "Row {$rowNum}: Section '{$sectionTitle}' belongs to '{$otherCatSec->category->name}' ({$otherCatSec->category->code}), not '{$matchedCategory->name}'. Please select a section belonging to the selected category.";
                } else {
                    $skipped++;
                    $rowErrors[] = "Row {$rowNum}: Section '{$sectionTitle}' does not exist under '{$matchedCategory->name}'. Please select an existing section from the dropdown list.";
                }
                continue;
            }

            // 3b. Duplicate Question Statement Check within same category
            $normalizedText = strtolower(trim($questionText));

            if (isset($importedQuestionTexts[$matchedCategory->id][$normalizedText])) {
                $skipped++;
                $rowErrors[] = "Row {$rowNum}: Duplicate question avoided. A question with the same statement ('" . Str::limit($questionText, 35) . "') was already imported under '{$matchedCategory->name}' in this file.";
                continue;
            }

            $dbDupQuery = Question::whereHas('section', function ($sq) use ($matchedCategory) {
                $sq->where('category_id', $matchedCategory->id);
            })->whereRaw('LOWER(TRIM(question_text)) = ?', [$normalizedText]);

            if (!$isSuperAdmin && $targetUniversityId) {
                $dbDupQuery->where(function ($q) use ($targetUniversityId) {
                    $q->whereNull('university_id')
                      ->orWhere('university_id', $targetUniversityId)
                      ->orWhereHas('universities', function ($uq) use ($targetUniversityId) {
                          $uq->where('universities.id', $targetUniversityId);
                      });
                });
            }

            if ($dbDupQuery->exists()) {
                $skipped++;
                $rowErrors[] = "Row {$rowNum}: Duplicate question avoided. A question with statement '" . Str::limit($questionText, 35) . "' already exists under '{$matchedCategory->name}'.";
                continue;
            }

            // 4. Identify Question Type
            $rawType = strtolower(trim($data['question_type'] ?? $data['type'] ?? ''));
            $type = 'short_text';

            if (str_contains($rawType, 'single') || str_contains($rawType, 'radio')) {
                $type = 'single_choice';
            } elseif (str_contains($rawType, 'multi') || str_contains($rawType, 'checkbox')) {
                $type = 'multiple_choice';
            } elseif (str_contains($rawType, 'dropdown') || str_contains($rawType, 'select')) {
                $type = 'dropdown';
            } elseif (str_contains($rawType, 'likert')) {
                $type = 'likert';
            } elseif (str_contains($rawType, 'rating') || str_contains($rawType, 'star')) {
                $type = 'rating';
            } elseif (str_contains($rawType, 'long') || str_contains($rawType, 'textarea')) {
                $type = 'long_text';
            } elseif (str_contains($rawType, 'voice') || str_contains($rawType, 'audio')) {
                $type = 'voice';
            } else {
                $type = 'short_text';
            }

            // 5. Options Validation for Choice types & Likert Scale Defaulting
            $rawOptions = $data['options_separated_by_for_choicedropdown'] ?? $data['options'] ?? $data['choices'] ?? '';
            $optionsList = [];
            if (in_array($type, ['single_choice', 'multiple_choice', 'dropdown'])) {
                $splitOptions = preg_split('/[;\r\n|]+/', (string)$rawOptions);
                $optionsList = array_values(array_filter(array_map('trim', $splitOptions)));

                if (empty($optionsList)) {
                    $skipped++;
                    $rowErrors[] = "Row {$rowNum}: Question type '{$type}' requires options to be provided in the Options column.";
                    continue;
                }
            } elseif ($type === 'likert') {
                $splitOptions = preg_split('/[;\r\n|]+/', (string)$rawOptions);
                $optionsList = array_values(array_filter(array_map('trim', $splitOptions)));

                if (empty($optionsList)) {
                    $optionsList = [
                        'Strongly Disagree',
                        'Disagree',
                        'Neutral',
                        'Agree',
                        'Strongly Agree'
                    ];
                }
            }

            // 6. Psychometric Dimension
            $rawDim = trim($data['psychometric_dimension_code_or_name'] ?? $data['psychometric_dimension'] ?? $data['dimension'] ?? '');
            $dimensionId = null;
            if (!empty($rawDim) && strcasecmp($rawDim, 'none') !== 0 && strcasecmp($rawDim, 'null') !== 0) {
                $dimCode = '';
                $dimName = $rawDim;
                if (str_contains($rawDim, ':')) {
                    $parts = explode(':', $rawDim, 2);
                    $dimCode = trim($parts[0]);
                    $dimName = trim($parts[1]);
                }
                $matchedDim = $dimensions->first(function ($d) use ($rawDim, $dimCode, $dimName) {
                    return ($dimCode && strcasecmp($d->code, $dimCode) === 0)
                        || strcasecmp($d->code, $rawDim) === 0
                        || strcasecmp($d->name, $dimName) === 0
                        || strcasecmp($d->name, $rawDim) === 0
                        || stripos($d->name, $dimName) !== false;
                });
                if ($matchedDim) {
                    $dimensionId = $matchedDim->id;
                }
            }

            // 7. Required & Active status
            $rawReq = strtolower(trim($data['must_to_answer_required'] ?? $data['must_to_answer'] ?? $data['required'] ?? $data['is_required'] ?? ''));
            $isRequired = in_array($rawReq, ['yes', '1', 'true', 'required', 'y']);

            $rawStat = strtolower(trim($data['status'] ?? $data['is_active'] ?? 'active'));
            $isActive = !in_array($rawStat, ['disabled', 'no', '0', 'false', 'inactive']);

            // 8. Tags
            $rawTags = trim($data['tags_commaseparated'] ?? $data['tags'] ?? $data['tag'] ?? '');
            $tagsArray = [];
            if (!empty($rawTags)) {
                $tagsArray = array_values(array_filter(array_map('trim', explode(',', $rawTags))));
            }

            $helpText = trim($data['help_text_instructions'] ?? $data['help_text'] ?? $data['instructions'] ?? '');

            $maxQOrder = Question::where('section_id', $section->id)->max('order') ?? 0;

            try {
                $question = Question::create([
                    'section_id' => $section->id,
                    'university_id' => $targetUniversityId,
                    'created_by' => $user->id,
                    'question_text' => $questionText,
                    'help_text' => !empty($helpText) ? $helpText : null,
                    'type' => $type,
                    'dimension_id' => $dimensionId,
                    'is_required' => $isRequired,
                    'is_active' => $isActive,
                    'tags' => !empty($tagsArray) ? $tagsArray : null,
                    'order' => $maxQOrder + 1,
                ]);

                if (!$isSuperAdmin && $targetUniversityId) {
                    $question->universities()->sync([$targetUniversityId]);
                } else {
                    $question->universities()->sync([]);
                }

                // Create options
                if (!empty($optionsList)) {
                    $optIdx = 1;
                    foreach ($optionsList as $optText) {
                        QuestionOption::create([
                            'question_id' => $question->id,
                            'option_text' => $optText,
                            'value' => Str::slug($optText) ?: 'opt_' . $optIdx,
                            'score' => $optIdx * 20,
                            'order' => $optIdx,
                        ]);
                        $optIdx++;
                    }
                }

                AuditLog::log('imported_question_bulk', 'Question', $question->id);
                $imported++;
                $importedQuestionTexts[$matchedCategory->id][$normalizedText] = true;
            } catch (\Exception $e) {
                $skipped++;
                $rowErrors[] = "Row {$rowNum} ('" . Str::limit($questionText, 30) . "'): Database error - " . $e->getMessage();
            }
        }

        $scopeMsg = $isSuperAdmin 
            ? 'as Common / Global questions accessible to all institutions' 
            : 'assigned to your institution (' . ($user->university->name ?? 'Your University') . ')';

        $summaryMsg = "Bulk Question Import completed! Successfully imported {$imported} question(s) {$scopeMsg}.";
        if ($skipped > 0) {
            $summaryMsg .= " Skipped {$skipped} invalid row(s).";
        }

        $res = back()->with('success', $summaryMsg);
        if (!empty($rowErrors)) {
            $res->with('import_errors', $rowErrors);
        }

        return $res;
    }
}

