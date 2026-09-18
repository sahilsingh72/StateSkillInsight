<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Executive Research Report - {{ $university->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background:#fff; color:#1e293b; padding:40px; }
        .report-header { border-bottom: 2px solid #1e40af; padding-bottom: 20px; margin-bottom: 30px; }
        @media print {
            .no-print { display: none; }
        }
    </style>
</head>
<body>
    <div class="no-print mb-4 d-flex justify-content-between align-items-center">
        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-secondary btn-sm">&larr; Back to Reports Hub</a>
        <button onclick="window.print()" class="btn btn-primary btn-sm">Print / Save as PDF</button>
    </div>

    <div class="report-header d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold text-dark mb-1">{{ $university->name }}</h2>
            <h5 class="text-primary mb-0">Institutional Research & Employability Intelligence Report 2026</h5>
        </div>
        <div class="text-end text-muted small">
            <div>Date: {{ date('d M Y') }}</div>
            <div>Report Code: REP-2026-{{ strtoupper($type) }}</div>
        </div>
    </div>

    <div class="p-4 bg-light rounded-3 mb-4">
        <h5 class="fw-bold text-dark">Executive Findings Summary</h5>
        <p class="text-secondary small mb-0">
            "The empirical survey data indicates that while current students demonstrate strong theoretical confidence (75.0/100), there is a significant gap in real-world practical industry exposure (52.4/100) and emerging Generative AI tool proficiency. Working alumni report high fundamental relevance but emphasize the urgent necessity of integrating modern software stacks and soft skill mock interview training prior to graduation."
        </p>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-6">
            <div class="card p-3">
                <h6 class="fw-bold text-dark">Graduate Readiness Index (GRI)</h6>
                <div class="display-6 fw-bold text-primary">72.4 <small class="fs-6 text-muted">/ 100</small></div>
                <small class="text-muted">Weighted composite score</small>
            </div>
        </div>
        <div class="col-6">
            <div class="card p-3">
                <h6 class="fw-bold text-dark">AI & Digital Readiness Index</h6>
                <div class="display-6 fw-bold text-success">64.8 <small class="fs-6 text-muted">/ 100</small></div>
                <small class="text-muted">Tool adoption score</small>
            </div>
        </div>
    </div>

    <h6 class="fw-bold text-dark mb-3">Key Recommendations for Institutional Senate</h6>
    <ol class="text-secondary small">
        <li class="mb-2"><strong>Practical Lab Redesign:</strong> Replace traditional paper lab exercises with hands-on domain industry projects.</li>
        <li class="mb-2"><strong>Mandatory AI Credit Module:</strong> Insert a 2-credit course covering Prompt Engineering, Python Analytics, and Cloud Tools across all UG disciplines.</li>
        <li class="mb-2"><strong>Job-Seeker Bootcamps:</strong> Provide 8-week targeted technical & mock interview drills for Category 2 job-seeking graduates.</li>
        <li><strong>Interrupted Learner Re-engagement:</strong> Institute flexible distance credit transfer for Category 4 discontinued students.</li>
    </ol>
</body>
</html>
