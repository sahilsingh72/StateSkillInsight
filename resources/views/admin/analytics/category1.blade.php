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
            <div class="small text-secondary fw-semibold">{{ $metrics['tech_name'] ?? 'Technical Capital Index' }}</div>
            <h3 class="fw-bold text-dark mb-0">
                @if(isset($metrics['tech_score']) && $metrics['tech_score'] !== null)
                    {{ number_format($metrics['tech_score'], 1) }} <small class="fs-6 text-muted">/100</small>
                @else
                    0.0 <small class="fs-6 text-muted">/100</small>
                @endif
            </h3>
            <small class="{{ $metrics['tech_status_class'] ?? 'text-muted' }}">
                <i class="bi {{ $metrics['tech_status_icon'] ?? 'bi-clock' }} me-1"></i> {{ $metrics['tech_status'] ?? 'Awaiting Responses' }}
            </small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-custom p-3 border-start border-info border-4">
            <div class="small text-secondary fw-semibold">{{ $metrics['adapt_name'] ?? 'Technological Adaptability' }}</div>
            <h3 class="fw-bold text-dark mb-0">
                @if(isset($metrics['adapt_score']) && $metrics['adapt_score'] !== null)
                    {{ number_format($metrics['adapt_score'], 1) }} <small class="fs-6 text-muted">/100</small>
                @else
                    0.0 <small class="fs-6 text-muted">/100</small>
                @endif
            </h3>
            <small class="{{ $metrics['adapt_status_class'] ?? 'text-muted' }}">
                {{ $metrics['adapt_status'] ?? 'Awaiting Responses' }}
            </small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-custom p-3 border-start border-success border-4">
            <div class="small text-secondary fw-semibold">{{ $metrics['lead_name'] ?? 'Management & Leadership' }}</div>
            <h3 class="fw-bold text-dark mb-0">
                @if(isset($metrics['lead_score']) && $metrics['lead_score'] !== null)
                    {{ number_format($metrics['lead_score'], 1) }} <small class="fs-6 text-muted">/100</small>
                @else
                    0.0 <small class="fs-6 text-muted">/100</small>
                @endif
            </h3>
            <small class="{{ $metrics['lead_status_class'] ?? 'text-muted' }}">
                {{ $metrics['lead_status'] ?? 'Awaiting Responses' }}
            </small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-custom p-3 border-start border-warning border-4">
            <div class="small text-secondary fw-semibold">Curriculum Practice Gap</div>
            <h3 class="fw-bold text-dark mb-0">
                @if(isset($metrics['gap_score']) && $metrics['gap_score'] !== null)
                    {{ number_format($metrics['gap_score'], 1) }}%
                @else
                    0.0%
                @endif
            </h3>
            <small class="{{ $metrics['gap_status_class'] ?? 'text-muted' }}">
                <i class="bi {{ $metrics['gap_status_icon'] ?? 'bi-clock' }} me-1"></i> {{ $metrics['gap_status'] ?? 'Awaiting Responses' }}
            </small>
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
                @forelse($respondents as $r)
                    <tr>
                        <td><strong>{{ $r->name }}</strong><br><small class="text-muted">{{ $r->email }}</small></td>
                        <td>{{ $r->programme }}</td>
                        <td>{{ $r->graduation_year }}</td>
                        <td><span class="badge bg-success">{{ $r->employment_status }}</span></td>
                        <td><a href="{{ route('admin.respondents.show', $r->id) }}" class="btn btn-sm btn-light border">View Profile</a></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-3 d-block mb-1 text-secondary opacity-50"></i>
                            <span>No respondents recorded for this category yet.</span>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($respondents->hasPages())
        <div class="mt-3">
            {{ $respondents->links() }}
        </div>
    @endif
</div>
@endsection
