@extends('layouts.admin')

@section('title', 'Longitudinal Research & Trends')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Longitudinal Research & Campaign Trends</h4>
        <p class="text-secondary small mb-0">Multi-year comparison across 2026, 2027, 2028 research campaigns.</p>
    </div>
</div>

<div class="card-custom p-4 mb-4">
    <h6 class="fw-bold text-dark mb-3">Graduate Readiness Index (GRI) Multi-Year Progression</h6>
    <div style="height: 320px;">
        <canvas id="trendsChart"></canvas>
    </div>
</div>
@endsection

@push('scripts')
<script>
    new Chart(document.getElementById('trendsChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: ['2024 Campaign', '2025 Campaign', '2026 Campaign (Current)'],
            datasets: [{
                label: 'Graduate Readiness Index (GRI)',
                data: [62.4, 67.1, 72.4],
                borderColor: '#1e40af',
                backgroundColor: 'rgba(30, 64, 175, 0.1)',
                fill: true,
                tension: 0.3
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
</script>
@endpush
