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
            <h3 class="fw-bold text-dark mb-0">
                @if(isset($metrics['reentry_interest']) && $metrics['reentry_interest'] !== null)
                    {{ number_format($metrics['reentry_interest'], 1) }}%
                @else
                    0.0%
                @endif
            </h3>
            <small class="{{ $metrics['reentry_class'] ?? 'text-muted' }}">
                <i class="bi {{ $metrics['reentry_icon'] ?? 'bi-clock' }} me-1"></i> {{ $metrics['reentry_status'] ?? 'Awaiting Responses' }}
            </small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-custom p-3 border-start border-primary border-4">
            <div class="small text-secondary fw-semibold">Retained Capability Index</div>
            <h3 class="fw-bold text-dark mb-0">
                @if(isset($metrics['retained_capability']) && $metrics['retained_capability'] !== null)
                    {{ number_format($metrics['retained_capability'], 1) }} <small class="fs-6 text-muted">/100</small>
                @else
                    0.0 <small class="fs-6 text-muted">/100</small>
                @endif
            </h3>
            <small class="{{ $metrics['retained_class'] ?? 'text-muted' }}">
                <i class="bi {{ $metrics['retained_icon'] ?? 'bi-clock' }} me-1"></i> {{ $metrics['retained_status'] ?? 'Awaiting Responses' }}
            </small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-custom p-3 border-start border-warning border-4">
            <div class="small text-secondary fw-semibold">Apprenticeship Readiness</div>
            <h3 class="fw-bold text-dark mb-0">
                @if(isset($metrics['apprenticeship_readiness']) && $metrics['apprenticeship_readiness'] !== null)
                    {{ number_format($metrics['apprenticeship_readiness'], 1) }}%
                @else
                    0.0%
                @endif
            </h3>
            <small class="{{ $metrics['apprenticeship_class'] ?? 'text-muted' }}">
                <i class="bi {{ $metrics['apprenticeship_icon'] ?? 'bi-clock' }} me-1"></i> {{ $metrics['apprenticeship_status'] ?? 'Awaiting Responses' }}
            </small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-custom p-3 border-start border-info border-4">
            <div class="small text-secondary fw-semibold">Entrepreneurial Potential</div>
            <h3 class="fw-bold text-dark mb-0">
                @if(isset($metrics['entrepreneurial_potential']) && $metrics['entrepreneurial_potential'] !== null)
                    {{ number_format($metrics['entrepreneurial_potential'], 1) }}%
                @else
                    0.0%
                @endif
            </h3>
            <small class="{{ $metrics['entrepreneurial_class'] ?? 'text-muted' }}">
                <i class="bi {{ $metrics['entrepreneurial_icon'] ?? 'bi-clock' }} me-1"></i> {{ $metrics['entrepreneurial_status'] ?? 'Awaiting Responses' }}
            </small>
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
                @forelse($respondents as $r)
                    <tr>
                        <td><strong>{{ $r->name }}</strong><br><small class="text-muted">{{ $r->email }}</small></td>
                        <td>{{ $r->programme }}</td>
                        <td>{{ $r->admission_year }}</td>
                        <td><span class="badge bg-danger">{{ $r->employment_status }}</span></td>
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
