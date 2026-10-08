<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\PsychometricDimension;
use App\Models\Respondent;
use App\Models\RespondentScore;
use App\Models\RespondentSurvey;
use App\Models\SurveyCategory;
use App\Models\University;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    protected AnalyticsService $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    public function index(Request $request)
    {
        $user = auth()->user();
        $universities = ($user && $user->isSuperAdmin()) ? University::orderBy('name')->get() : collect();
        $selectedUniversityId = $request->get('university_id');

        return view('admin.reports.index', compact('universities', 'selectedUniversityId'));
    }

    public function generate(string $type, Request $request)
    {
        $user = auth()->user();
        $universities = ($user && $user->isSuperAdmin()) ? University::orderBy('name')->get() : collect();
        
        $uniId = null;
        $university = null;

        // Resolve University
        if ($user && $user->isSuperAdmin()) {
            if ($request->filled('university_id')) {
                $university = University::find($request->university_id);
                $uniId = $university?->id;
            }
            // If superadmin doesn't pass university_id, $uniId stays null (State-Wide / All Colleges)
        } elseif ($user && !$user->isSuperAdmin()) {
            $university = $user->university ?? ($user->university_id ? University::find($user->university_id) : null);
            $uniId = $university?->id;
        }

        if (!$university) {
            $university = new University([
                'name' => 'All Universities & Colleges (State-Wide Consolidated)',
                'code' => 'STATE',
                'state' => 'Odisha',
            ]);
            $university->id = null;
        }

        $normalizedType = strtolower(str_replace(['_', '-'], '', $type));

        // Map report configuration
        $reportConfig = $this->getReportConfig($normalizedType, $university);
        $categoryCode = $reportConfig['category_code'];

        // Overview & Category Analytics across scope (null $uniId = all colleges)
        $overviewMetrics = $this->analyticsService->getOverviewMetrics($uniId);
        $cat1Metrics = $this->analyticsService->getCategory1Metrics($uniId);
        $cat2Metrics = $this->analyticsService->getCategory2Metrics($uniId);
        $cat3Metrics = $this->analyticsService->getCategory3Metrics($uniId);
        $cat4Metrics = $this->analyticsService->getCategory4Metrics($uniId);

        // Cohort sample sizes
        $respQuery = Respondent::query();
        if ($uniId) {
            $respQuery->where('university_id', $uniId);
        }
        if ($categoryCode) {
            $respQuery->where('category_code', $categoryCode);
        }

        $totalCohort = (clone $respQuery)->count();
        $completedCohort = (clone $respQuery)->whereHas('surveys', function ($q) {
            $q->where('status', 'completed');
        })->count();
        $completionRate = $totalCohort > 0 ? round(($completedCohort / $totalCohort) * 100, 1) : 0;

        // Fetch Dimension details & dynamic scores
        $dimensionsQuery = PsychometricDimension::query();
        if ($categoryCode) {
            $dimensionsQuery->where('category_code', $categoryCode);
        }
        $rawDimensions = $dimensionsQuery->orderBy('id')->get();

        $dimensions = [];
        foreach ($rawDimensions as $dim) {
            $scoreQ = RespondentScore::where('dimension_id', $dim->id);
            if ($uniId) {
                $scoreQ->whereHas('respondentSurvey.respondent', function ($r) use ($uniId) {
                    $r->where('university_id', $uniId);
                });
            }
            $avgVal = $scoreQ->avg('score');
            $evalCount = $scoreQ->count();
            $numScore = $avgVal !== null ? round((float)$avgVal, 1) : null;

            $band = $this->getInterpretationBand($numScore);

            $dimensions[] = [
                'id' => $dim->id,
                'name' => $dim->name,
                'code' => $dim->code,
                'category_code' => $dim->category_code,
                'score' => $numScore,
                'band_label' => $band['label'],
                'band_class' => $band['badge_class'],
                'text_class' => $band['text_class'],
                'bar_class' => $band['bar_class'],
                'eval_count' => $evalCount,
            ];
        }

        // Key Cards data
        $keyCards = $this->buildKeyCards($normalizedType, $overviewMetrics, $cat1Metrics, $cat2Metrics, $cat3Metrics, $cat4Metrics);

        // Dynamic Findings Narrative
        $summaryFindings = $this->generateExecutiveSummary($normalizedType, $university, $totalCohort, $completedCohort, $completionRate, $dimensions, $overviewMetrics);

        // Dynamic Recommendations
        $recommendations = $this->generateRecommendations($normalizedType, $dimensions, $overviewMetrics, $cat1Metrics, $cat2Metrics, $cat3Metrics, $cat4Metrics);

        // Programme Breakdown
        $programmeBreakdown = (clone $respQuery)->whereNotNull('programme')
            ->select('programme', DB::raw('count(*) as total'))
            ->groupBy('programme')
            ->orderByDesc('total')
            ->take(6)
            ->get();

        // If State-wide (All Colleges), fetch University breakdown
        $universityBreakdown = collect();
        if (!$uniId) {
            $universityBreakdown = Respondent::whereNotNull('university_id')
                ->with('university')
                ->select('university_id', DB::raw('count(*) as total'))
                ->groupBy('university_id')
                ->orderByDesc('total')
                ->take(8)
                ->get();
        }

        AuditLog::log("generated_{$type}_report", 'Report', $uniId);

        return view('admin.reports.view', compact(
            'university',
            'universities',
            'uniId',
            'type',
            'normalizedType',
            'reportConfig',
            'totalCohort',
            'completedCohort',
            'completionRate',
            'overviewMetrics',
            'dimensions',
            'keyCards',
            'summaryFindings',
            'recommendations',
            'programmeBreakdown',
            'universityBreakdown',
            'cat1Metrics',
            'cat2Metrics',
            'cat3Metrics',
            'cat4Metrics'
        ));
    }

    protected function getReportConfig(string $type, University $university): array
    {
        $configs = [
            'executive' => [
                'title' => 'Executive Institutional Research & Employability Intelligence Report',
                'subtitle' => 'Comprehensive multi-category synthesis across alumni professional practice, job transition friction, and undergraduate readiness.',
                'category_code' => null,
                'category_name' => 'All Stakeholder Categories (Composite)',
                'code_suffix' => 'EXEC',
                'lead_color' => '#1e40af',
            ],
            'category1' => [
                'title' => 'Category 1: Working Alumni & Professional Practice Evidence Report',
                'subtitle' => 'Assessment of curriculum relevance, workplace skill retention, leadership adaptability, and practical knowledge transfer.',
                'category_code' => 'cat_1',
                'category_name' => 'Category 1: Working Alumni',
                'code_suffix' => 'CAT1-ALUMNI',
                'lead_color' => '#1e40af',
            ],
            'cat1' => [
                'title' => 'Category 1: Working Alumni & Professional Practice Evidence Report',
                'subtitle' => 'Assessment of curriculum relevance, workplace skill retention, leadership adaptability, and practical knowledge transfer.',
                'category_code' => 'cat_1',
                'category_name' => 'Category 1: Working Alumni',
                'code_suffix' => 'CAT1-ALUMNI',
                'lead_color' => '#1e40af',
            ],
            'category2' => [
                'title' => 'Category 2: Job-Seeking Alumni & Employability Gap Analysis Report',
                'subtitle' => 'Diagnostic analysis of job-search resilience, technical assessment friction, self-directed upskilling, and career support needs.',
                'category_code' => 'cat_2',
                'category_name' => 'Category 2: Job-Seeking Alumni',
                'code_suffix' => 'CAT2-CAREER',
                'lead_color' => '#0d9488',
            ],
            'cat2' => [
                'title' => 'Category 2: Job-Seeking Alumni & Employability Gap Analysis Report',
                'subtitle' => 'Diagnostic analysis of job-search resilience, technical assessment friction, self-directed upskilling, and career support needs.',
                'category_code' => 'cat_2',
                'category_name' => 'Category 2: Job-Seeking Alumni',
                'code_suffix' => 'CAT2-CAREER',
                'lead_color' => '#0d9488',
            ],
            'category3' => [
                'title' => 'Category 3: Current Students & Graduate Readiness Intelligence Report',
                'subtitle' => 'Evaluation of undergraduate theoretical foundations, practical lab competence, and emerging Generative AI adaptability.',
                'category_code' => 'cat_3',
                'category_name' => 'Category 3: Current Students',
                'code_suffix' => 'CAT3-STUDENTS',
                'lead_color' => '#d97706',
            ],
            'cat3' => [
                'title' => 'Category 3: Current Students & Graduate Readiness Intelligence Report',
                'subtitle' => 'Evaluation of undergraduate theoretical foundations, practical lab competence, and emerging Generative AI adaptability.',
                'category_code' => 'cat_3',
                'category_name' => 'Category 3: Current Students',
                'code_suffix' => 'CAT3-STUDENTS',
                'lead_color' => '#d97706',
            ],
            'category4' => [
                'title' => 'Category 4: Interrupted Learners & Re-engagement Pathways Report',
                'subtitle' => 'Analysis of academic interruption factors, retained foundational capabilities, vocational preference, and re-entry intent.',
                'category_code' => 'cat_4',
                'category_name' => 'Category 4: Interrupted Students',
                'code_suffix' => 'CAT4-REENTRY',
                'lead_color' => '#dc2626',
            ],
            'cat4' => [
                'title' => 'Category 4: Interrupted Learners & Re-engagement Pathways Report',
                'subtitle' => 'Analysis of academic interruption factors, retained foundational capabilities, vocational preference, and re-entry intent.',
                'category_code' => 'cat_4',
                'category_name' => 'Category 4: Interrupted Students',
                'code_suffix' => 'CAT4-REENTRY',
                'lead_color' => '#dc2626',
            ],
        ];

        return $configs[$type] ?? $configs['executive'];
    }

    protected function getInterpretationBand(?float $score): array
    {
        if ($score === null) {
            return [
                'label' => 'Awaiting Data',
                'badge_class' => 'badge bg-light text-secondary border',
                'text_class' => 'text-muted',
                'bar_class' => 'bg-secondary',
            ];
        }
        if ($score >= 75) {
            return [
                'label' => 'Advanced / Benchmark',
                'badge_class' => 'badge bg-success-subtle text-success border border-success-subtle',
                'text_class' => 'text-success',
                'bar_class' => 'bg-success',
            ];
        }
        if ($score >= 60) {
            return [
                'label' => 'Proficient / Developing',
                'badge_class' => 'badge bg-primary-subtle text-primary border border-primary-subtle',
                'text_class' => 'text-primary',
                'bar_class' => 'bg-primary',
            ];
        }
        if ($score >= 45) {
            return [
                'label' => 'Moderate / Intervention Needed',
                'badge_class' => 'badge bg-warning-subtle text-warning-emphasis border border-warning-subtle',
                'text_class' => 'text-warning-emphasis',
                'bar_class' => 'bg-warning',
            ];
        }
        return [
            'label' => 'Critical Priority Gap',
            'badge_class' => 'badge bg-danger-subtle text-danger border border-danger-subtle',
            'text_class' => 'text-danger',
            'bar_class' => 'bg-danger',
        ];
    }

    protected function buildKeyCards(string $type, array $overview, array $cat1, array $cat2, array $cat3, array $cat4): array
    {
        switch ($type) {
            case 'category1':
            case 'cat1':
                return [
                    [
                        'title' => 'Technical Capital Index',
                        'score' => $cat1['tech_score'] ?? ($cat1['has_data'] ? $cat1['tech_score'] : null),
                        'benchmark' => 'Industry Expectation: 75.0',
                        'status' => $cat1['tech_status'] ?? 'Baseline Evaluation',
                        'status_class' => $cat1['tech_status_class'] ?? 'text-muted',
                    ],
                    [
                        'title' => 'Technological Adaptability',
                        'score' => $cat1['adapt_score'] ?? null,
                        'benchmark' => 'Tool Agility: 70.0',
                        'status' => $cat1['adapt_status'] ?? 'Baseline Evaluation',
                        'status_class' => $cat1['adapt_status_class'] ?? 'text-muted',
                    ],
                    [
                        'title' => 'Management & Leadership',
                        'score' => $cat1['lead_score'] ?? null,
                        'benchmark' => 'Senior Benchmark: 65.0',
                        'status' => $cat1['lead_status'] ?? 'Baseline Evaluation',
                        'status_class' => $cat1['lead_status_class'] ?? 'text-muted',
                    ],
                    [
                        'title' => 'Curriculum Relevance Gap',
                        'score' => $cat1['gap_score'] ?? null,
                        'benchmark' => 'Target Gap: < 15.0',
                        'status' => $cat1['gap_status'] ?? 'Calculated Deficit',
                        'status_class' => $cat1['gap_status_class'] ?? 'text-muted',
                    ],
                ];

            case 'category2':
            case 'cat2':
                return [
                    [
                        'title' => 'Transition Confidence Index',
                        'score' => $cat2['experience_score'] ?? null,
                        'benchmark' => 'Confidence Target: 70.0',
                        'status' => $cat2['experience_status'] ?? 'Assessment Metric',
                        'status_class' => $cat2['experience_class'] ?? 'text-muted',
                    ],
                    [
                        'title' => 'Job-Search Resilience Index',
                        'score' => $cat2['resilience_score'] ?? null,
                        'benchmark' => 'Persistence Target: 70.0',
                        'status' => $cat2['resilience_status'] ?? 'Persistence Metric',
                        'status_class' => $cat2['resilience_class'] ?? 'text-muted',
                    ],
                    [
                        'title' => 'Self-Directed Upskilling',
                        'score' => $cat2['employability_score'] ?? null,
                        'benchmark' => 'Continuous Learning: 65.0',
                        'status' => $cat2['employability_status'] ?? 'Certification Rate',
                        'status_class' => $cat2['employability_class'] ?? 'text-muted',
                    ],
                    [
                        'title' => 'Recruitment Friction Gap',
                        'score' => $cat2['conversion_gap'] ?? null,
                        'benchmark' => 'Target Friction: < 20.0',
                        'status' => $cat2['conversion_gap_status'] ?? 'Conversion Index',
                        'status_class' => $cat2['conversion_gap_class'] ?? 'text-muted',
                    ],
                ];

            case 'category3':
            case 'cat3':
                return [
                    [
                        'title' => 'Graduate Readiness Index (GRI)',
                        'score' => $cat3['academic_foundation'] ?? ($overview['avg_readiness'] > 0 ? $overview['avg_readiness'] : null),
                        'benchmark' => 'Graduate Target: 75.0',
                        'status' => $cat3['academic_status'] ?? 'Overall Foundation',
                        'status_class' => $cat3['academic_class'] ?? 'text-muted',
                    ],
                    [
                        'title' => 'Digital & AI Adaptability',
                        'score' => $cat3['ai_readiness'] ?? ($overview['avg_ai_readiness'] > 0 ? $overview['avg_ai_readiness'] : null),
                        'benchmark' => 'AI Benchmark: 70.0',
                        'status' => $cat3['ai_status'] ?? 'Tool Fluency',
                        'status_class' => $cat3['ai_class'] ?? 'text-muted',
                    ],
                    [
                        'title' => 'Practical Competence Index',
                        'score' => $cat3['practical_exposure'] ?? ($overview['avg_practical_readiness'] > 0 ? $overview['avg_practical_readiness'] : null),
                        'benchmark' => 'Industry Lab Target: 70.0',
                        'status' => $cat3['practical_status'] ?? 'Hands-on Exposure',
                        'status_class' => $cat3['practical_class'] ?? 'text-muted',
                    ],
                    [
                        'title' => 'Career Goal Clarity',
                        'score' => $cat3['career_clarity'] ?? ($overview['avg_career_clarity'] > 0 ? $overview['avg_career_clarity'] : null),
                        'benchmark' => 'Clarity Target: 70.0',
                        'status' => $cat3['career_status'] ?? 'Pathway Certainty',
                        'status_class' => $cat3['career_class'] ?? 'text-muted',
                    ],
                ];

            case 'category4':
            case 'cat4':
                return [
                    [
                        'title' => 'Re-engagement Readiness Index',
                        'score' => $cat4['reentry_interest'] ?? null,
                        'benchmark' => 'Willingness Target: 60.0',
                        'status' => $cat4['reentry_status'] ?? 'Re-entry Intent',
                        'status_class' => $cat4['reentry_class'] ?? 'text-muted',
                    ],
                    [
                        'title' => 'Retained Capability Index',
                        'score' => $cat4['retained_capability'] ?? null,
                        'benchmark' => 'Core Retention: 60.0',
                        'status' => $cat4['retained_status'] ?? 'Academic Continuity',
                        'status_class' => $cat4['retained_class'] ?? 'text-muted',
                    ],
                    [
                        'title' => 'Apprenticeship Affinity',
                        'score' => $cat4['apprenticeship_readiness'] ?? null,
                        'benchmark' => 'Vocational Target: 65.0',
                        'status' => $cat4['apprenticeship_status'] ?? 'Work-based Learning',
                        'status_class' => $cat4['apprenticeship_class'] ?? 'text-muted',
                    ],
                    [
                        'title' => 'Self-Employment Potential',
                        'score' => $cat4['entrepreneurial_potential'] ?? null,
                        'benchmark' => 'Livelihood Orientation',
                        'status' => $cat4['entrepreneurial_status'] ?? 'Enterprise Mindset',
                        'status_class' => $cat4['entrepreneurial_class'] ?? 'text-muted',
                    ],
                ];

            case 'executive':
            default:
                return [
                    [
                        'title' => 'Graduate Readiness Index (GRI)',
                        'score' => $overview['avg_readiness'] > 0 ? $overview['avg_readiness'] : null,
                        'benchmark' => 'State Composite Target: 75.0',
                        'status' => $overview['avg_readiness'] >= 70 ? 'Optimal Readiness' : ($overview['avg_readiness'] >= 50 ? 'Moderate Foundation' : 'Priority Attention'),
                        'status_class' => $overview['avg_readiness'] >= 70 ? 'text-success' : ($overview['avg_readiness'] >= 50 ? 'text-primary' : 'text-danger'),
                    ],
                    [
                        'title' => 'AI & Digital Fluency Index',
                        'score' => $overview['avg_ai_readiness'] > 0 ? $overview['avg_ai_readiness'] : null,
                        'benchmark' => 'Digital Target: 70.0',
                        'status' => $overview['avg_ai_readiness'] >= 65 ? 'High Tool Fluency' : ($overview['avg_ai_readiness'] >= 50 ? 'Emerging Adoption' : 'Early Stage Literacy'),
                        'status_class' => $overview['avg_ai_readiness'] >= 65 ? 'text-success' : 'text-warning-emphasis',
                    ],
                    [
                        'title' => 'Practical Industry Exposure',
                        'score' => $overview['avg_practical_readiness'] > 0 ? $overview['avg_practical_readiness'] : null,
                        'benchmark' => 'Lab Exposure: 70.0',
                        'status' => $overview['avg_practical_readiness'] >= 65 ? 'Well Aligned' : ($overview['avg_practical_readiness'] >= 50 ? 'Intervention Required' : 'Critical Practical Deficit'),
                        'status_class' => $overview['avg_practical_readiness'] >= 65 ? 'text-success' : 'text-danger',
                    ],
                    [
                        'title' => 'Institutional Survey Completion',
                        'score' => $overview['completion_rate'] > 0 ? $overview['completion_rate'] : null,
                        'benchmark' => 'Target Sample Reach: > 75%',
                        'status' => $overview['completion_rate'] >= 70 ? 'High Statistical Confidence' : ($overview['completion_rate'] >= 40 ? 'Moderate Sample Size' : 'Active Intake Period'),
                        'status_class' => $overview['completion_rate'] >= 70 ? 'text-success' : 'text-primary',
                    ],
                ];
        }
    }

    protected function generateExecutiveSummary(
        string $type,
        University $university,
        int $totalCohort,
        int $completedCohort,
        float $completionRate,
        array $dimensions,
        array $overview
    ): string {
        $isAllColleges = empty($university->id);
        $uniName = $isAllColleges ? "all universities and colleges across the state" : $university->name;

        if ($totalCohort === 0) {
            return "The Institutional Research Portal is currently gathering empirical data for {$uniName}. As respondent survey submissions progress, aggregate psychometric scores, competence indices, and comparative cohort benchmarks will dynamically populate in this section.";
        }

        $validDimScores = array_filter(array_column($dimensions, 'score'), fn($s) => $s !== null);
        $avgScore = count($validDimScores) > 0 ? round(array_sum($validDimScores) / count($validDimScores), 1) : $overview['avg_readiness'];

        switch ($type) {
            case 'category1':
            case 'cat1':
                return "Empirical data collected from {$completedCohort} verified working alumni across {$uniName} shows an aggregate competence score of {$avgScore}/100. Graduates currently active in the corporate and public sectors report strong theoretical mastery but recommend immediate enhancements in contemporary technology stacks, project management methodologies, and continuous workplace tool adaptability.";

            case 'category2':
            case 'cat2':
                return "Analysis across {$completedCohort} job-seeking alumni across {$uniName} indicates an overall transition readiness index of {$avgScore}/100. While persistence in applying to opportunities remains positive, data indicates clear bottlenecks during technical assessment rounds and live coding/case evaluations. Targeted technical finishing schools and structured mock interviews are strongly recommended.";

            case 'category3':
            case 'cat3':
                return "The current undergraduate student cohort across {$uniName} demonstrates a foundational readiness score of {$avgScore}/100 across evaluated dimensions. Findings highlight strong classroom conceptual learning, alongside a pronounced requirement to modernize laboratory exercises with real-world industry tools and embedded Generative AI productivity workflows.";

            case 'category4':
            case 'cat4':
                return "Diagnostics from {$completedCohort} interrupted students across {$uniName} demonstrate an average re-engagement capability index of {$avgScore}/100. A significant proportion of respondents indicate high interest in completing modular degrees, provided flexible credit-transfer mechanisms, evening/weekend bridge classes, and work-integrated apprenticeships are made accessible.";

            case 'executive':
            default:
                return "The state-wide empirical survey data for {$uniName} ({$completedCohort} completed surveys with a {$completionRate}% completion rate) reveals an overall Graduate Readiness Index of {$avgScore}/100. While students and alumni exhibit robust academic foundations, strategic interventions are required to address the practical industry exposure deficit and standardize Generative AI tool proficiency across all academic disciplines.";
        }
    }

    protected function generateRecommendations(
        string $type,
        array $dimensions,
        array $overview,
        array $cat1,
        array $cat2,
        array $cat3,
        array $cat4
    ): array {
        switch ($type) {
            case 'category1':
            case 'cat1':
                return [
                    [
                        'title' => 'Industry-Curated Capstone Projects',
                        'description' => 'Mandate that final-year projects in all professional disciplines be co-designed and evaluated with active industry practitioners to ensure workplace readiness.',
                        'badge' => 'High Priority',
                        'badge_class' => 'badge bg-danger-subtle text-danger',
                    ],
                    [
                        'title' => 'Alumni Mentorship Network',
                        'description' => 'Establish a structured 1-on-1 mentorship channel connecting senior working alumni with graduating students for domain insights and career navigation.',
                        'badge' => 'Strategic',
                        'badge_class' => 'badge bg-primary-subtle text-primary',
                    ],
                    [
                        'title' => 'Continuous Professional Development Credits',
                        'description' => 'Offer micro-credential certifications in emerging software engineering, cloud architectures, and data science for recent alumni.',
                        'badge' => 'Curriculum',
                        'badge_class' => 'badge bg-success-subtle text-success',
                    ],
                ];

            case 'category2':
            case 'cat2':
                return [
                    [
                        'title' => 'Intensive Placement Finishing Bootcamps',
                        'description' => 'Deploy 8-week intensive technical problem-solving, live coding, and behavioral interview bootcamps specifically for unplaced alumni.',
                        'badge' => 'Urgent Action',
                        'badge_class' => 'badge bg-danger-subtle text-danger',
                    ],
                    [
                        'title' => 'AI-Powered Resume & Portfolio Optimization',
                        'description' => 'Integrate automated ATS resume screening tools and GitHub/portfolio reviews to maximize interview conversion rates.',
                        'badge' => 'Employability',
                        'badge_class' => 'badge bg-primary-subtle text-primary',
                    ],
                    [
                        'title' => 'Direct Industry Hiring Drives',
                        'description' => 'Organize specialized lateral and off-campus recruitment expos with state MSME and IT industry consortia.',
                        'badge' => 'Placement',
                        'badge_class' => 'badge bg-warning-subtle text-warning-emphasis',
                    ],
                ];

            case 'category3':
            case 'cat3':
                return [
                    [
                        'title' => 'Practical Lab Overhaul & Modernization',
                        'description' => 'Transition lab manuals from obsolete paper scripts to modern GitHub-hosted interactive coding environments and simulated industrial setups.',
                        'badge' => 'Senate Reform',
                        'badge_class' => 'badge bg-danger-subtle text-danger',
                    ],
                    [
                        'title' => 'Mandatory 2-Credit AI & Data Literacy Course',
                        'description' => 'Introduce an institution-wide course covering Prompt Engineering, Python data analysis, and ethical AI tooling across all undergraduate streams.',
                        'badge' => 'Curriculum',
                        'badge_class' => 'badge bg-primary-subtle text-primary',
                    ],
                    [
                        'title' => 'Mandatory Pre-Final Year Internships',
                        'description' => 'Institute a minimum 6-week credited summer internship requirement with state research bodies, startups, or industrial partners.',
                        'badge' => 'Accreditation',
                        'badge_class' => 'badge bg-success-subtle text-success',
                    ],
                ];

            case 'category4':
            case 'cat4':
                return [
                    [
                        'title' => 'Flexible Multi-Entry / Multi-Exit Credit Bank',
                        'description' => 'Implement National Credit Framework (NCrF) policies allowing discontinued learners to resume their degree with prior learning recognition.',
                        'badge' => 'Policy Reform',
                        'badge_class' => 'badge bg-danger-subtle text-danger',
                    ],
                    [
                        'title' => 'Vocational Bridge & Apprenticeship Programs',
                        'description' => 'Offer skill-based vocational certificates and paid apprenticeship tracks in alignment with state skill development missions.',
                        'badge' => 'Livelihood',
                        'badge_class' => 'badge bg-primary-subtle text-primary',
                    ],
                    [
                        'title' => 'Dedicated Re-entry Counseling Desks',
                        'description' => 'Establish counseling hotlines to assist discontinued students with fee concessions, academic credit evaluation, and re-enrolment support.',
                        'badge' => 'Student Welfare',
                        'badge_class' => 'badge bg-success-subtle text-success',
                    ],
                ];

            case 'executive':
            default:
                return [
                    [
                        'title' => 'Systemic Curriculum Modernization for Industry 4.0',
                        'description' => 'Update university syllabus every 2 years in consultation with state industrial councils, embedding practical software tools and data analytics into core modules.',
                        'badge' => 'Senate Mandate',
                        'badge_class' => 'badge bg-danger-subtle text-danger',
                    ],
                    [
                        'title' => 'Institutional AI & Digital Fluency Integration',
                        'description' => 'Deploy foundational Generative AI courses and digital lab simulators across engineering, management, sciences, and humanities departments.',
                        'badge' => 'Academic Council',
                        'badge_class' => 'badge bg-primary-subtle text-primary',
                    ],
                    [
                        'title' => 'Comprehensive Career Readiness & Transition Finishing Schools',
                        'description' => 'Institute pre-placement technical drill tracks, communication workshops, and psychometric assessment diagnostics starting from semester 5.',
                        'badge' => 'Employability',
                        'badge_class' => 'badge bg-warning-subtle text-warning-emphasis',
                    ],
                    [
                        'title' => 'Inclusive Re-engagement & Credit Transfer Framework',
                        'description' => 'Establish formal institutional pathways for interrupted students to complete modular degrees via hybrid and distance education.',
                        'badge' => 'Inclusion Policy',
                        'badge_class' => 'badge bg-success-subtle text-success',
                    ],
                ];
        }
    }
}

