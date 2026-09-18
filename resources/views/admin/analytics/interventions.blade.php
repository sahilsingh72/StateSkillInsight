@extends('layouts.admin')

@section('title', 'Institutional Intervention Engine')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-1">Institutional Intervention Engine & Recommended Actions</h4>
        <p class="text-secondary small mb-0">Automated classification of respondent skill gaps into actionable support profiles.</p>
    </div>
</div>

<!-- Active Rules Grid -->
<div class="row g-4 mb-4">
    @foreach($rules as $r)
        <div class="col-md-6">
            <div class="card-custom p-4 h-100 border-start border-{{ $r->priority == 'high' ? 'danger' : 'warning' }} border-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="fw-bold text-dark mb-0">{{ $r->name }}</h6>
                    <span class="badge bg-{{ $r->priority == 'high' ? 'danger' : 'warning' }} text-white">{{ strtoupper($r->priority) }} PRIORITY</span>
                </div>
                <div class="small text-secondary mb-2"><strong>Category Code:</strong> {{ $r->category_code }}</div>
                <div class="p-3 bg-light rounded-3 border mb-3 small text-dark">
                    <strong>Recommended Action:</strong> {{ $r->recommended_intervention }}
                </div>
            </div>
        </div>
    @endforeach
</div>

<!-- Assigned Interventions List -->
<div class="card-custom p-4">
    <h6 class="fw-bold text-dark mb-3">Assigned Respondent Interventions</h6>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Respondent</th>
                    <th>Intervention Triggered</th>
                    <th>Priority</th>
                    <th>Date Assigned</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($interventions as $item)
                    <tr>
                        <td>
                            <strong>{{ $item->respondentSurvey->respondent->name ?? 'Respondent' }}</strong><br>
                            <small class="text-muted">{{ $item->respondentSurvey->respondent->email ?? '' }}</small>
                        </td>
                        <td>
                            <div class="fw-bold text-dark small">{{ $item->rule->name ?? 'Rule' }}</div>
                            <small class="text-secondary">{{ Str::limit($item->rule->recommended_intervention ?? '', 45) }}</small>
                        </td>
                        <td>
                            <span class="badge bg-{{ ($item->rule->priority ?? '') == 'high' ? 'danger' : 'warning' }}">{{ strtoupper($item->rule->priority ?? 'MEDIUM') }}</span>
                        </td>
                        <td>{{ $item->created_at->format('d M Y') }}</td>
                        <td>
                            <a href="{{ route('admin.respondents.show', $item->respondentSurvey->respondent_id) }}" class="btn btn-sm btn-light border">View Respondent</a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection
