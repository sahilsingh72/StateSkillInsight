<?php

namespace App\Services;

use App\Models\PsychometricDimension;
use App\Models\Respondent;
use App\Models\RespondentScore;
use App\Models\RespondentSurvey;
use App\Models\SurveyCategory;
use Illuminate\Support\Facades\DB;

class AnalyticsService
{
    /**
     * Get high-level overview metrics for main dashboard.
     */
    public function getOverviewMetrics(?int $universityId = null): array
    {
        $respQuery = Respondent::query();
        $surveyQuery = RespondentSurvey::where('status', 'completed');

        if ($universityId) {
            $respQuery->where('university_id', $universityId);
            $surveyQuery->whereHas('respondent', function ($q) use ($universityId) {
                $q->where('university_id', $universityId);
            });
        }

        $totalRespondents = $respQuery->count();
        $totalCompleted = $surveyQuery->count();
        $completionRate = ($totalRespondents > 0) ? round(($totalCompleted / $totalRespondents) * 100, 1) : 0;

        $cat1Count = (clone $respQuery)->where('category_code', 'cat_1')->count();
        $cat2Count = (clone $respQuery)->where('category_code', 'cat_2')->count();
        $cat3Count = (clone $respQuery)->where('category_code', 'cat_3')->count();
        $cat4Count = (clone $respQuery)->where('category_code', 'cat_4')->count();

        $todaySubmissions = (clone $surveyQuery)->whereDate('completed_at', now()->today())->count();
        $weekSubmissions = (clone $surveyQuery)->where('completed_at', '>=', now()->subDays(7))->count();
        $monthSubmissions = (clone $surveyQuery)->where('completed_at', '>=', now()->subDays(30))->count();

        $avgReadiness = RespondentScore::whereNull('dimension_id')->avg('score');
        
        $aiDim = PsychometricDimension::where('code', 'dim_cat3_ai')->first();
        $avgAi = $aiDim ? RespondentScore::where('dimension_id', $aiDim->id)->avg('score') : null;

        $pracDim = PsychometricDimension::where('code', 'dim_cat3_prac')->first();
        $avgPrac = $pracDim ? RespondentScore::where('dimension_id', $pracDim->id)->avg('score') : null;

        $careerDim = PsychometricDimension::where('code', 'dim_cat2_conf')->orWhere('code', 'dim_cat3_read')->first();
        $avgCareer = $careerDim ? RespondentScore::where('dimension_id', $careerDim->id)->avg('score') : null;

        return [
            'total_respondents' => $totalRespondents,
            'total_completed' => $totalCompleted,
            'completion_rate' => $completionRate,
            'cat1_count' => $cat1Count,
            'cat2_count' => $cat2Count,
            'cat3_count' => $cat3Count,
            'cat4_count' => $cat4Count,
            'today_submissions' => $todaySubmissions,
            'week_submissions' => $weekSubmissions,
            'month_submissions' => $monthSubmissions,
            'avg_readiness' => $avgReadiness !== null ? round((float)$avgReadiness, 1) : 0.0,
            'avg_ai_readiness' => $avgAi !== null ? round((float)$avgAi, 1) : 0.0,
            'avg_practical_readiness' => $avgPrac !== null ? round((float)$avgPrac, 1) : 0.0,
            'avg_career_clarity' => $avgCareer !== null ? round((float)$avgCareer, 1) : 0.0,
        ];
    }

    /**
     * Get datasets formatted for Chart.js graphics.
     */
    public function getChartData(?int $universityId = null): array
    {
        $respQuery = Respondent::query();
        if ($universityId) {
            $respQuery->where('university_id', $universityId);
        }

        $categoryDonut = [
            'labels' => ['Working Alumni', 'Job-Seeking Alumni', 'Current Students', 'Interrupted Students'],
            'data' => [
                (clone $respQuery)->where('category_code', 'cat_1')->count(),
                (clone $respQuery)->where('category_code', 'cat_2')->count(),
                (clone $respQuery)->where('category_code', 'cat_3')->count(),
                (clone $respQuery)->where('category_code', 'cat_4')->count(),
            ],
            'backgroundColor' => ['#1e40af', '#0d9488', '#d97706', '#dc2626'],
        ];

        // Group actual completions by day over the last 7 days
        $days = collect(range(6, 0))->map(function($i) {
            return now()->subDays($i)->format('D');
        })->toArray();

        $trendData = collect(range(6, 0))->map(function($i) use ($universityId) {
            $date = now()->subDays($i)->toDateString();
            $q = RespondentSurvey::where('status', 'completed')->whereDate('completed_at', $date);
            if ($universityId) {
                $q->whereHas('respondent', fn($r) => $r->where('university_id', $universityId));
            }
            return $q->count();
        })->toArray();

        $completionTrend = [
            'labels' => $days,
            'data' => $trendData,
        ];

        // Top 6 programmes in the database
        $progCounts = (clone $respQuery)->whereNotNull('programme')
            ->select('programme', DB::raw('count(*) as total'))
            ->groupBy('programme')
            ->orderByDesc('total')
            ->take(6)
            ->get();

        $disciplineDistribution = [
            'labels' => $progCounts->pluck('programme')->toArray(),
            'data' => $progCounts->pluck('total')->toArray(),
        ];

        // Readiness scores per dimension
        $allDims = PsychometricDimension::take(6)->get();
        $dimLabels = [];
        $dimData = [];
        foreach ($allDims as $dim) {
            $dimLabels[] = $dim->name;
            $avgScore = RespondentScore::where('dimension_id', $dim->id);
            if ($universityId) {
                $avgScore->whereHas('respondentSurvey.respondent', fn($r) => $r->where('university_id', $universityId));
            }
            $val = $avgScore->avg('score');
            $dimData[] = $val !== null ? round((float)$val, 1) : 0;
        }

        $readinessBar = [
            'labels' => $dimLabels,
            'data' => $dimData,
        ];

        return [
            'categoryDonut' => $categoryDonut,
            'completionTrend' => $completionTrend,
            'disciplineDistribution' => $disciplineDistribution,
            'readinessBar' => $readinessBar,
        ];
    }

    /**
     * Perform cross-analysis filter query.
     */
    public function runCrossAnalysis(array $filters, ?int $universityId = null): array
    {
        $query = Respondent::query();

        if ($universityId) {
            $query->where('university_id', $universityId);
        }

        if (!empty($filters['category_code'])) {
            $query->where('category_code', $filters['category_code']);
        }
        if (!empty($filters['programme'])) {
            $query->where('programme', 'LIKE', '%' . $filters['programme'] . '%');
        }
        if (!empty($filters['department'])) {
            $query->where('department', 'LIKE', '%' . $filters['department'] . '%');
        }
        if (!empty($filters['graduation_year'])) {
            $query->where('graduation_year', $filters['graduation_year']);
        }

        $count = $query->count();
        $respondents = $query->take(20)->get();

        // Calculate dynamic averages for filtered respondents
        $respIds = $query->pluck('id');
        $surveyIds = RespondentSurvey::whereIn('respondent_id', $respIds)->pluck('id');

        $avgScore = RespondentScore::whereIn('respondent_survey_id', $surveyIds)->whereNull('dimension_id')->avg('score');
        $aiDim = PsychometricDimension::where('code', 'dim_cat3_ai')->first();
        $aiScore = $aiDim ? RespondentScore::whereIn('respondent_survey_id', $surveyIds)->where('dimension_id', $aiDim->id)->avg('score') : null;
        $pracDim = PsychometricDimension::where('code', 'dim_cat3_prac')->first();
        $pracScore = $pracDim ? RespondentScore::whereIn('respondent_survey_id', $surveyIds)->where('dimension_id', $pracDim->id)->avg('score') : null;
        $careerDim = PsychometricDimension::where('code', 'dim_cat2_conf')->first();
        $careerScore = $careerDim ? RespondentScore::whereIn('respondent_survey_id', $surveyIds)->where('dimension_id', $careerDim->id)->avg('score') : null;

        return [
            'filtered_count' => $count,
            'avg_score' => $avgScore !== null ? round((float)$avgScore, 1) : 0.0,
            'ai_readiness' => $aiScore !== null ? round((float)$aiScore, 1) : 0.0,
            'practical_readiness' => $pracScore !== null ? round((float)$pracScore, 1) : 0.0,
            'career_clarity' => $careerScore !== null ? round((float)$careerScore, 1) : 0.0,
            'respondents' => $respondents,
        ];
    }

    /**
     * Get comparison matrix between all 4 categories.
     */
    public function getCategoryComparison(): array
    {
        $dimensions = ['Technical Foundation', 'Digital & AI Readiness', 'Practical/Industry Exposure', 'Learning Agility', 'Career Confidence'];

        $getCatScores = function(string $catCode) {
            $dims = PsychometricDimension::where('category_code', $catCode)->get();
            if ($dims->isEmpty()) {
                return [0, 0, 0, 0, 0];
            }
            $scores = [];
            foreach ($dims as $d) {
                $avg = RespondentScore::where('dimension_id', $d->id)->avg('score');
                $scores[] = $avg !== null ? round((float)$avg, 1) : 0;
            }
            while (count($scores) < 5) {
                $scores[] = 0;
            }
            return array_slice($scores, 0, 5);
        };

        return [
            'dimensions' => $dimensions,
            'cat_1' => $getCatScores('cat_1'),
            'cat_2' => $getCatScores('cat_2'),
            'cat_3' => $getCatScores('cat_3'),
            'cat_4' => $getCatScores('cat_4'),
        ];
    }

    /**
     * Category 1 (Working Alumni) dynamic analytics metrics.
     */
    public function getCategory1Metrics(?int $universityId = null): array
    {
        $respCount = Respondent::where('category_code', 'cat_1');
        if ($universityId) $respCount->where('university_id', $universityId);
        $hasRespondents = $respCount->count() > 0;

        $dimTech = PsychometricDimension::where('category_code', 'cat_1')->where('code', 'dim_cat1_tech')->first();
        $dimAdapt = PsychometricDimension::where('category_code', 'cat_1')->where('code', 'dim_cat1_adapt')->first();
        $dimLead = PsychometricDimension::where('category_code', 'cat_1')->where('code', 'dim_cat1_lead')->first();

        $queryScore = function($dimId) use ($universityId) {
            if (!$dimId) return null;
            $q = RespondentScore::where('dimension_id', $dimId);
            if ($universityId) {
                $q->whereHas('respondentSurvey.respondent', fn($r) => $r->where('university_id', $universityId));
            }
            return $q->avg('score');
        };

        $rawTech = $queryScore($dimTech?->id);
        $rawAdapt = $queryScore($dimAdapt?->id);
        $rawLead = $queryScore($dimLead?->id);

        if (!$hasRespondents || ($rawTech === null && $rawAdapt === null && $rawLead === null)) {
            return [
                'has_data' => false,
                'tech_name' => 'Technical Capital Index',
                'tech_score' => null,
                'tech_status' => 'Awaiting Responses',
                'tech_status_class' => 'text-muted',
                'tech_status_icon' => 'bi-clock',

                'adapt_name' => 'Technological Adaptability',
                'adapt_score' => null,
                'adapt_status' => 'Awaiting Responses',
                'adapt_status_class' => 'text-muted',

                'lead_name' => 'Management & Leadership',
                'lead_score' => null,
                'lead_status' => 'Awaiting Responses',
                'lead_status_class' => 'text-muted',

                'gap_score' => null,
                'gap_status' => 'Awaiting Responses',
                'gap_status_class' => 'text-muted',
                'gap_status_icon' => 'bi-clock',
            ];
        }

        $techScore = $rawTech !== null ? round((float)$rawTech, 1) : 0.0;
        $adaptScore = $rawAdapt !== null ? round((float)$rawAdapt, 1) : 0.0;
        $leadScore = $rawLead !== null ? round((float)$rawLead, 1) : 0.0;
        $gapScore = round(max(0, min(100, 100 - (($techScore * 0.4) + ($adaptScore * 0.4) + ($leadScore * 0.2)))), 1);

        $techStatus = ($techScore >= 75) ? 'Core Foundations Strong' : (($techScore >= 60) ? 'Moderate Foundations' : 'Foundational Gaps');
        $techClass = ($techScore >= 75) ? 'text-success' : (($techScore >= 60) ? 'text-warning' : 'text-danger');
        $techIcon = ($techScore >= 75) ? 'bi-check-circle' : 'bi-exclamation-triangle';

        $adaptStatus = ($adaptScore >= 75) ? 'Tool Adoption Speed' : (($adaptScore >= 60) ? 'Moderate Adaptability' : 'Low Tool Adaptability');
        $adaptClass = ($adaptScore >= 75) ? 'text-primary' : (($adaptScore >= 60) ? 'text-warning' : 'text-danger');

        $leadStatus = ($leadScore >= 70) ? 'Strong Leadership Ability' : (($leadScore >= 60) ? 'Executive Orientation' : 'Developing Leadership');
        $leadClass = ($leadScore >= 70) ? 'text-success' : (($leadScore >= 60) ? 'text-muted' : 'text-warning');

        $gapStatus = ($gapScore >= 25) ? 'Requires Modernization' : (($gapScore >= 15) ? 'Moderate Industry Gap' : 'Minimal Gap / Well Aligned');
        $gapClass = ($gapScore >= 25) ? 'text-danger' : (($gapScore >= 15) ? 'text-warning' : 'text-success');
        $gapIcon = ($gapScore >= 25) ? 'bi-exclamation-triangle' : 'bi-check-circle';

        return [
            'has_data' => true,
            'tech_name' => 'Technical Capital Index',
            'tech_score' => $techScore,
            'tech_status' => $techStatus,
            'tech_status_class' => $techClass,
            'tech_status_icon' => $techIcon,

            'adapt_name' => 'Technological Adaptability',
            'adapt_score' => $adaptScore,
            'adapt_status' => $adaptStatus,
            'adapt_status_class' => $adaptClass,

            'lead_name' => 'Management & Leadership',
            'lead_score' => $leadScore,
            'lead_status' => $leadStatus,
            'lead_status_class' => $leadClass,

            'gap_score' => $gapScore,
            'gap_status' => $gapStatus,
            'gap_status_class' => $gapClass,
            'gap_status_icon' => $gapIcon,
        ];
    }

    /**
     * Category 2 (Job-Seeking Alumni) dynamic analytics metrics.
     */
    public function getCategory2Metrics(?int $universityId = null): array
    {
        $respCount = Respondent::where('category_code', 'cat_2');
        if ($universityId) $respCount->where('university_id', $universityId);
        $hasRespondents = $respCount->count() > 0;

        $dimConf = PsychometricDimension::where('category_code', 'cat_2')->where('code', 'dim_cat2_conf')->first();
        $dimResil = PsychometricDimension::where('category_code', 'cat_2')->where('code', 'dim_cat2_resil')->first();
        $dimSkill = PsychometricDimension::where('category_code', 'cat_2')->where('code', 'dim_cat2_skill')->first();

        $queryScore = function($dimId) use ($universityId) {
            if (!$dimId) return null;
            $q = RespondentScore::where('dimension_id', $dimId);
            if ($universityId) {
                $q->whereHas('respondentSurvey.respondent', fn($r) => $r->where('university_id', $universityId));
            }
            return $q->avg('score');
        };

        $rawConf = $queryScore($dimConf?->id);
        $rawResil = $queryScore($dimResil?->id);
        $rawSkill = $queryScore($dimSkill?->id);

        if (!$hasRespondents || ($rawConf === null && $rawResil === null && $rawSkill === null)) {
            return [
                'has_data' => false,
                'conversion_gap' => null,
                'conversion_gap_status' => 'Awaiting Responses',
                'conversion_gap_class' => 'text-muted',
                'conversion_gap_icon' => 'bi-clock',

                'experience_score' => null,
                'experience_status' => 'Awaiting Responses',
                'experience_class' => 'text-muted',
                'experience_icon' => 'bi-clock',

                'resilience_score' => null,
                'resilience_status' => 'Awaiting Responses',
                'resilience_class' => 'text-muted',
                'resilience_icon' => 'bi-clock',

                'employability_score' => null,
                'employability_status' => 'Awaiting Responses',
                'employability_class' => 'text-muted',
                'employability_icon' => 'bi-clock',
            ];
        }

        $resilScore = $rawResil !== null ? round((float)$rawResil, 1) : 0.0;
        $skillScore = $rawSkill !== null ? round((float)$rawSkill, 1) : 0.0;
        $confScore = $rawConf !== null ? round((float)$rawConf, 1) : 0.0;
        $conversionGap = round(max(0, min(100, 100 - $confScore * 0.9)), 1);

        $convClass = ($conversionGap >= 40) ? 'text-danger' : (($conversionGap >= 25) ? 'text-warning' : 'text-success');
        $convStatus = ($conversionGap >= 40) ? 'Highest Bottleneck Stage' : (($conversionGap >= 25) ? 'Moderate Friction' : 'Low Friction');
        $convIcon = ($conversionGap >= 40) ? 'bi-exclamation-triangle' : 'bi-check-circle';

        $expClass = ($confScore >= 70) ? 'text-success' : (($confScore >= 50) ? 'text-warning' : 'text-danger');
        $expStatus = ($confScore >= 70) ? 'Strong Assessment Confidence' : (($confScore >= 50) ? 'Moderate Confidence' : 'Support Required');
        $expIcon = ($confScore >= 70) ? 'bi-check-circle' : 'bi-exclamation-circle';

        $resilClass = ($resilScore >= 70) ? 'text-primary' : (($resilScore >= 50) ? 'text-warning' : 'text-danger');
        $resilStatus = ($resilScore >= 70) ? 'Continuous Learning Agility' : (($resilScore >= 50) ? 'Moderate Persistence' : 'Support Required');
        $resilIcon = ($resilScore >= 70) ? 'bi-arrow-repeat' : 'bi-exclamation-circle';

        $empClass = ($skillScore >= 70) ? 'text-success' : (($skillScore >= 50) ? 'text-primary' : 'text-warning');
        $empStatus = ($skillScore >= 70) ? 'High Upskilling Rate' : (($skillScore >= 50) ? 'Online Certification Upskilling' : 'Basic Upskilling');
        $empIcon = ($skillScore >= 70) ? 'bi-check-circle' : 'bi-laptop';

        return [
            'has_data' => true,
            'conversion_gap' => $conversionGap,
            'conversion_gap_status' => $convStatus,
            'conversion_gap_class' => $convClass,
            'conversion_gap_icon' => $convIcon,

            'experience_score' => $confScore,
            'experience_status' => $expStatus,
            'experience_class' => $expClass,
            'experience_icon' => $expIcon,

            'resilience_score' => $resilScore,
            'resilience_status' => $resilStatus,
            'resilience_class' => $resilClass,
            'resilience_icon' => $resilIcon,

            'employability_score' => $skillScore,
            'employability_status' => $empStatus,
            'employability_class' => $empClass,
            'employability_icon' => $empIcon,
        ];
    }

    /**
     * Category 3 (Current Students) dynamic analytics metrics.
     */
    public function getCategory3Metrics(?int $universityId = null): array
    {
        $respCount = Respondent::where('category_code', 'cat_3');
        if ($universityId) $respCount->where('university_id', $universityId);
        $hasRespondents = $respCount->count() > 0;

        $dimRead = PsychometricDimension::where('category_code', 'cat_3')->where('code', 'dim_cat3_read')->first();
        $dimAi = PsychometricDimension::where('category_code', 'cat_3')->where('code', 'dim_cat3_ai')->first();
        $dimPrac = PsychometricDimension::where('category_code', 'cat_3')->where('code', 'dim_cat3_prac')->first();

        $queryScore = function($dimId) use ($universityId) {
            if (!$dimId) return null;
            $q = RespondentScore::where('dimension_id', $dimId);
            if ($universityId) {
                $q->whereHas('respondentSurvey.respondent', fn($r) => $r->where('university_id', $universityId));
            }
            return $q->avg('score');
        };

        $rawRead = $queryScore($dimRead?->id);
        $rawAi = $queryScore($dimAi?->id);
        $rawPrac = $queryScore($dimPrac?->id);

        if (!$hasRespondents || ($rawRead === null && $rawAi === null && $rawPrac === null)) {
            return [
                'has_data' => false,
                'academic_foundation' => null,
                'academic_status' => 'Awaiting Responses',
                'academic_class' => 'text-muted',
                'academic_icon' => 'bi-clock',

                'practical_exposure' => null,
                'practical_status' => 'Awaiting Responses',
                'practical_class' => 'text-muted',
                'practical_icon' => 'bi-clock',

                'ai_readiness' => null,
                'ai_status' => 'Awaiting Responses',
                'ai_class' => 'text-muted',
                'ai_icon' => 'bi-clock',

                'career_clarity' => null,
                'career_status' => 'Awaiting Responses',
                'career_class' => 'text-muted',
                'career_icon' => 'bi-clock',
            ];
        }

        $acadScore = $rawRead !== null ? round((float)$rawRead, 1) : 0.0;
        $pracScore = $rawPrac !== null ? round((float)$rawPrac, 1) : 0.0;
        $aiScore = $rawAi !== null ? round((float)$rawAi, 1) : 0.0;
        $careerScore = $rawRead !== null ? round((float)$rawRead, 1) : 0.0;

        $acadClass = ($acadScore >= 70) ? 'text-success' : (($acadScore >= 50) ? 'text-primary' : 'text-warning');
        $acadStatus = ($acadScore >= 70) ? 'Strong Theory Confidence' : (($acadScore >= 50) ? 'Moderate Foundations' : 'Needs Development');
        $acadIcon = ($acadScore >= 70) ? 'bi-check-circle' : 'bi-info-circle';

        $pracClass = ($pracScore >= 70) ? 'text-success' : (($pracScore >= 50) ? 'text-warning' : 'text-danger');
        $pracStatus = ($pracScore >= 70) ? 'Strong Practical Exposure' : (($pracScore >= 50) ? 'Moderate Exposure' : 'Major Gap Identified');
        $pracIcon = ($pracScore >= 70) ? 'bi-check-circle' : 'bi-exclamation-circle';

        $aiClass = ($aiScore >= 70) ? 'text-primary' : (($aiScore >= 50) ? 'text-info' : 'text-warning');
        $aiStatus = ($aiScore >= 70) ? 'Advanced Tool Fluency' : (($aiScore >= 50) ? 'Emerging Tool Usage' : 'Basic Literacy');
        $aiIcon = ($aiScore >= 70) ? 'bi-cpu' : 'bi-gear';

        $careerClass = ($careerScore >= 70) ? 'text-success' : (($careerScore >= 50) ? 'text-primary' : 'text-secondary');
        $careerStatus = ($careerScore >= 70) ? 'Clear Professional Goal' : (($careerScore >= 50) ? 'Emerging Career Clarity' : 'Exploring Options');
        $careerIcon = ($careerScore >= 70) ? 'bi-compass' : 'bi-search';

        return [
            'has_data' => true,
            'academic_foundation' => $acadScore,
            'academic_status' => $acadStatus,
            'academic_class' => $acadClass,
            'academic_icon' => $acadIcon,

            'practical_exposure' => $pracScore,
            'practical_status' => $pracStatus,
            'practical_class' => $pracClass,
            'practical_icon' => $pracIcon,

            'ai_readiness' => $aiScore,
            'ai_status' => $aiStatus,
            'ai_class' => $aiClass,
            'ai_icon' => $aiIcon,

            'career_clarity' => $careerScore,
            'career_status' => $careerStatus,
            'career_class' => $careerClass,
            'career_icon' => $careerIcon,
        ];
    }

    /**
     * Category 4 (Interrupted Students) dynamic analytics metrics.
     */
    public function getCategory4Metrics(?int $universityId = null): array
    {
        $respCount = Respondent::where('category_code', 'cat_4');
        if ($universityId) $respCount->where('university_id', $universityId);
        $hasRespondents = $respCount->count() > 0;

        $dimReentry = PsychometricDimension::where('category_code', 'cat_4')->where('code', 'dim_cat4_reentry')->first();
        $dimWork = PsychometricDimension::where('category_code', 'cat_4')->where('code', 'dim_cat4_work')->first();

        $queryScore = function($dimId) use ($universityId) {
            if (!$dimId) return null;
            $q = RespondentScore::where('dimension_id', $dimId);
            if ($universityId) {
                $q->whereHas('respondentSurvey.respondent', fn($r) => $r->where('university_id', $universityId));
            }
            return $q->avg('score');
        };

        $rawReentry = $queryScore($dimReentry?->id);
        $rawWork = $queryScore($dimWork?->id);

        if (!$hasRespondents || ($rawReentry === null && $rawWork === null)) {
            return [
                'has_data' => false,
                'reentry_interest' => null,
                'reentry_status' => 'Awaiting Responses',
                'reentry_class' => 'text-muted',
                'reentry_icon' => 'bi-clock',

                'retained_capability' => null,
                'retained_status' => 'Awaiting Responses',
                'retained_class' => 'text-muted',
                'retained_icon' => 'bi-clock',

                'apprenticeship_readiness' => null,
                'apprenticeship_status' => 'Awaiting Responses',
                'apprenticeship_class' => 'text-muted',
                'apprenticeship_icon' => 'bi-clock',

                'entrepreneurial_potential' => null,
                'entrepreneurial_status' => 'Awaiting Responses',
                'entrepreneurial_class' => 'text-muted',
                'entrepreneurial_icon' => 'bi-clock',
            ];
        }

        $reentryScore = $rawReentry !== null ? round((float)$rawReentry, 1) : 0.0;
        $retainedScore = $rawWork !== null ? round((float)$rawWork, 1) : 0.0;
        $apprenticeScore = round(min(100, $retainedScore * 1.1), 1);
        $entreScore = round(min(100, $reentryScore * 0.5), 1);

        $reentryClass = ($reentryScore >= 60) ? 'text-success' : (($reentryScore >= 40) ? 'text-warning' : 'text-danger');
        $reentryStatus = ($reentryScore >= 60) ? 'High Willingness to Complete' : (($reentryScore >= 40) ? 'Moderate Re-entry Interest' : 'Low Re-entry Intent');
        $reentryIcon = ($reentryScore >= 60) ? 'bi-arrow-repeat' : 'bi-info-circle';

        $retainedClass = ($retainedScore >= 60) ? 'text-primary' : (($retainedScore >= 40) ? 'text-warning' : 'text-danger');
        $retainedStatus = ($retainedScore >= 60) ? 'Foundational Knowledge Retained' : (($retainedScore >= 40) ? 'Partial Retention' : 'Bridge Support Required');
        $retainedIcon = ($retainedScore >= 60) ? 'bi-bookmark-check' : 'bi-exclamation-triangle';

        $appClass = ($apprenticeScore >= 60) ? 'text-warning' : (($apprenticeScore >= 40) ? 'text-primary' : 'text-secondary');
        $appStatus = ($apprenticeScore >= 60) ? 'Work-based Skill Preference' : (($apprenticeScore >= 40) ? 'Moderate Readiness' : 'Pre-vocational Needed');
        $appIcon = ($apprenticeScore >= 60) ? 'bi-tools' : 'bi-briefcase';

        $entreClass = ($entreScore >= 40) ? 'text-info' : 'text-secondary';
        $entreStatus = ($entreScore >= 40) ? 'Self-Livelihood Interest' : 'Structured Employment Focus';
        $entreIcon = ($entreScore >= 40) ? 'bi-lightbulb' : 'bi-person';

        return [
            'has_data' => true,
            'reentry_interest' => $reentryScore,
            'reentry_status' => $reentryStatus,
            'reentry_class' => $reentryClass,
            'reentry_icon' => $reentryIcon,

            'retained_capability' => $retainedScore,
            'retained_status' => $retainedStatus,
            'retained_class' => $retainedClass,
            'retained_icon' => $retainedIcon,

            'apprenticeship_readiness' => $apprenticeScore,
            'apprenticeship_status' => $appStatus,
            'apprenticeship_class' => $appClass,
            'apprenticeship_icon' => $appIcon,

            'entrepreneurial_potential' => $entreScore,
            'entrepreneurial_status' => $entreStatus,
            'entrepreneurial_class' => $entreClass,
            'entrepreneurial_icon' => $entreIcon,
        ];
    }
}
