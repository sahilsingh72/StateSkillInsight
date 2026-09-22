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
        $user = auth()->user();
        $uniId = ($user && !$user->isSuperAdmin()) ? $user->university_id : null;

        $metrics = $analyticsService->getOverviewMetrics($uniId);
        $chartData = $analyticsService->getChartData($uniId);

        $respQuery = Respondent::query();
        $surveyQuery = Survey::query();

        if ($uniId) {
            $respQuery->where('university_id', $uniId);
            $surveyQuery->where('university_id', $uniId);
        }

        $recentRespondents = $respQuery->latest()->take(8)->get();
        $university = $user ? ($user->university ?? University::find($uniId)) : University::first();
        $surveys = $surveyQuery->get();

        return view('admin.dashboard.index', compact('metrics', 'chartData', 'recentRespondents', 'university', 'surveys'));
    }
}
