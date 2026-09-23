<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\University;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\NamedRange;

class UniversityController extends Controller
{
    public function index(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized. Institution Enrollment directory is reserved for Super Administrator role only.');
        }

        $query = University::with(['parent', 'colleges' => function ($q) {
            $q->orderBy('name', 'asc');
        }]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('short_name', 'LIKE', "%{$search}%")
                  ->orWhere('tagline', 'LIKE', "%{$search}%")
                  ->orWhere('email', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('parent_id')) {
            $query->where('parent_id', $request->parent_id);
        }

        if ($request->filled('state')) {
            $query->where('state', $request->state);
        }

        $sort = $request->get('sort', 'name_asc');
        match ($sort) {
            'oldest' => $query->oldest(),
            'latest' => $query->latest(),
            'name_desc' => $query->orderBy('name', 'desc'),
            'type' => $query->orderBy('type', 'asc'),
            default => $query->orderBy('name', 'asc'),
        };

        $perPage = (int) $request->get('per_page', 10);
        if (!in_array($perPage, [5, 10, 15, 25, 50, 100])) {
            $perPage = 10;
        }

        $institutions = $query->paginate($perPage)->withQueryString();
        $parentUniversities = University::where('type', 'university')->with('colleges')->orderBy('name')->get();
        $states = University::whereNotNull('state')->where('state', '!=', '')->distinct()->orderBy('state')->pluck('state');

        return view('admin.university.index', compact('institutions', 'parentUniversities', 'states'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized. Institution Enrollment is reserved for Super Administrator role only.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'required|string|max:50',
            'type' => 'required|in:ini,university,autonomous_college,affiliated_college,polytechnic_iti',
            'parent_id' => 'nullable|required_if:type,affiliated_college|exists:universities,id',
            'tagline' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'state' => 'nullable|string',
            'primary_color' => 'nullable|string|max:20',
            'secondary_color' => 'nullable|string|max:20',
        ]);

        if ($validated['type'] !== 'affiliated_college') {
            $validated['parent_id'] = null;
        }

        $institution = University::create($validated);
        AuditLog::log('enrolled_institution', 'University', $institution->id);

        return back()->with('success', 'Institution ('.$institution->name.') enrolled successfully!');
    }

    public function edit(?University $university = null)
    {
        $user = auth()->user();
        if (!$user || (!$user->isSuperAdmin() && !$user->hasRole('university_admin'))) {
            abort(403, 'Unauthorized access. University Profile & Branding Configurator is reserved for Super Administrator or University Administrator roles.');
        }

        if ($user->isSuperAdmin()) {
            $targetUniversity = $university ?? University::find($user->university_id) ?? University::first() ?? new University();
        } else {
            $targetUniversity = University::find($user->university_id) ?? $university ?? University::first() ?? new University();
        }

        $parentUniversities = University::where('type', 'university')->where('id', '!=', $targetUniversity->id ?? 0)->get();

        return view('admin.university.edit', [
            'university' => $targetUniversity,
            'parentUniversities' => $parentUniversities,
        ]);
    }

    public function update(Request $request, ?University $university = null)
    {
        $user = auth()->user();
        if (!$user || (!$user->isSuperAdmin() && !$user->hasRole('university_admin'))) {
            abort(403, 'Unauthorized access. University Profile & Branding Configurator is reserved for Super Administrator or University Administrator roles.');
        }

        if ($user->isSuperAdmin()) {
            $targetUniversity = $university ?? University::find($request->input('university_id')) ?? University::find($user->university_id) ?? University::first();
        } else {
            $targetUniversity = University::find($user->university_id) ?? $university;
        }

        if (!$targetUniversity) {
            return back()->with('error', 'Target institution not found.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'required|string|max:50',
            'type' => 'nullable|in:ini,university,autonomous_college,affiliated_college,polytechnic_iti',
            'parent_id' => 'nullable|exists:universities,id',
            'tagline' => 'nullable|string|max:255',
            'website' => 'nullable|url|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'state' => 'nullable|string',
            'country' => 'nullable|string',
            'primary_color' => 'required|string|max:20',
            'secondary_color' => 'required|string|max:20',
            'survey_header' => 'nullable|string|max:255',
            'footer_text' => 'nullable|string',
            'privacy_text' => 'nullable|string',
        ]);

        if ($request->has('programmes_text')) {
            $rawText = $request->input('programmes_text');
            if (is_string($rawText)) {
                $lines = preg_split('/[\r\n,]+/', $rawText);
                $validated['programmes'] = array_values(array_filter(array_map('trim', $lines)));
            }
        } elseif ($request->has('programmes') && is_array($request->input('programmes'))) {
            $validated['programmes'] = array_values(array_filter(array_map('trim', $request->input('programmes'))));
        }

        if ($request->has('departments_text')) {
            $rawText = $request->input('departments_text');
            if (is_string($rawText)) {
                $lines = explode("\n", $rawText);
                $deptMap = [];
                foreach ($lines as $line) {
                    $line = trim($line);
                    if (empty($line)) continue;
                    if (str_contains($line, ':') || str_contains($line, '=')) {
                        $parts = preg_split('/[:=]/', $line, 2);
                        $progKey = trim($parts[0]);
                        $deptsList = array_values(array_filter(array_map('trim', explode(',', $parts[1]))));
                        if (!empty($progKey) && !empty($deptsList)) {
                            $deptMap[$progKey] = $deptsList;
                        }
                    }
                }
                if (!empty($deptMap)) {
                    $validated['departments'] = $deptMap;
                }
            }
        } elseif ($request->has('departments') && is_array($request->input('departments'))) {
            $validated['departments'] = $request->input('departments');
        }

        if (isset($validated['type']) && $validated['type'] !== 'affiliated_college') {
            $validated['parent_id'] = null;
        }

        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('branding', 'public');
            $validated['logo'] = $logoPath;
        }

        $targetUniversity->update($validated);

        AuditLog::log('updated_university_settings', 'University', $targetUniversity->id);

        return back()->with('success', 'Institution ('.$targetUniversity->name.') profile & branding settings updated successfully!');
    }

    public function downloadSampleCsv()
    {
        $spreadsheet = new Spreadsheet();

        // Main Sheet
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Enrollment Template');

        // Hidden Options Sheet for Excel Data Validation Dropdowns
        $lookupSheet = $spreadsheet->createSheet();
        $lookupSheet->setTitle('Options');

        $typeOptions = [
            ['Institute of National Importance (IIT / NIT / IIM / AIIMS)'],
            ['Central / State University'],
            ['Autonomous College (Independent Academic Autonomy)'],
            ['Affiliated College (Works Under Parent University)'],
            ['Polytechnic & ITI (Technical / Skill Institute)'],
        ];
        $lookupSheet->fromArray($typeOptions, null, 'A1');
        $lookupSheet->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);

        $headers = [
            'Name *',
            'Short Name *',
            'Type *',
            'Email',
            'Phone',
            'Website',
            'Address',
            'State',
            'Country',
            'Affiliated Under (Short Name)'
        ];

        $sheet->fromArray([$headers], null, 'A1');

        $headerRange = 'A1:J1';
        $sheet->getStyle($headerRange)->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));
        $sheet->getStyle($headerRange)->getFill()->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)->getStartColor()->setARGB('4F46E5');
        $sheet->freezePane('A2');

        $sampleData = [
            [
                'Indian Institute of Technology Bhubaneswar',
                'IIT BBS',
                'Institute of National Importance (IIT / NIT / IIM / AIIMS)',
                'contact@iitbbs.ac.in',
                '06742576000',
                'https://www.iitbbs.ac.in',
                'Argul, Jatni',
                'Odisha',
                'India',
                ''
            ],
            [
                'Utkal University',
                'UU',
                'Central / State University',
                'info@utkaluniversity.ac.in',
                '06742567382',
                'https://utkaluniversity.ac.in',
                'Vani Vihar, Bhubaneswar',
                'Odisha',
                'India',
                ''
            ],
            [
                'Bhubaneswar Institute of Technology',
                'BIT',
                'Affiliated College (Works Under Parent University)',
                'info@bit.edu.in',
                '06742500111',
                'https://www.bit.edu.in',
                'Infocity, Bhubaneswar',
                'Odisha',
                'India',
                'UU'
            ]
        ];

        $sheet->fromArray($sampleData, null, 'A2');

        $spreadsheet->addNamedRange(new NamedRange('ClassificationList', $lookupSheet, '$A$1:$A$5'));

        // Create Dropdown Data Validation for Type (Column C) referencing NamedRange 'ClassificationList'
        $validation = $sheet->getCell('C2')->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank(false);
        $validation->setShowInputMessage(true);
        $validation->setShowErrorMessage(true);
        $validation->setShowDropDown(true);
        $validation->setErrorTitle('Invalid Classification Type');
        $validation->setError('You must select a valid Institution Classification Type from the dropdown list.');
        $validation->setPromptTitle('Select Classification Type');
        $validation->setPrompt('Click the dropdown arrow to select the Institution Classification Type.');
        $validation->setFormula1('ClassificationList');

        // Apply dropdown validation to Column C rows 2 to 1000
        for ($i = 2; $i <= 1000; $i++) {
            $sheet->getCell("C{$i}")->setDataValidation(clone $validation);
        }

        foreach (range('A', 'J') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $spreadsheet->setActiveSheetIndex(0);

        $writer = new Xlsx($spreadsheet);
        $fileName = 'institutions_bulk_enrollment_template.xlsx';

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $fileName, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function import(Request $request)
    {
        $user = auth()->user();
        if (!$user || !$user->isSuperAdmin()) {
            abort(403, 'Unauthorized. Bulk Institution Import is reserved for Super Administrator role only.');
        }

        $request->validate([
            'import_file' => 'required|file|mimes:xlsx,xls,csv,txt|max:10240',
        ]);

        $file = $request->file('import_file');
        $filePath = $file->getRealPath();

        try {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);
        } catch (\Exception $e) {
            return back()->with('error', 'Unable to parse file: ' . $e->getMessage());
        }

        if (empty($rows) || count($rows) < 2) {
            return back()->with('error', 'The uploaded file contains no data rows.');
        }

        $headerRow = array_shift($rows);
        $cleanHeader = [];
        foreach ($headerRow as $colLetter => $headerName) {
            $rawHeader = (string)$headerName;
            // Remove asterisk and special chars except spaces/underscores, convert to lowercase underscore
            $cleaned = preg_replace('/[^a-zA-Z0-9\s_]/', '', $rawHeader);
            $cleaned = strtolower(trim(preg_replace('/\s+/', '_', $cleaned)));
            $cleaned = trim($cleaned, '_');
            $cleanHeader[$colLetter] = $cleaned;
        }

        $imported = 0;
        $updated = 0;
        $skipped = 0;
        $rowErrors = [];

        foreach ($rows as $rowNum => $row) {
            $data = [];
            foreach ($cleanHeader as $colLetter => $key) {
                if (!empty($key)) {
                    $data[$key] = isset($row[$colLetter]) ? trim((string)$row[$colLetter]) : '';
                }
            }

            // Skip completely empty rows
            if (empty(array_filter($data))) {
                continue;
            }

            $name = $data['name'] ?? $data['name_'] ?? $data['institution_name'] ?? $data['university_name'] ?? '';
            $shortName = $data['short_name'] ?? $data['short_name_'] ?? $data['code'] ?? $data['abbr'] ?? '';

            $missingFields = [];
            if (empty($name)) {
                $missingFields[] = 'Name';
            }
            if (empty($shortName)) {
                $missingFields[] = 'Short Name';
            }

            if (!empty($missingFields)) {
                $skipped++;
                $rowErrors[] = "Row {$rowNum}: Missing required field(s) [" . implode(', ', $missingFields) . "].";
                continue;
            }

            $rawType = trim($data['type'] ?? $data['type_'] ?? $data['classification'] ?? $data['institution_classification_type'] ?? '');
            $typeLower = strtolower($rawType);

            if (str_contains($typeLower, 'national importance') || str_contains($typeLower, 'ini') || str_contains($typeLower, 'iit')) {
                $type = 'ini';
            } elseif (str_contains($typeLower, 'autonomous')) {
                $type = 'autonomous_college';
            } elseif (str_contains($typeLower, 'affiliated')) {
                $type = 'affiliated_college';
            } elseif (str_contains($typeLower, 'polytechnic') || str_contains($typeLower, 'iti')) {
                $type = 'polytechnic_iti';
            } else {
                $type = 'university';
            }

            $parentShortName = $data['affiliated_under_short_name'] ?? $data['affiliated_under'] ?? $data['parent_short_name'] ?? $data['parent_university'] ?? '';
            $parentId = null;
            if (!empty($parentShortName)) {
                $parentUni = University::where('short_name', $parentShortName)
                    ->orWhere('name', 'LIKE', '%' . $parentShortName . '%')
                    ->first();
                if ($parentUni) {
                    $parentId = $parentUni->id;
                }
            }

            $existing = University::where('short_name', $shortName)
                ->orWhere('name', $name)
                ->first();

            $institutionData = [
                'name' => $name,
                'short_name' => $shortName,
                'type' => $type,
                'parent_id' => ($type === 'affiliated_college') ? $parentId : null,
                'email' => !empty($data['email']) ? $data['email'] : null,
                'phone' => !empty($data['phone']) ? $data['phone'] : null,
                'website' => !empty($data['website']) ? $data['website'] : null,
                'address' => !empty($data['address']) ? $data['address'] : null,
                'state' => !empty($data['state']) ? $data['state'] : null,
                'country' => !empty($data['country']) ? $data['country'] : 'India',
                'is_active' => true,
            ];

            try {
                if ($existing) {
                    $existing->update($institutionData);
                    $updated++;
                } else {
                    $newUni = University::create($institutionData);
                    AuditLog::log('enrolled_institution_bulk', 'University', $newUni->id);
                    $imported++;
                }
            } catch (\Exception $e) {
                $skipped++;
                $rowErrors[] = "Row {$rowNum} ('{$name}'): Database error - " . $e->getMessage();
            }
        }

        $msg = "Bulk Institution Import Summary: Newly Enrolled: {$imported}, Updated: {$updated}.";
        if ($skipped > 0) {
            $msg .= " Skipped invalid rows: {$skipped}.";
        }

        $response = back()->with('success', $msg);
        if (!empty($rowErrors)) {
            $response->with('import_errors', $rowErrors);
        }

        return $response;
    }
}
