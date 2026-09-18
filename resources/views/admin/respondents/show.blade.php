@extends('layouts.admin')

@section('title', 'Respondent Detail - ' . $respondent->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="fw-bold text-dark mb-0">{{ $respondent->name }}</h4>
        <small class="text-secondary">Token: {{ $respondent->token }} | Category: {{ $respondent->category_code }}</small>
    </div>
    <a href="{{ route('admin.respondents.index') }}" class="btn btn-light border btn-sm"><i class="bi bi-arrow-left me-1"></i> Back</a>
</div>

<div class="row g-4">
    <div class="col-md-4">
        <div class="card-custom p-4">
            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="bi bi-person-vcard me-2 text-primary"></i> Demographic Profile</h6>
            <div class="small mb-2"><strong>Email:</strong> {{ $respondent->email ?? 'N/A' }}</div>
            <div class="small mb-2"><strong>Mobile:</strong> {{ $respondent->mobile ?? 'N/A' }}</div>
            <div class="small mb-2"><strong>Gender:</strong> {{ $respondent->gender ?? 'N/A' }}</div>
            <div class="small mb-2"><strong>Programme:</strong> {{ $respondent->programme }}</div>
            <div class="small mb-2"><strong>Department:</strong> {{ $respondent->department }}</div>
            <div class="small mb-2"><strong>Graduation Year:</strong> {{ $respondent->graduation_year }}</div>
            <div class="small mb-2"><strong>Location:</strong> {{ $respondent->current_city }}, {{ $respondent->country }}</div>
            <div class="small mb-2"><strong>Consent Given:</strong> Yes ({{ $respondent->consent_at->format('Y-m-d') }})</div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="card-custom p-4">
            <h6 class="fw-bold text-dark border-bottom pb-2 mb-3"><i class="bi bi-journal-text me-2 text-primary"></i> Survey Answers & Score Results</h6>
            
            @foreach($respondent->respondentSurveys as $rSurv)
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-3 p-3 bg-light rounded-3">
                        <div>
                            <div class="fw-bold text-dark">{{ $rSurv->survey->title }}</div>
                            <small class="text-muted">Status: {{ strtoupper($rSurv->status) }} | Progress: {{ $rSurv->completion_percentage }}%</small>
                        </div>
                        @php
                            $scoreRec = $rSurv->scores->firstWhere('dimension_id', null);
                        @endphp
                        @if($scoreRec)
                            <div class="text-end">
                                <div class="fs-4 fw-bold text-primary">{{ $scoreRec->score }} / 100</div>
                                <span class="badge bg-success">{{ $scoreRec->interpretation_band }}</span>
                            </div>
                        @endif
                    </div>

                    <div class="list-group">
                        @foreach($rSurv->responses as $resp)
                            <div class="list-group-item bg-white py-3">
                                <div class="fw-semibold text-dark small mb-1">{{ $resp->question->question_text }}</div>
                                <div class="text-primary small fw-semibold">
                                    <i class="bi bi-chat-left-text me-1"></i> {{ $resp->text_value ?? 'N/A' }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
