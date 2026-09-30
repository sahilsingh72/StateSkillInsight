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
            <h3 class="fw-bold text-dark mb-0">
                @if(isset($metrics['academic_foundation']) && $metrics['academic_foundation'] !== null)
                    {{ number_format($metrics['academic_foundation'], 1) }} <small class="fs-6 text-muted">/100</small>
                @else
                    0.0 <small class="fs-6 text-muted">/100</small>
                @endif
            </h3>
            <small class="{{ $metrics['academic_class'] ?? 'text-muted' }}">
                <i class="bi {{ $metrics['academic_icon'] ?? 'bi-clock' }} me-1"></i> {{ $metrics['academic_status'] ?? 'Awaiting Responses' }}
            </small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-custom p-3 border-start border-warning border-4">
            <div class="small text-secondary fw-semibold">Practical/Industry Exposure</div>
            <h3 class="fw-bold text-dark mb-0">
                @if(isset($metrics['practical_exposure']) && $metrics['practical_exposure'] !== null)
                    {{ number_format($metrics['practical_exposure'], 1) }} <small class="fs-6 text-muted">/100</small>
                @else
                    0.0 <small class="fs-6 text-muted">/100</small>
                @endif
            </h3>
            <small class="{{ $metrics['practical_class'] ?? 'text-muted' }}">
                <i class="bi {{ $metrics['practical_icon'] ?? 'bi-clock' }} me-1"></i> {{ $metrics['practical_status'] ?? 'Awaiting Responses' }}
            </small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-custom p-3 border-start border-info border-4">
            <div class="small text-secondary fw-semibold">AI & Digital Readiness</div>
            <h3 class="fw-bold text-dark mb-0">
                @if(isset($metrics['ai_readiness']) && $metrics['ai_readiness'] !== null)
                    {{ number_format($metrics['ai_readiness'], 1) }} <small class="fs-6 text-muted">/100</small>
                @else
                    0.0 <small class="fs-6 text-muted">/100</small>
                @endif
            </h3>
            <small class="{{ $metrics['ai_class'] ?? 'text-muted' }}">
                <i class="bi {{ $metrics['ai_icon'] ?? 'bi-clock' }} me-1"></i> {{ $metrics['ai_status'] ?? 'Awaiting Responses' }}
            </small>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card-custom p-3 border-start border-success border-4">
            <div class="small text-secondary fw-semibold">Career Clarity</div>
            <h3 class="fw-bold text-dark mb-0">
                @if(isset($metrics['career_clarity']) && $metrics['career_clarity'] !== null)
                    {{ number_format($metrics['career_clarity'], 1) }} <small class="fs-6 text-muted">/100</small>
                @else
                    0.0 <small class="fs-6 text-muted">/100</small>
                @endif
            </h3>
            <small class="{{ $metrics['career_class'] ?? 'text-muted' }}">
                <i class="bi {{ $metrics['career_icon'] ?? 'bi-clock' }} me-1"></i> {{ $metrics['career_status'] ?? 'Awaiting Responses' }}
            </small>
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
                @forelse($respondents as $r)
                    <tr>
                        <td><strong>{{ $r->name }}</strong><br><small class="text-muted">{{ $r->email }}</small></td>
                        <td>{{ $r->programme }}</td>
                        <td>{{ $r->department }}</td>
                        <td><span class="badge bg-info text-dark">{{ $r->employment_status }}</span></td>
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
