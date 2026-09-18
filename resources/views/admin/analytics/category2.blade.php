@extends('layouts.admin')

@section('title', 'Category 2: Job-Seeking Alumni Analytics')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <span class="badge bg-teal text-white fw-bold mb-1" style="background:var(--uni-secondary);">Category 2 Research</span>
        <h4 class="fw-bold text-dark mb-0">Job-Seeking Alumni Dashboard</h4>
        <p class="text-secondary small mb-0">Employment Transition Evidence & Recruitment Stage Difficulties.</p>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card-custom p-3 border-start border-danger border-4">
            <div class="small text-secondary fw-semibold">Interview Conversion Gap</div>
            <h3 class="fw-bold text-dark mb-0">42.8%</h3>
            <small class="text-danger">Highest Bottleneck Stage</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-custom p-3 border-start border-warning border-4">
            <div class="small text-secondary fw-semibold">Practical Experience Gap</div>
            <h3 class="fw-bold text-dark mb-0">48.2 <small class="fs-6 text-muted">/100</small></h3>
            <small class="text-warning">Real Project Shortfall</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-custom p-3 border-start border-primary border-4">
            <div class="small text-secondary fw-semibold">Job-Search Resilience</div>
            <h3 class="fw-bold text-dark mb-0">70.5 <small class="fs-6 text-muted">/100</small></h3>
            <small class="text-primary">Continuous Learning Agility</small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-custom p-3 border-start border-success border-4">
            <div class="small text-secondary fw-semibold">Self-Directed Employability</div>
            <h3 class="fw-bold text-dark mb-0">65.0 <small class="fs-6 text-muted">/100</small></h3>
            <small class="text-success">Online Certification Upskilling</small>
        </div>
    </div>
</div>

<div class="card-custom p-4 mb-4">
    <h6 class="fw-bold text-dark mb-3">Job-Seeking Alumni Respondents</h6>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Name</th>
                    <th>Programme</th>
                    <th>Grad Year</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($respondents as $r)
                    <tr>
                        <td><strong>{{ $r->name }}</strong><br><small class="text-muted">{{ $r->email }}</small></td>
                        <td>{{ $r->programme }}</td>
                        <td>{{ $r->graduation_year }}</td>
                        <td><span class="badge bg-warning text-dark">{{ $r->employment_status }}</span></td>
                        <td><a href="{{ route('admin.respondents.show', $r->id) }}" class="btn btn-sm btn-light border">View Profile</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
