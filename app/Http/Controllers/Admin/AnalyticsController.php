<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\InterventionRule;
use App\Models\Respondent;
use App\Models\RespondentIntervention;
use App\Models\RespondentSurvey;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    protected AnalyticsService $analyticsService;

    public function __construct(AnalyticsService $analyticsService)
    {
        $this->analyticsService = $analyticsService;
    }

    private function getUniId(): ?int
    {
        $user = auth()->user();
        return ($user && !$user->isSuperAdmin()) ? $user->university_id : null;
    }

    public function overview()
    {
        $uniId = $this->getUniId();
        $metrics = $this->analyticsService->getOverviewMetrics($uniId);
        $chartData = $this->analyticsService->getChartData($uniId);
        return view('admin.analytics.overview', compact('metrics', 'chartData'));
    }

    public function category1()
    {
        $query = Respondent::where('category_code', 'cat_1');
        if ($uniId = $this->getUniId()) {
            $query->where('university_id', $uniId);
        }
        $respondents = $query->paginate(15);
        return view('admin.analytics.category1', compact('respondents'));
    }

    public function category2()
    {
        $query = Respondent::where('category_code', 'cat_2');
        if ($uniId = $this->getUniId()) {
            $query->where('university_id', $uniId);
        }
        $respondents = $query->paginate(15);
        return view('admin.analytics.category2', compact('respondents'));
    }

    public function category3()
    {
        $query = Respondent::where('category_code', 'cat_3');
        if ($uniId = $this->getUniId()) {
            $query->where('university_id', $uniId);
        }
        $respondents = $query->paginate(15);
        return view('admin.analytics.category3', compact('respondents'));
    }

    public function category4()
    {
        $query = Respondent::where('category_code', 'cat_4');
        if ($uniId = $this->getUniId()) {
            $query->where('university_id', $uniId);
        }
        $respondents = $query->paginate(15);
        return view('admin.analytics.category4', compact('respondents'));
    }

    public function crossAnalysis(Request $request)
    {
        $filters = $request->only(['category_code', 'programme', 'department', 'graduation_year']);
        $uniId = $this->getUniId();
        $analysisResult = $this->analyticsService->runCrossAnalysis($filters, $uniId);

        return view('admin.analytics.cross_analysis', compact('analysisResult', 'filters'));
    }

    public function comparison()
    {
        $matrix = $this->analyticsService->getCategoryComparison();
        return view('admin.analytics.comparison', compact('matrix'));
    }

    public function trends()
    {
        return view('admin.analytics.trends');
    }

    public function interventions()
    {
        $query = RespondentIntervention::with(['rule', 'respondentSurvey.respondent']);
        if ($uniId = $this->getUniId()) {
            $query->whereHas('respondentSurvey.respondent', function ($q) use ($uniId) {
                $q->where('university_id', $uniId);
            });
        }
        $interventions = $query->paginate(20);
        $rules = InterventionRule::all();

        return view('admin.analytics.interventions', compact('interventions', 'rules'));
    }
}
