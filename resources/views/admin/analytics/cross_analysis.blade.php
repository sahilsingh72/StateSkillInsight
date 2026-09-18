@extends('layouts.admin')

@section('title', 'Multi-Variable Cross Analysis')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Multi-Variable Cross-Analysis Filter Engine</h4>
        <p class="text-secondary small mb-0">Cross-filter responses by Category, Programme, Department, Year, and Employment status.</p>
    </div>
</div>

<!-- Filter Panel -->
<div class="card-custom p-4 mb-4">
    <form method="GET" action="{{ route('admin.analytics.cross_analysis') }}">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Category</label>
                <select name="category_code" class="form-select">
                    <option value="">All Categories</option>
                    <option value="cat_1" {{ ($filters['category_code'] ?? '') == 'cat_1' ? 'selected' : '' }}>Working Alumni</option>
                    <option value="cat_2" {{ ($filters['category_code'] ?? '') == 'cat_2' ? 'selected' : '' }}>Job-Seeking Alumni</option>
                    <option value="cat_3" {{ ($filters['category_code'] ?? '') == 'cat_3' ? 'selected' : '' }}>Current Students</option>
                    <option value="cat_4" {{ ($filters['category_code'] ?? '') == 'cat_4' ? 'selected' : '' }}>Interrupted Students</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Programme</label>
                <input type="text" name="programme" class="form-control" placeholder="e.g. Computer Science" value="{{ $filters['programme'] ?? '' }}">
            </div>
            <div class="col-md-3">
                <label class="form-label fw-semibold small">Department</label>
                <input type="text" name="department" class="form-control" placeholder="e.g. Dept of CS" value="{{ $filters['department'] ?? '' }}">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary-custom w-100"><i class="bi bi-funnel me-1"></i> Apply Cross Filter</button>
            </div>
        </div>
    </form>
</div>

<!-- Filtered Summary Cards -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card-custom p-3 border-start border-primary border-4">
            <div class="small text-secondary fw-semibold">Filtered Respondents</div>
            <h3 class="fw-bold text-dark mb-0">{{ number_format($analysisResult['filtered_count']) }}</h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-custom p-3 border-start border-success border-4">
            <div class="small text-secondary fw-semibold">Average Readiness</div>
            <h3 class="fw-bold text-dark mb-0">{{ $analysisResult['avg_score'] }} <small class="fs-6 text-muted">/100</small></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-custom p-3 border-start border-info border-4">
            <div class="small text-secondary fw-semibold">AI Readiness</div>
            <h3 class="fw-bold text-dark mb-0">{{ $analysisResult['ai_readiness'] }} <small class="fs-6 text-muted">/100</small></h3>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-custom p-3 border-start border-warning border-4">
            <div class="small text-secondary fw-semibold">Practical Readiness</div>
            <h3 class="fw-bold text-dark mb-0">{{ $analysisResult['practical_readiness'] }} <small class="fs-6 text-muted">/100</small></h3>
        </div>
    </div>
</div>

<div class="card-custom p-4">
    <h6 class="fw-bold text-dark mb-3">Filtered Sample Group (First 20 Records)</h6>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Programme</th>
                    <th>Department</th>
                    <th>Grad Year</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($analysisResult['respondents'] as $r)
                    <tr>
                        <td><strong>{{ $r->name }}</strong></td>
                        <td><span class="badge bg-light text-dark border">{{ $r->category_code }}</span></td>
                        <td>{{ $r->programme }}</td>
                        <td>{{ $r->department }}</td>
                        <td>{{ $r->graduation_year }}</td>
                        <td><a href="{{ route('admin.respondents.show', $r->id) }}" class="btn btn-sm btn-light border">View Profile</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
