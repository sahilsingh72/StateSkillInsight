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

    public function overview()
    {
        $metrics = $this->analyticsService->getOverviewMetrics();
        $chartData = $this->analyticsService->getChartData();
        return view('admin.analytics.overview', compact('metrics', 'chartData'));
    }

    public function category1()
    {
        $respondents = Respondent::where('category_code', 'cat_1')->paginate(15);
        return view('admin.analytics.category1', compact('respondents'));
    }

    public function category2()
    {
        $respondents = Respondent::where('category_code', 'cat_2')->paginate(15);
        return view('admin.analytics.category2', compact('respondents'));
    }

    public function category3()
    {
        $respondents = Respondent::where('category_code', 'cat_3')->paginate(15);
        return view('admin.analytics.category3', compact('respondents'));
    }

    public function category4()
    {
        $respondents = Respondent::where('category_code', 'cat_4')->paginate(15);
        return view('admin.analytics.category4', compact('respondents'));
    }

    public function crossAnalysis(Request $request)
    {
        $filters = $request->only(['category_code', 'programme', 'department', 'graduation_year']);
        $analysisResult = $this->analyticsService->runCrossAnalysis($filters);

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
        $interventions = RespondentIntervention::with(['rule', 'respondentSurvey.respondent'])->paginate(20);
        $rules = InterventionRule::all();

        return view('admin.analytics.interventions', compact('interventions', 'rules'));
    }
}
