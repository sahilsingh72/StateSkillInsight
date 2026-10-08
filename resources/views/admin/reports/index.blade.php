@extends('layouts.admin')

@section('title', 'Institutional Research Reports Hub')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
<style>
    .ts-wrapper.form-select-sm, .form-select-sm.ts-wrapper {
        border-radius: 0.375rem !important;
        font-size: 0.85rem !important;
        min-width: 280px !important;
    }
    .ts-dropdown {
        border-radius: 0.5rem !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1) !important;
        z-index: 1050 !important;
    }
</style>
@endpush

@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Institutional Research Reports Hub</h4>
        <p class="text-secondary small mb-0">Generate executive summaries, category findings, psychometric breakdowns, and curriculum recommendation reports.</p>
    </div>

    @if(isset($universities) && $universities->isNotEmpty())
        <form method="GET" action="{{ route('admin.reports.index') }}" class="d-flex align-items-center gap-2">
            <label class="small text-muted fw-semibold text-nowrap"><i class="bi bi-funnel me-1"></i> Institution Scope:</label>
            <select name="university_id" id="university_scope_select" class="form-select form-select-sm" style="min-width: 280px;">
                <option value="">All Universities & Colleges (State-Wide Consolidated)</option>
                @foreach($universities as $uni)
                    <option value="{{ $uni->id }}" {{ ($selectedUniversityId == $uni->id) ? 'selected' : '' }}>
                        {{ $uni->name }}
                    </option>
                @endforeach
            </select>
        </form>
    @endif
</div>

<div class="row g-4">
    <!-- Executive Summary Report -->
    <div class="col-md-4">
        <div class="card-custom p-4 text-center h-100 d-flex flex-column justify-content-between">
            <div>
                <i class="bi bi-file-earmark-pdf fs-1 text-danger mb-2"></i>
                <h5 class="fw-bold text-dark">Executive Summary Report</h5>
                <p class="small text-secondary mb-3">Comprehensive university-wide research synthesis across all 4 categories and state composite readiness.</p>
            </div>
            <a href="{{ route('admin.reports.view', array_filter(['type' => 'executive', 'university_id' => $selectedUniversityId])) }}" class="btn btn-uni-primary btn-sm w-100" target="_blank">
                <i class="bi bi-box-arrow-up-right me-1"></i> Generate Executive Report
            </a>
        </div>
    </div>

    <!-- Category 1: Working Alumni -->
    <div class="col-md-4">
        <div class="card-custom p-4 text-center h-100 d-flex flex-column justify-content-between">
            <div>
                <i class="bi bi-briefcase fs-1 text-primary mb-2"></i>
                <h5 class="fw-bold text-dark">Category 1: Working Alumni Report</h5>
                <p class="small text-secondary mb-3">Professional practice evidence, workplace skill retention, leadership adaptability, and academic relevance synthesis.</p>
            </div>
            <a href="{{ route('admin.reports.view', array_filter(['type' => 'category1', 'university_id' => $selectedUniversityId])) }}" class="btn btn-outline-primary btn-sm w-100" target="_blank">
                <i class="bi bi-box-arrow-up-right me-1"></i> Generate Report
            </a>
        </div>
    </div>

    <!-- Category 2: Job-Seeking Alumni -->
    <div class="col-md-4">
        <div class="card-custom p-4 text-center h-100 d-flex flex-column justify-content-between">
            <div>
                <i class="bi bi-person-vcard fs-1 text-teal mb-2" style="color:var(--uni-secondary);"></i>
                <h5 class="fw-bold text-dark">Category 2: Employability Gap Report</h5>
                <p class="small text-secondary mb-3">Job search barriers, technical assessment friction, interview conversion, and self-directed upskilling findings.</p>
            </div>
            <a href="{{ route('admin.reports.view', array_filter(['type' => 'category2', 'university_id' => $selectedUniversityId])) }}" class="btn btn-outline-secondary btn-sm w-100" target="_blank">
                <i class="bi bi-box-arrow-up-right me-1"></i> Generate Report
            </a>
        </div>
    </div>

    <!-- Category 3: Current Students -->
    <div class="col-md-4">
        <div class="card-custom p-4 text-center h-100 d-flex flex-column justify-content-between">
            <div>
                <i class="bi bi-mortarboard fs-1 text-warning mb-2"></i>
                <h5 class="fw-bold text-dark">Category 3: Current Students Report</h5>
                <p class="small text-secondary mb-3">Undergraduate theoretical foundation, practical lab competence, career goal clarity, and Generative AI tool agility.</p>
            </div>
            <a href="{{ route('admin.reports.view', array_filter(['type' => 'category3', 'university_id' => $selectedUniversityId])) }}" class="btn btn-outline-warning text-dark btn-sm w-100" target="_blank">
                <i class="bi bi-box-arrow-up-right me-1"></i> Generate Report
            </a>
        </div>
    </div>

    <!-- Category 4: Interrupted Learners -->
    <div class="col-md-4">
        <div class="card-custom p-4 text-center h-100 d-flex flex-column justify-content-between">
            <div>
                <i class="bi bi-arrow-repeat fs-1 text-danger mb-2"></i>
                <h5 class="fw-bold text-dark">Category 4: Interrupted Learners Report</h5>
                <p class="small text-secondary mb-3">Academic interruption factors, retained foundational knowledge, work-based learning, and degree re-entry readiness.</p>
            </div>
            <a href="{{ route('admin.reports.view', array_filter(['type' => 'category4', 'university_id' => $selectedUniversityId])) }}" class="btn btn-outline-danger btn-sm w-100" target="_blank">
                <i class="bi bi-box-arrow-up-right me-1"></i> Generate Report
            </a>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const uniSelect = document.getElementById('university_scope_select');
        if (uniSelect) {
            new TomSelect(uniSelect, {
                create: false,
                maxItems: 1,
                placeholder: 'Search institute or college...',
                allowEmptyOption: true,
                onChange: function () {
                    this.input.form.submit();
                }
            });
        }
    });
</script>
@endpush


