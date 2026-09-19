<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;

class AuditLogController extends Controller
{
    public function index()
    {
        if (!auth()->user() || !auth()->user()->isSuperAdmin()) {
            abort(403, 'Unauthorized access. System Audit Logs are reserved for Super Administrator role only.');
        }

        $logs = AuditLog::with('user')->latest()->paginate(25);
        return view('admin.audit_logs.index', compact('logs'));
    }
}
