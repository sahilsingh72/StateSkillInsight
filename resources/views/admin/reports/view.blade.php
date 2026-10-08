<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $reportConfig['title'] }} - {{ $university->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --lead-color: {{ $reportConfig['lead_color'] ?? '#1e40af' }};
        }
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f8fafc;
            color: #0f172a;
            padding: 30px 20px;
        }
        .report-container {
            max-width: 1040px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(15, 23, 42, 0.08);
            padding: 45px 50px;
            border: 1px solid #e2e8f0;
        }
        .report-header {
            border-bottom: 2px solid #e2e8f0;
            padding-bottom: 24px;
            margin-bottom: 28px;
        }
        .uni-emblem {
            width: 58px;
            height: 58px;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--lead-color), #3b82f6);
            color: #fff;
            font-weight: 800;
            font-size: 1.4rem;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(30, 64, 175, 0.25);
        }
        .metric-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 18px 20px;
            transition: all 0.2s ease;
            position: relative;
            overflow: hidden;
        }
        .metric-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: var(--lead-color);
        }
        .score-val {
            font-size: 2.1rem;
            font-weight: 800;
            letter-spacing: -0.03em;
            line-height: 1.1;
        }
        .findings-callout {
            background: #f8fafc;
            border-left: 4px solid var(--lead-color);
            border-radius: 0 12px 12px 0;
            padding: 20px 24px;
        }
        .progress {
            height: 8px;
            border-radius: 999px;
            background-color: #f1f5f9;
        }
        .recommendation-card {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 16px 20px;
            background: #fff;
            margin-bottom: 12px;
        }
        .nav-report-btn {
            font-size: 0.82rem;
            font-weight: 600;
            border-radius: 8px;
            padding: 6px 14px;
        }
        @media print {
            body {
                background: #fff;
                padding: 0;
                color: #000;
            }
            .report-container {
                box-shadow: none;
                border: none;
                padding: 0;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
            .metric-card {
                break-inside: avoid;
            }
            .recommendation-card {
                break-inside: avoid;
            }
        }
    </style>
</head>
<body>

    <div class="report-container">
        <!-- Top Toolbar (Hidden on Print) -->
        <div class="no-print mb-4 d-flex flex-wrap justify-content-between align-items-center gap-2 pb-3 border-bottom">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="{{ route('admin.reports.index', array_filter(['university_id' => $uniId])) }}" class="btn btn-outline-secondary btn-sm nav-report-btn">
                    <i class="bi bi-arrow-left me-1"></i> Reports Hub
                </a>

                @if(isset($universities) && $universities->isNotEmpty())
                    <div class="dropdown">
                        <button class="btn btn-light border btn-sm dropdown-toggle nav-report-btn d-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                            <i class="bi bi-building text-primary"></i> 
                            <span class="text-truncate" style="max-width: 220px;">{{ $uniId ? $university->name : 'All Universities (State-Wide)' }}</span>
                        </button>
                        <div class="dropdown-menu shadow-sm p-2" style="width: 320px;">
                            <div class="mb-2 px-1">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light border-end-0"><i class="bi bi-search text-muted"></i></span>
                                    <input type="text" id="institutionSearchInput" class="form-control border-start-0 ps-0" placeholder="Search institute or college..." autocomplete="off">
                                </div>
                            </div>
                            <div id="institutionList" style="max-height: 270px; overflow-y: auto;">
                                <a class="dropdown-item rounded py-1.5 mb-1 {{ !$uniId ? 'active fw-bold' : '' }} institution-item" href="{{ route('admin.reports.view', ['type' => $type]) }}">
                                    <i class="bi bi-globe me-1"></i> All Universities & Colleges (State-Wide)
                                </a>
                                <div class="dropdown-divider my-1"></div>
                                @foreach($universities as $u)
                                    <a class="dropdown-item rounded py-1.5 mb-1 {{ ($uniId == $u->id) ? 'active fw-bold' : '' }} institution-item" 
                                       href="{{ route('admin.reports.view', ['type' => $type, 'university_id' => $u->id]) }}"
                                       data-name="{{ strtolower($u->name) }} {{ strtolower($u->code ?? '') }}">
                                        <div class="text-truncate fw-semibold" style="font-size: 0.85rem;">{{ $u->name }}</div>
                                        @if($u->code)
                                            <small class="text-muted d-block" style="font-size:0.72rem;">Code: {{ $u->code }}</small>
                                        @endif
                                    </a>
                                @endforeach
                                <div id="noInstitutionFound" class="text-center text-muted small py-3 d-none">
                                    <i class="bi bi-search d-block mb-1 text-secondary fs-6"></i> No matching institute found
                                </div>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="dropdown">
                    <button class="btn btn-light border btn-sm dropdown-toggle nav-report-btn" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-file-earmark-text me-1 text-secondary"></i> Switch Report Type
                    </button>
                    <ul class="dropdown-menu shadow-sm">
                        <li><a class="dropdown-item {{ $type === 'executive' ? 'active' : '' }}" href="{{ route('admin.reports.view', array_filter(['type' => 'executive', 'university_id' => $uniId])) }}">Executive Synthesis</a></li>
                        <li><a class="dropdown-item {{ in_array($type, ['category1', 'cat_1', 'cat1']) ? 'active' : '' }}" href="{{ route('admin.reports.view', array_filter(['type' => 'category1', 'university_id' => $uniId])) }}">Category 1: Working Alumni</a></li>
                        <li><a class="dropdown-item {{ in_array($type, ['category2', 'cat_2', 'cat2']) ? 'active' : '' }}" href="{{ route('admin.reports.view', array_filter(['type' => 'category2', 'university_id' => $uniId])) }}">Category 2: Job-Seeking Alumni</a></li>
                        <li><a class="dropdown-item {{ in_array($type, ['category3', 'cat_3', 'cat3']) ? 'active' : '' }}" href="{{ route('admin.reports.view', array_filter(['type' => 'category3', 'university_id' => $uniId])) }}">Category 3: Current Students</a></li>
                        <li><a class="dropdown-item {{ in_array($type, ['category4', 'cat_4', 'cat4']) ? 'active' : '' }}" href="{{ route('admin.reports.view', array_filter(['type' => 'category4', 'university_id' => $uniId])) }}">Category 4: Interrupted Students</a></li>
                    </ul>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                <span class="badge {{ $uniId ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-success-subtle text-success border border-success-subtle' }} px-3 py-2">
                    <i class="bi {{ $uniId ? 'bi-building' : 'bi-globe' }} me-1"></i> {{ $university->name }}
                </span>
                <button onclick="window.print()" class="btn btn-primary btn-sm nav-report-btn px-3">
                    <i class="bi bi-printer me-1"></i> Print / Save PDF
                </button>
            </div>
        </div>

        <!-- Institutional Header / Letterhead -->
        <div class="report-header">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="uni-emblem" style="{{ !$uniId ? 'background: linear-gradient(135deg, #0d9488, #2563eb);' : '' }}">
                        @if($uniId)
                            {{ strtoupper(substr($university->code ?? $university->name, 0, 2)) }}
                        @else
                            <i class="bi bi-globe fs-4"></i>
                        @endif
                    </div>
                    <div>
                        <h3 class="fw-bold text-dark mb-0">{{ $university->name }}</h3>
                        <p class="text-secondary small mb-0">State Higher Education Department &bull; Skill & Employability Intelligence Portal</p>
                    </div>
                </div>
                <div class="text-end text-muted small">
                    <div class="fw-bold text-dark">{{ date('d F Y') }}</div>
                    <div class="text-uppercase font-monospace text-secondary" style="font-size: 0.75rem;">REP-{{ date('Y') }}-{{ $reportConfig['code_suffix'] }}</div>
                </div>
            </div>

            <div class="mt-2 pt-2">
                <h4 class="fw-bold text-dark mb-1" style="color: var(--lead-color) !important;">{{ $reportConfig['title'] }}</h4>
                <p class="text-secondary small mb-2">{{ $reportConfig['subtitle'] }}</p>
                <div class="d-flex flex-wrap gap-2 pt-1">
                    <span class="badge bg-light text-dark border"><i class="bi bi-people me-1 text-primary"></i> Target Scope: {{ $reportConfig['category_name'] }}</span>
                    <span class="badge bg-light text-dark border"><i class="bi bi-shield-check me-1 text-success"></i> Official Senate Intelligence</span>
                    <span class="badge bg-light text-dark border"><i class="bi bi-calendar3 me-1 text-secondary"></i> Academic Year {{ date('Y') }}</span>
                    @if(!$uniId)
                        <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="bi bi-diagram-3 me-1"></i> State-Wide Consolidated</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Cohort Sample & Coverage Banner -->
        <div class="row g-3 mb-4">
            <div class="col-sm-4">
                <div class="p-3 bg-light rounded-3 border text-center">
                    <div class="text-muted small fw-semibold text-uppercase">Cohort Sample Registered</div>
                    <div class="fs-4 fw-bold text-dark">{{ number_format($totalCohort) }}</div>
                    <div class="small text-muted" style="font-size:0.75rem;">Unique Profiles in Scope</div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="p-3 bg-light rounded-3 border text-center">
                    <div class="text-muted small fw-semibold text-uppercase">Validated Submissions</div>
                    <div class="fs-4 fw-bold text-primary">{{ number_format($completedCohort) }}</div>
                    <div class="small text-muted" style="font-size:0.75rem;">Full Response Completed</div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="p-3 bg-light rounded-3 border text-center">
                    <div class="text-muted small fw-semibold text-uppercase">Completion Reliability</div>
                    <div class="fs-4 fw-bold {{ $completionRate >= 50 ? 'text-success' : 'text-warning-emphasis' }}">{{ $completionRate }}%</div>
                    <div class="small text-muted" style="font-size:0.75rem;">
                        {{ $completionRate >= 70 ? 'High Statistical Confidence' : ($completionRate > 0 ? 'Active Intake Period' : 'Awaiting Responses') }}
                    </div>
                </div>
            </div>
        </div>

        <!-- Executive Findings Summary Callout -->
        <div class="findings-callout mb-4">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i class="bi bi-lightbulb-fill text-warning fs-5"></i>
                <h6 class="fw-bold text-dark mb-0">Executive Findings & Analytical Synthesis</h6>
            </div>
            <p class="text-secondary small mb-0 lh-base">
                {{ $summaryFindings }}
            </p>
        </div>

        <!-- Key Metrics Cards Grid -->
        <h6 class="fw-bold text-dark mb-3"><i class="bi bi-speedometer2 me-1 text-primary"></i> Primary Competence & Readiness Indices</h6>
        <div class="row g-3 mb-4">
            @foreach($keyCards as $card)
                <div class="col-md-3 col-sm-6">
                    <div class="metric-card h-100">
                        <div class="text-muted small fw-semibold mb-1 text-truncate" title="{{ $card['title'] }}">{{ $card['title'] }}</div>
                        <div class="score-val {{ $card['score'] !== null ? 'text-primary' : 'text-muted fs-4' }}">
                            @if($card['score'] !== null)
                                {{ $card['score'] }} <span class="fs-6 text-muted fw-normal">/ 100</span>
                            @else
                                <span class="badge bg-light text-secondary border fs-6 fw-normal">Awaiting Data</span>
                            @endif
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                            <span class="small text-muted" style="font-size:0.75rem;">{{ $card['benchmark'] }}</span>
                            <span class="small fw-semibold {{ $card['status_class'] ?? 'text-secondary' }}" style="font-size:0.75rem;">
                                {{ $card['status'] }}
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Psychometric Dimension Breakdown Table -->
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold text-dark mb-0">
                    <i class="bi bi-bar-chart-steps me-1 text-primary"></i> Psychometric Dimension Score Breakdown
                </h6>
                <span class="text-muted small">{{ count($dimensions) }} Dimensions Evaluated</span>
            </div>

            <div class="table-responsive border rounded-3">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr class="small text-muted">
                            <th style="width: 35%;">Dimension & Metric</th>
                            <th style="width: 30%;">Score Performance Bar</th>
                            <th style="width: 15%;" class="text-center">Score</th>
                            <th style="width: 20%;" class="text-end">Evaluation Band</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($dimensions as $dim)
                            <tr>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $dim['name'] }}</div>
                                    <div class="text-muted font-monospace" style="font-size:0.72rem;">{{ $dim['code'] }}</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1">
                                            <div class="progress-bar {{ $dim['bar_class'] }}" 
                                                 role="progressbar" 
                                                 style="width: {{ $dim['score'] ?? 0 }}%;" 
                                                 aria-valuenow="{{ $dim['score'] ?? 0 }}" 
                                                 aria-valuemin="0" 
                                                 aria-valuemax="100">
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    @if($dim['score'] !== null)
                                        <span class="fw-bold {{ $dim['text_class'] }}">{{ number_format($dim['score'], 1) }}</span>
                                        <span class="text-muted small">/100</span>
                                    @else
                                        <span class="text-muted small">&mdash;</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <span class="{{ $dim['band_class'] }} small px-2 py-1">
                                        {{ $dim['band_label'] }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">
                                    <i class="bi bi-inbox fs-3 d-block mb-1"></i>
                                    No psychometric dimensions configured for this report scope.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Contributing Institutions Breakdown (State-Wide Report) -->
        @if(!$uniId && isset($universityBreakdown) && $universityBreakdown->isNotEmpty())
            <div class="mb-4">
                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-building me-1 text-primary"></i> Top Contributing Universities & Institutions</h6>
                <div class="row g-2">
                    @foreach($universityBreakdown as $ub)
                        <div class="col-md-6">
                            <div class="p-2 px-3 border rounded-3 bg-light d-flex justify-content-between align-items-center">
                                <span class="small fw-semibold text-dark text-truncate me-2" title="{{ $ub->university->name ?? 'Unknown' }}">
                                    <i class="bi bi-mortarboard text-secondary me-1"></i> {{ $ub->university->name ?? 'University #' . $ub->university_id }}
                                </span>
                                <span class="badge bg-primary-subtle text-primary small">{{ $ub->total }} responses</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Academic Discipline Breakdown (If Available) -->
        @if($programmeBreakdown->isNotEmpty())
            <div class="mb-4">
                <h6 class="fw-bold text-dark mb-3"><i class="bi bi-book me-1 text-primary"></i> Top Contributing Academic Disciplines</h6>
                <div class="row g-2">
                    @foreach($programmeBreakdown as $prog)
                        <div class="col-md-4 col-sm-6">
                            <div class="p-2 px-3 border rounded-3 bg-light d-flex justify-content-between align-items-center">
                                <span class="small fw-semibold text-dark text-truncate me-2" title="{{ $prog->programme }}">{{ $prog->programme }}</span>
                                <span class="badge bg-secondary-subtle text-secondary small">{{ $prog->total }} responses</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Strategic Senate & Academic Council Recommendations -->
        <div class="mb-4 pt-2">
            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-check2-circle me-1 text-primary"></i> Actionable Institutional Senate Recommendations</h6>
            @foreach($recommendations as $index => $rec)
                <div class="recommendation-card">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <div class="fw-bold text-dark small">
                            <span class="text-primary me-1">{{ $index + 1 }}.</span> {{ $rec['title'] }}
                        </div>
                        <span class="{{ $rec['badge_class'] }} small">{{ $rec['badge'] }}</span>
                    </div>
                    <p class="text-secondary small mb-0 lh-sm">
                        {{ $rec['description'] }}
                    </p>
                </div>
            @endforeach
        </div>

        <!-- Report Sign-off & Institutional Footer -->
        <div class="pt-4 border-top text-muted small d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <strong>Generated by:</strong> State Higher Education Employability Intelligence Engine
                <div class="text-secondary" style="font-size: 0.72rem;">Strictly for internal institutional decision-making and academic senate governance.</div>
            </div>
            <div class="text-end">
                <div class="fw-semibold text-dark">{{ $university->name }}</div>
                <div class="text-secondary" style="font-size: 0.72rem;">Status: Formal Assessment Record &bull; {{ date('Y') }}</div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('institutionSearchInput');
            if (searchInput) {
                searchInput.addEventListener('input', function () {
                    const q = this.value.toLowerCase().trim();
                    const items = document.querySelectorAll('.institution-item');
                    let visibleCount = 0;

                    items.forEach(function (item) {
                        const name = (item.getAttribute('data-name') || item.textContent).toLowerCase();
                        if (!q || name.includes(q)) {
                            item.classList.remove('d-none');
                            visibleCount++;
                        } else {
                            item.classList.add('d-none');
                        }
                    });

                    const noFound = document.getElementById('noInstitutionFound');
                    if (noFound) {
                        if (visibleCount === 0) {
                            noFound.classList.remove('d-none');
                        } else {
                            noFound.classList.add('d-none');
                        }
                    }
                });

                // Auto-focus search input when dropdown opens
                const instDropdown = searchInput.closest('.dropdown');
                if (instDropdown) {
                    instDropdown.addEventListener('shown.bs.dropdown', function () {
                        searchInput.focus();
                    });
                }
            }
        });
    </script>
</body>
</html>


