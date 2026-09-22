<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\University;

class ReportController extends Controller
{
    public function index()
    {
        return view('admin.reports.index');
    }

    public function generate(string $type)
    {
        $user = auth()->user();
        $university = ($user && !$user->isSuperAdmin()) ? ($user->university ?? University::find($user->university_id)) : University::first();
        AuditLog::log("generated_{$type}_report", 'Report', null);

        return view('admin.reports.view', compact('university', 'type'));
    }
}
