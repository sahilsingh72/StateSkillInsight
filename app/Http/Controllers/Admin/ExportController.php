<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Services\ExportService;
use Illuminate\Http\Request;

class ExportController extends Controller
{
    public function exportCsv(Request $request, ExportService $exportService)
    {
        $categoryCode = $request->query('category_code');
        $universityId = $request->query('university_id') ? (int)$request->query('university_id') : null;
        AuditLog::log('exported_csv_data', 'Export', null);

        return $exportService->exportRespondentsCsv($categoryCode, $universityId);
    }
}
