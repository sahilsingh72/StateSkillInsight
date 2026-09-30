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
            <h3 class="fw-bold text-dark mb-0">
                @if(isset($metrics['conversion_gap']) && $metrics['conversion_gap'] !== null)
                    {{ number_format($metrics['conversion_gap'], 1) }}%
                @else
                    0.0%
                @endif
            </h3>
            <small class="{{ $metrics['conversion_gap_class'] ?? 'text-muted' }}">
                <i class="bi {{ $metrics['conversion_gap_icon'] ?? 'bi-clock' }} me-1"></i> {{ $metrics['conversion_gap_status'] ?? 'Awaiting Responses' }}
            </small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-custom p-3 border-start border-warning border-4">
            <div class="small text-secondary fw-semibold">Career Transition Confidence</div>
            <h3 class="fw-bold text-dark mb-0">
                @if(isset($metrics['experience_score']) && $metrics['experience_score'] !== null)
                    {{ number_format($metrics['experience_score'], 1) }} <small class="fs-6 text-muted">/100</small>
                @else
                    0.0 <small class="fs-6 text-muted">/100</small>
                @endif
            </h3>
            <small class="{{ $metrics['experience_class'] ?? 'text-muted' }}">
                <i class="bi {{ $metrics['experience_icon'] ?? 'bi-clock' }} me-1"></i> {{ $metrics['experience_status'] ?? 'Awaiting Responses' }}
            </small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-custom p-3 border-start border-primary border-4">
            <div class="small text-secondary fw-semibold">Job-Search Resilience</div>
            <h3 class="fw-bold text-dark mb-0">
                @if(isset($metrics['resilience_score']) && $metrics['resilience_score'] !== null)
                    {{ number_format($metrics['resilience_score'], 1) }} <small class="fs-6 text-muted">/100</small>
                @else
                    0.0 <small class="fs-6 text-muted">/100</small>
                @endif
            </h3>
            <small class="{{ $metrics['resilience_class'] ?? 'text-muted' }}">
                <i class="bi {{ $metrics['resilience_icon'] ?? 'bi-clock' }} me-1"></i> {{ $metrics['resilience_status'] ?? 'Awaiting Responses' }}
            </small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-custom p-3 border-start border-success border-4">
            <div class="small text-secondary fw-semibold">Self-Directed Employability</div>
            <h3 class="fw-bold text-dark mb-0">
                @if(isset($metrics['employability_score']) && $metrics['employability_score'] !== null)
                    {{ number_format($metrics['employability_score'], 1) }} <small class="fs-6 text-muted">/100</small>
                @else
                    0.0 <small class="fs-6 text-muted">/100</small>
                @endif
            </h3>
            <small class="{{ $metrics['employability_class'] ?? 'text-muted' }}">
                <i class="bi {{ $metrics['employability_icon'] ?? 'bi-clock' }} me-1"></i> {{ $metrics['employability_status'] ?? 'Awaiting Responses' }}
            </small>
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
                @forelse($respondents as $r)
                    <tr>
                        <td><strong>{{ $r->name }}</strong><br><small class="text-muted">{{ $r->email }}</small></td>
                        <td>{{ $r->programme }}</td>
                        <td>{{ $r->graduation_year }}</td>
                        <td><span class="badge bg-warning text-dark">{{ $r->employment_status }}</span></td>
                        <td><a href="{{ route('admin.respondents.show', $r->id) }}" class="btn btn-sm btn-light border">View Profile</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                            No respondents recorded in this category yet.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
