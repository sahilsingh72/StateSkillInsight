@extends('layouts.admin')

@section('title', 'Institutional Research Reports Hub')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Institutional Research Reports Hub</h4>
        <p class="text-secondary small mb-0">Generate executive summaries, category findings, and curriculum recommendation reports.</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card-custom p-4 text-center h-100">
            <i class="bi bi-file-earmark-pdf fs-1 text-danger mb-2"></i>
            <h5 class="fw-bold text-dark">Executive Summary Report</h5>
            <p class="small text-secondary mb-3">Comprehensive university-wide research synthesis across all 4 categories.</p>
            <a href="{{ route('admin.reports.view', 'executive') }}" class="btn btn-uni-primary btn-sm w-100" target="_blank">Generate Report</a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-custom p-4 text-center h-100">
            <i class="bi bi-briefcase fs-1 text-primary mb-2"></i>
            <h5 class="fw-bold text-dark">Category 1: Working Alumni Report</h5>
            <p class="small text-secondary mb-3">Professional practice evidence and academic relevance synthesis.</p>
            <a href="{{ route('admin.reports.view', 'category1') }}" class="btn btn-outline-primary btn-sm w-100" target="_blank">Generate Report</a>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card-custom p-4 text-center h-100">
            <i class="bi bi-person-search fs-1 text-teal mb-2" style="color:var(--uni-secondary);"></i>
            <h5 class="fw-bold text-dark">Category 2: Employability Gap Report</h5>
            <p class="small text-secondary mb-3">Job search barriers, interview conversion, and skill gap findings.</p>
            <a href="{{ route('admin.reports.view', 'category2') }}" class="btn btn-outline-secondary btn-sm w-100" target="_blank">Generate Report</a>
        </div>
    </div>
</div>
@endsection
