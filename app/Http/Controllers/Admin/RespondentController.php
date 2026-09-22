<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Respondent;
use App\Models\RespondentSurvey;
use Illuminate\Http\Request;

class RespondentController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Respondent::with(['university', 'respondentSurveys.survey']);

        if ($user && !$user->isSuperAdmin()) {
            $query->where('university_id', $user->university_id);
        }

        if ($request->has('category_code') && $request->category_code) {
            $query->where('category_code', $request->category_code);
        }
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                  ->orWhere('email', 'LIKE', '%' . $search . '%')
                  ->orWhere('university_student_alumni_id', 'LIKE', '%' . $search . '%');
            });
        }

        $respondents = $query->latest()->paginate(20);

        return view('admin.respondents.index', compact('respondents'));
    }

    public function show(Respondent $respondent)
    {
        $user = auth()->user();
        if ($user && !$user->isSuperAdmin() && $respondent->university_id !== $user->university_id) {
            abort(403, 'Unauthorized. You do not have access to respondent records from other institutions.');
        }

        $respondent->load(['university', 'respondentSurveys.responses.question', 'respondentSurveys.scores', 'respondentSurveys.interventions.rule']);
        return view('admin.respondents.show', compact('respondent'));
    }
}
