<?php

namespace App\Services;

use App\Models\Respondent;
use App\Models\RespondentSurvey;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportService
{
    /**
     * Export raw respondent data to CSV.
     */
    public function exportRespondentsCsv(?string $categoryCode = null): StreamedResponse
    {
        $fileName = 'respondents_export_' . date('Y_m_d_His') . '.csv';

        $user = auth()->user();
        $query = Respondent::with('university');

        if ($user && !$user->isSuperAdmin()) {
            $query->where('university_id', $user->university_id);
        }

        if ($categoryCode) {
            $query->where('category_code', $categoryCode);
        }

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ];

        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'ID', 'Name', 'Email', 'Mobile', 'Gender', 'Category Code',
                'Programme', 'Department', 'Graduation Year', 'Employment Status', 'City', 'Country', 'Created At'
            ]);

            $query->chunk(100, function ($respondents) use ($file) {
                foreach ($respondents as $r) {
                    fputcsv($file, [
                        $r->id,
                        $r->name,
                        $r->email,
                        $r->mobile,
                        $r->gender,
                        $r->category_code,
                        $r->programme,
                        $r->department,
                        $r->graduation_year,
                        $r->employment_status,
                        $r->current_city,
                        $r->country,
                        $r->created_at->format('Y-m-d H:i'),
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
