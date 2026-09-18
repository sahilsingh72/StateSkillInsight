@extends('layouts.admin')

@section('title', 'Category 3: Current Students Analytics')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <span class="badge bg-warning text-dark fw-bold mb-1">Category 3 Research</span>
        <h4 class="fw-bold text-dark mb-0">Current Student Dashboard</h4>
        <p class="text-secondary small mb-0">Pre-Graduation Future Professional Readiness Map.</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card-custom p-3 border-start border-primary border-4">
            <div class="small text-secondary fw-semibold">Academic Foundation</div>
            <h3 class="fw-bold text-dark mb-0">75.0 <small class="fs-6 text-muted">/100</small></h3>
            <small class="text-success">Strong Theory Confidence</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-custom p-3 border-start border-warning border-4">
            <div class="small text-secondary fw-semibold">Practical/Industry Exposure</div>
            <h3 class="fw-bold text-dark mb-0">52.4 <small class="fs-6 text-muted">/100</small></h3>
            <small class="text-danger"><i class="bi bi-exclamation-circle me-1"></i> Major Gap Identified</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-custom p-3 border-start border-info border-4">
            <div class="small text-secondary fw-semibold">AI & Digital Readiness</div>
            <h3 class="fw-bold text-dark mb-0">66.2 <small class="fs-6 text-muted">/100</small></h3>
            <small class="text-primary">Emerging Tool Usage</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-custom p-3 border-start border-success border-4">
            <div class="small text-secondary fw-semibold">Career Clarity</div>
            <h3 class="fw-bold text-dark mb-0">78.0 <small class="fs-6 text-muted">/100</small></h3>
            <small class="text-success">Clear Professional Goal</small>
        </div>
    </div>
</div>

<div class="card-custom p-4 mb-4">
    <h6 class="fw-bold text-dark mb-3">Current Student Respondents</h6>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Programme</th>
                    <th>Dept</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($respondents as $r)
                    <tr>
                        <td><strong>{{ $r->name }}</strong><br><small class="text-muted">{{ $r->email }}</small></td>
                        <td>{{ $r->programme }}</td>
                        <td>{{ $r->department }}</td>
                        <td><span class="badge bg-info text-dark">{{ $r->employment_status }}</span></td>
                        <td><a href="{{ route('admin.respondents.show', $r->id) }}" class="btn btn-sm btn-light border">View Profile</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
