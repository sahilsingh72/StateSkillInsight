@extends('layouts.admin')

@section('title', 'Category 4: Educationally Interrupted Students Analytics')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <span class="badge bg-danger text-white fw-bold mb-1">Category 4 Research</span>
        <h4 class="fw-bold text-dark mb-0">Interrupted Education Dashboard</h4>
        <p class="text-secondary small mb-0">Supportive Rehabilitation & Re-engagement Evidence.</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card-custom p-3 border-start border-success border-4">
            <div class="small text-secondary fw-semibold">Re-entry Interest</div>
            <h3 class="fw-bold text-dark mb-0">72.4%</h3>
            <small class="text-success"><i class="bi bi-arrow-repeat me-1"></i> High Willingness to Complete</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-custom p-3 border-start border-primary border-4">
            <div class="small text-secondary fw-semibold">Retained Capability Index</div>
            <h3 class="fw-bold text-dark mb-0">60.0 <small class="fs-6 text-muted">/100</small></h3>
            <small class="text-primary">Foundational Knowledge Retained</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-custom p-3 border-start border-warning border-4">
            <div class="small text-secondary fw-semibold">Apprenticeship Readiness</div>
            <h3 class="fw-bold text-dark mb-0">65.8%</h3>
            <small class="text-warning">Work-based Skill Preference</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-custom p-3 border-start border-info border-4">
            <div class="small text-secondary fw-semibold">Entrepreneurial Potential</div>
            <h3 class="fw-bold text-dark mb-0">34.2%</h3>
            <small class="text-info">Self-Livelihood Interest</small>
        </div>
    </div>
</div>

<div class="card-custom p-4 mb-4">
    <h6 class="fw-bold text-dark mb-3">Interrupted Student Respondents</h6>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Programme</th>
                    <th>Admission Year</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($respondents as $r)
                    <tr>
                        <td><strong>{{ $r->name }}</strong><br><small class="text-muted">{{ $r->email }}</small></td>
                        <td>{{ $r->programme }}</td>
                        <td>{{ $r->admission_year }}</td>
                        <td><span class="badge bg-danger">{{ $r->employment_status }}</span></td>
                        <td><a href="{{ route('admin.respondents.show', $r->id) }}" class="btn btn-sm btn-light border">View Profile</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
