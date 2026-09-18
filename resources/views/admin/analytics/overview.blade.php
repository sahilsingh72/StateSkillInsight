@extends('layouts.admin')

@section('title', 'Analytics Overview')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Analytics Overview</h4>
        <p class="text-secondary small mb-0">High-level research metrics and distribution breakdown.</p>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card-custom p-4">
            <h6 class="fw-bold text-dark mb-3">Respondents by Category</h6>
            <div style="height: 300px;">
                <canvas id="overviewDonut"></canvas>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card-custom p-4">
            <h6 class="fw-bold text-dark mb-3">Graduate Readiness Index Dimensions</h6>
            <div style="height: 300px;">
                <canvas id="overviewBar"></canvas>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    new Chart(document.getElementById('overviewDonut').getContext('2d'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($chartData['categoryDonut']['labels']) !!},
            datasets: [{
                data: {!! json_encode($chartData['categoryDonut']['data']) !!},
                backgroundColor: {!! json_encode($chartData['categoryDonut']['backgroundColor']) !!}
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });

    new Chart(document.getElementById('overviewBar').getContext('2d'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($chartData['readinessBar']['labels']) !!},
            datasets: [{
                label: 'Average Score',
                data: {!! json_encode($chartData['readinessBar']['data']) !!},
                backgroundColor: '#1e40af'
            }]
        },
        options: { responsive: true, maintainAspectRatio: false }
    });
</script>
@endpush
