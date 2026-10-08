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
            @if(auth()->check() && auth()->user()->isSuperAdmin())
                <div class="small mb-2">
                    <strong>Institution:</strong> 
                    @if($respondent->university)
                        <span class="badge bg-primary-subtle text-primary border ms-1">
                            <i class="bi bi-building me-1"></i> {{ $respondent->university->name }} ({{ $respondent->university->short_name }})
                        </span>
                    @else
                        <span class="text-muted">N/A</span>
                    @endif
                </div>
            @endif
            <div class="small mb-2"><strong>Email:</strong> {{ $respondent->email ?? 'N/A' }}</div>
            <div class="small mb-2"><strong>Mobile:</strong> {{ $respondent->mobile ?? 'N/A' }}</div>
            <div class="small mb-2"><strong>Gender:</strong> {{ $respondent->gender ?? 'N/A' }}</div>
            <div class="small mb-2"><strong>Programme:</strong> {{ $respondent->programme }}</div>
            <div class="small mb-2"><strong>Department:</strong> {{ $respondent->department }}</div>
            <div class="small mb-2"><strong>Graduation Year:</strong> {{ $respondent->graduation_year }}</div>
            <div class="small mb-2"><strong>Location:</strong> {{ $respondent->current_city }}, {{ $respondent->country }}</div>
            <div class="small mb-2"><strong>Consent Given:</strong> Yes ({{ $respondent->consent_at ? $respondent->consent_at->format('Y-m-d') : 'Yes' }})</div>
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
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <div class="fw-semibold text-dark small">{{ $resp->question->question_text ?? 'Question' }}</div>
                                    @if($resp->question && $resp->question->type)
                                        <span class="badge bg-light text-muted border text-uppercase ms-2" style="font-size: 0.65rem;">
                                            {{ str_replace('_', ' ', $resp->question->type) }}
                                        </span>
                                    @endif
                                </div>

                                @php
                                    $isVoice = ($resp->question && $resp->question->type === 'voice') 
                                        || $resp->voiceResponse 
                                        || str_contains($resp->text_value ?? '', '[Voice Recording Uploaded');
                                    $voice = $resp->voiceResponse;
                                @endphp

                                @if($isVoice && $voice && $voice->file_path)
                                    <div class="mt-2 p-3 bg-light rounded-3 border">
                                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="rounded-circle bg-primary bg-opacity-10 p-2 d-flex align-items-center justify-content-center text-primary" style="width: 34px; height: 34px;">
                                                    <i class="bi bi-mic-fill"></i>
                                                </div>
                                                <div>
                                                    <div class="d-flex align-items-center gap-2">
                                                        <span class="badge bg-primary-subtle text-primary border" style="font-size: 0.7rem;">Voice Answer</span>
                                                        @if($voice->duration_seconds)
                                                            <span class="badge bg-white text-secondary border" style="font-size: 0.7rem;">
                                                                <i class="bi bi-clock me-1"></i> {{ $voice->duration_seconds }}s
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="d-flex align-items-center gap-2">
                                                <audio controls preload="none" style="height: 36px; max-width: 250px;">
                                                    <source src="{{ Storage::url($voice->file_path) }}" type="{{ $voice->mime_type ?? 'audio/webm' }}">
                                                    Your browser does not support audio playback.
                                                </audio>
                                                <a href="{{ Storage::url($voice->file_path) }}" download class="btn btn-sm btn-outline-secondary" title="Download Recording">
                                                    <i class="bi bi-download"></i>
                                                </a>
                                            </div>
                                        </div>

                                        @if($voice->transcript_text)
                                            <div class="mt-2 p-2 bg-white rounded border small text-dark">
                                                <i class="bi bi-quote text-primary me-1"></i> {{ $voice->transcript_text }}
                                            </div>
                                        @endif
                                    </div>
                                @elseif($isVoice)
                                    <div class="mt-2 p-2 bg-light rounded border text-muted small d-flex align-items-center gap-2">
                                        <i class="bi bi-mic-mute text-warning fs-5"></i>
                                        <div>
                                            <span>{{ $resp->text_value ?? 'Voice response recorded' }}</span>
                                            <small class="d-block text-secondary">(Audio file not found on disk or processing)</small>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-primary small fw-semibold mt-1">
                                        <i class="bi bi-chat-left-text me-1"></i> {{ $resp->text_value ?? 'N/A' }}
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
