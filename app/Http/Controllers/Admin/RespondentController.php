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
        $query = Respondent::with('respondentSurveys.survey');

        if ($request->has('category_code') && $request->category_code) {
            $query->where('category_code', $request->category_code);
        }
        if ($request->has('search') && $request->search) {
            $query->where('name', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('email', 'LIKE', '%' . $request->search . '%')
                  ->orWhere('university_student_alumni_id', 'LIKE', '%' . $request->search . '%');
        }

        $respondents = $query->latest()->paginate(20);

        return view('admin.respondents.index', compact('respondents'));
    }

    public function show(Respondent $respondent)
    {
        $respondent->load(['respondentSurveys.responses.question', 'respondentSurveys.scores', 'respondentSurveys.interventions.rule']);
        return view('admin.respondents.show', compact('respondent'));
    }
}
