@extends('layouts.admin')

@section('title', 'Institutional Analytics Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Education–Employment Research Dashboard</h4>
        <p class="text-secondary small mb-0">Real-time empirical intelligence across all 4 respondent categories.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('admin.reports.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-file-earmark-pdf me-1"></i> Research Reports
        </a>
        <a href="{{ route('admin.exports.csv') }}" class="btn btn-primary-custom btn-sm">
            <i class="bi bi-download me-1"></i> Export Raw CSV
        </a>
    </div>
</div>

<!-- 4 Core Metric Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card-custom p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary small fw-semibold">Total Respondents</span>
                <span class="badge bg-primary-subtle text-primary"><i class="bi bi-people"></i></span>
            </div>
            <h3 class="fw-bold text-dark mb-0">{{ number_format($metrics['total_respondents']) }}</h3>
            <small class="text-success"><i class="bi bi-arrow-up-right me-1"></i> {{ $metrics['completion_rate'] }}% Completion Rate</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-custom p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary small fw-semibold">Graduate Readiness (GRI)</span>
                <span class="badge bg-success-subtle text-success"><i class="bi bi-award"></i></span>
            </div>
            <h3 class="fw-bold text-dark mb-0">{{ $metrics['avg_readiness'] }} <small class="fs-6 text-muted">/ 100</small></h3>
            <small class="text-secondary">Average Composite Score</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-custom p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary small fw-semibold">AI & Digital Readiness</span>
                <span class="badge bg-info-subtle text-info"><i class="bi bi-cpu"></i></span>
            </div>
            <h3 class="fw-bold text-dark mb-0">{{ $metrics['avg_ai_readiness'] }} <small class="fs-6 text-muted">/ 100</small></h3>
            <small class="text-primary">Emerging Tool Adoption</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-custom p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="text-secondary small fw-semibold">Today's Submissions</span>
                <span class="badge bg-warning-subtle text-warning"><i class="bi bi-lightning-charge"></i></span>
            </div>
            <h3 class="fw-bold text-dark mb-0">{{ $metrics['today_submissions'] }}</h3>
            <small class="text-muted">{{ $metrics['week_submissions'] }} this week</small>
        </div>
    </div>
</div>

<!-- 4 Category Breakdown Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <a href="{{ route('admin.analytics.category1') }}" class="card-custom p-3 d-block text-decoration-none text-dark hover-shadow border-start border-primary border-4">
            <div class="fw-bold"><i class="bi bi-briefcase text-primary me-2"></i> Working Alumni</div>
            <div class="fs-4 fw-bold mt-2">{{ number_format($metrics['cat1_count']) }}</div>
            <small class="text-muted">Professional Practice Evidence</small>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ route('admin.analytics.category2') }}" class="card-custom p-3 d-block text-decoration-none text-dark hover-shadow border-start border-teal border-4" style="border-color:var(--uni-secondary)!important;">
            <div class="fw-bold" style="color:var(--uni-secondary);"><i class="bi bi-person-vcard me-2"></i> Job-Seeking Alumni</div>
            <div class="fs-4 fw-bold mt-2">{{ number_format($metrics['cat2_count']) }}</div>
            <small class="text-muted">Recruitment Bottlenecks</small>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ route('admin.analytics.category3') }}" class="card-custom p-3 d-block text-decoration-none text-dark hover-shadow border-start border-warning border-4">
            <div class="fw-bold text-warning"><i class="bi bi-mortarboard me-2"></i> Current Students</div>
            <div class="fs-4 fw-bold mt-2">{{ number_format($metrics['cat3_count']) }}</div>
            <small class="text-muted">Pre-Graduation Map</small>
        </a>
    </div>
    <div class="col-md-3">
        <a href="{{ route('admin.analytics.category4') }}" class="card-custom p-3 d-block text-decoration-none text-dark hover-shadow border-start border-danger border-4">
            <div class="fw-bold text-danger"><i class="bi bi-arrow-counterclockwise me-2"></i> Interrupted Students</div>
            <div class="fs-4 fw-bold mt-2">{{ number_format($metrics['cat4_count']) }}</div>
            <small class="text-muted">Skill & Re-entry Pathways</small>
        </a>
    </div>
</div>

<!-- Charts Row -->
<div class="row g-4 mb-4">
    <div class="col-md-5">
        <div class="card-custom p-4 h-100">
            <h6 class="fw-bold text-dark mb-3">Respondents by Category</h6>
            <div style="height: 250px;">
                <canvas id="categoryDonutChart"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card-custom p-4 h-100">
            <h6 class="fw-bold text-dark mb-3">Graduate Readiness Index Dimensions</h6>
            <div style="height: 250px;">
                <canvas id="readinessBarChart"></canvas>
            </div>
        </div>
    </div>
</div>

<!-- Recent Respondents Table -->
<div class="card-custom p-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h6 class="fw-bold text-dark mb-0">Recent Survey Submissions</h6>
        <a href="{{ route('admin.respondents.index') }}" class="btn btn-sm btn-outline-secondary">View All</a>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Respondent</th>
                    <th>Category</th>
                    <th>Programme</th>
                    <th>Graduation Year</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($recentRespondents as $r)
                    <tr>
                        <td>
                            <div class="fw-bold text-dark">{{ $r->name }}</div>
                            <small class="text-muted">{{ $r->email ?? $r->mobile }}</small>
                        </td>
                        <td>
                            <span class="badge bg-light text-dark border">{{ $r->category_code }}</span>
                        </td>
                        <td>{{ $r->programme }}</td>
                        <td>{{ $r->graduation_year }}</td>
                        <td><span class="badge bg-success">Submitted</span></td>
                        <td>
                            <a href="{{ route('admin.respondents.show', $r->id) }}" class="btn btn-sm btn-light border"><i class="bi bi-eye me-1"></i> View</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Donut Chart
    const ctxDonut = document.getElementById('categoryDonutChart').getContext('2d');
    new Chart(ctxDonut, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($chartData['categoryDonut']['labels']) !!},
            datasets: [{
                data: {!! json_encode($chartData['categoryDonut']['data']) !!},
                backgroundColor: {!! json_encode($chartData['categoryDonut']['backgroundColor']) !!}
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });

    // Readiness Bar Chart
    const ctxBar = document.getElementById('readinessBarChart').getContext('2d');
    new Chart(ctxBar, {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartData['readinessBar']['labels']) !!},
            datasets: [{
                label: 'Average Score (/100)',
                data: {!! json_encode($chartData['readinessBar']['data']) !!},
                backgroundColor: 'rgba(30, 64, 175, 0.85)',
                borderRadius: 6
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: { y: { beginAtZero: true, max: 100 } }
        }
    });
</script>
@endpush
