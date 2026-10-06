<?php

namespace App\Services;

use App\Models\Respondent;
use App\Models\RespondentSurvey;
use App\Models\University;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportService
{
    /**
     * Export raw respondent data to CSV.
     */
    public function exportRespondentsCsv(?string $categoryCode = null, ?int $universityId = null): StreamedResponse
    {
        $fileName = 'respondents_export_' . date('Y_m_d_His') . '.csv';

        $user = auth()->user();
        $query = Respondent::with('university');

        if ($user && !$user->isSuperAdmin()) {
            $userUniId = $user->university_id;
            $childIds = University::where('parent_id', $userUniId)->pluck('id')->toArray();
            $allowedUniIds = array_merge([$userUniId], $childIds);

            if ($universityId && in_array($universityId, $allowedUniIds)) {
                $subChildIds = University::where('parent_id', $universityId)->pluck('id')->toArray();
                $query->whereIn('university_id', array_merge([$universityId], $subChildIds));
            } else {
                $query->whereIn('university_id', $allowedUniIds);
            }
        } else {
            if ($universityId) {
                $childIds = University::where('parent_id', $universityId)->pluck('id')->toArray();
                $query->whereIn('university_id', array_merge([$universityId], $childIds));
            }
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
                'ID', 'Name', 'Email', 'Mobile', 'Institution', 'Gender', 'Category Code',
                'Programme', 'Department', 'Graduation Year', 'Employment Status', 'City', 'Country', 'Created At'
            ]);

            $query->chunk(100, function ($respondents) use ($file) {
                foreach ($respondents as $r) {
                    fputcsv($file, [
                        $r->id,
                        $r->name,
                        $r->email,
                        $r->mobile,
                        $r->university?->name ?? 'N/A',
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

