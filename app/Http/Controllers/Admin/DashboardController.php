<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\Respondent;
use App\Models\RespondentSurvey;
use App\Models\Survey;
use App\Models\University;
use App\Services\AnalyticsService;

class DashboardController extends Controller
{
    public function index(AnalyticsService $analyticsService)
    {
        $metrics = $analyticsService->getOverviewMetrics();
        $chartData = $analyticsService->getChartData();
        $recentRespondents = Respondent::latest()->take(8)->get();
        $university = University::first();
        $surveys = Survey::all();

        return view('admin.dashboard.index', compact('metrics', 'chartData', 'recentRespondents', 'university', 'surveys'));
    }
}
