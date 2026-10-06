<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Respondent;
use App\Models\RespondentSurvey;
use App\Models\University;
use Illuminate\Http\Request;

class RespondentController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        $query = Respondent::with(['university.parent', 'respondentSurveys.survey']);

        if ($user && !$user->isSuperAdmin()) {
            $userUniId = $user->university_id;
            $childIds = University::where('parent_id', $userUniId)->pluck('id')->toArray();
            $allowedUniIds = array_merge([$userUniId], $childIds);

            if ($request->filled('university_id') && in_array($request->university_id, $allowedUniIds)) {
                $selId = $request->university_id;
                $subChildIds = University::where('parent_id', $selId)->pluck('id')->toArray();
                $query->whereIn('university_id', array_merge([$selId], $subChildIds));
            } else {
                $query->whereIn('university_id', $allowedUniIds);
            }

            $universities = University::whereIn('id', $allowedUniIds)->orderBy('name')->get();
        } else {
            if ($request->filled('university_id')) {
                $selectedId = $request->university_id;
                $childIds = University::where('parent_id', $selectedId)->pluck('id')->toArray();
                $query->whereIn('university_id', array_merge([$selectedId], $childIds));
            }
            $universities = University::orderBy('name')->get();
        }

        if ($request->filled('category_code')) {
            $query->where('category_code', $request->category_code);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', '%' . $search . '%')
                  ->orWhere('email', 'LIKE', '%' . $search . '%')
                  ->orWhere('mobile', 'LIKE', '%' . $search . '%')
                  ->orWhere('university_student_alumni_id', 'LIKE', '%' . $search . '%');
            });
        }

        $respondents = $query->latest()->paginate(20)->withQueryString();

        return view('admin.respondents.index', compact('respondents', 'universities'));
    }

    public function show(Respondent $respondent)
    {
        $user = auth()->user();
        if ($user && !$user->isSuperAdmin()) {
            $childIds = University::where('parent_id', $user->university_id)->pluck('id')->toArray();
            $allowedIds = array_merge([$user->university_id], $childIds);
            if (!in_array($respondent->university_id, $allowedIds)) {
                abort(403, 'Unauthorized. You do not have access to respondent records from other institutions.');
            }
        }

        $respondent->load(['university.parent', 'respondentSurveys.responses.question', 'respondentSurveys.scores', 'respondentSurveys.interventions.rule']);
        return view('admin.respondents.show', compact('respondent'));
    }
}

