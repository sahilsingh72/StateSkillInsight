@extends('layouts.admin')

@section('title', 'Category 1: Working Alumni Analytics')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <span class="badge bg-primary-subtle text-primary fw-bold mb-1">Category 1 Research</span>
        <h4 class="fw-bold text-dark mb-0">Working Alumni Dashboard</h4>
        <p class="text-secondary small mb-0">Professional Practice Evidence & Curriculum Relevance.</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card-custom p-3 border-start border-primary border-4">
            <div class="small text-secondary fw-semibold">Technical Capital Index</div>
            <h3 class="fw-bold text-dark mb-0">82.4 <small class="fs-6 text-muted">/100</small></h3>
            <small class="text-success"><i class="bi bi-check-circle me-1"></i> Core Foundations Strong</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-custom p-3 border-start border-info border-4">
            <div class="small text-secondary fw-semibold">Technological Adaptability</div>
            <h3 class="fw-bold text-dark mb-0">74.1 <small class="fs-6 text-muted">/100</small></h3>
            <small class="text-primary">Tool Adoption Speed</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-custom p-3 border-start border-success border-4">
            <div class="small text-secondary fw-semibold">Management & Leadership</div>
            <h3 class="fw-bold text-dark mb-0">68.5 <small class="fs-6 text-muted">/100</small></h3>
            <small class="text-muted">Executive Orientation</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-custom p-3 border-start border-warning border-4">
            <div class="small text-secondary fw-semibold">Curriculum Practice Gap</div>
            <h3 class="fw-bold text-dark mb-0">28.6%</h3>
            <small class="text-danger"><i class="bi bi-exclamation-triangle me-1"></i> Requires Modernization</small>
        </div>
    </div>
</div>

<div class="card-custom p-4 mb-4">
    <h6 class="fw-bold text-dark mb-3">Working Alumni Respondents</h6>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Programme</th>
                    <th>Grad Year</th>
                    <th>Employment Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($respondents as $r)
                    <tr>
                        <td><strong>{{ $r->name }}</strong><br><small class="text-muted">{{ $r->email }}</small></td>
                        <td>{{ $r->programme }}</td>
                        <td>{{ $r->graduation_year }}</td>
                        <td><span class="badge bg-success">{{ $r->employment_status }}</span></td>
                        <td><a href="{{ route('admin.respondents.show', $r->id) }}" class="btn btn-sm btn-light border">View Profile</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
