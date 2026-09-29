@extends('layouts.survey')

@section('title', 'Survey Closed / Unavailable')

@section('content')
<div class="min-vh-100 d-flex align-items-center justify-content-center py-5 px-3" style="background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%);">
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden" style="max-width: 620px; width: 100%;">
        <div class="p-4 p-md-5 text-center">
            @if(!$survey->hasStarted())
                <!-- Not Started Yet -->
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px; background: #fef3c7; color: #d97706;">
                    <i class="bi bi-clock-history fs-1"></i>
                </div>
                <h3 class="fw-bold text-dark mb-2">Survey Not Yet Open</h3>
                <p class="text-secondary mb-4">
                    This survey is scheduled to begin on 
                    <strong class="text-dark">{{ \Carbon\Carbon::parse($survey->start_date)->format('F d, Y') }}</strong>. 
                    Please check back once the survey window is active.
                </p>
                <div class="p-3 rounded-3 bg-light border text-start mb-4">
                    <div class="d-flex justify-content-between small text-secondary mb-1">
                        <span>Campaign:</span>
                        <strong class="text-dark">{{ $survey->title }}</strong>
                    </div>
                    <div class="d-flex justify-content-between small text-secondary">
                        <span>Scheduled Duration:</span>
                        <strong class="text-dark">
                            {{ \Carbon\Carbon::parse($survey->start_date)->format('M d, Y') }} &rarr; 
                            {{ $survey->end_date ? \Carbon\Carbon::parse($survey->end_date)->format('M d, Y') : 'Open Ended' }}
                        </strong>
                    </div>
                </div>
            @elseif($survey->isExpired())
                <!-- Expired -->
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px; background: #fee2e2; color: #dc2626;">
                    <i class="bi bi-calendar-x-fill fs-1"></i>
                </div>
                <h3 class="fw-bold text-dark mb-2">Survey Period Ended</h3>
                <p class="text-secondary mb-4">
                    The participation window for this survey concluded on 
                    <strong class="text-dark">{{ \Carbon\Carbon::parse($survey->end_date)->format('F d, Y') }}</strong>. 
                    We are no longer accepting new responses. Thank you for your interest.
                </p>
                <div class="p-3 rounded-3 bg-light border text-start mb-4">
                    <div class="d-flex justify-content-between small text-secondary mb-1">
                        <span>Campaign:</span>
                        <strong class="text-dark">{{ $survey->title }}</strong>
                    </div>
                    <div class="d-flex justify-content-between small text-secondary">
                        <span>Concluded On:</span>
                        <strong class="text-dark">{{ \Carbon\Carbon::parse($survey->end_date)->format('M d, Y') }}</strong>
                    </div>
                </div>
            @elseif($survey->isQuotaFull())
                <!-- Quota Full -->
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px; background: #fee2e2; color: #dc2626;">
                    <i class="bi bi-people-fill fs-1"></i>
                </div>
                <h3 class="fw-bold text-dark mb-2">Respondent Limit Reached</h3>
                <p class="text-secondary mb-4">
                    This survey has reached its target quota of 
                    <strong class="text-dark">{{ number_format($survey->max_respondents) }}</strong> respondents and is now closed to new entries.
                </p>
                <div class="p-3 rounded-3 bg-light border text-start mb-4">
                    <div class="d-flex justify-content-between small text-secondary mb-1">
                        <span>Campaign:</span>
                        <strong class="text-dark">{{ $survey->title }}</strong>
                    </div>
                    <div class="d-flex justify-content-between small text-secondary">
                        <span>Target Quota:</span>
                        <strong class="text-dark">{{ number_format($survey->max_respondents) }} Participants</strong>
                    </div>
                </div>
            @else
                <!-- Inactive / Draft -->
                <div class="rounded-circle d-inline-flex align-items-center justify-content-center mb-4" style="width: 80px; height: 80px; background: #e0e7ff; color: #4338ca;">
                    <i class="bi bi-pause-circle-fill fs-1"></i>
                </div>
                <h3 class="fw-bold text-dark mb-2">Survey Currently Inactive</h3>
                <p class="text-secondary mb-4">
                    This survey campaign is currently not accepting responses. Please contact the administrator if you believe this is an error.
                </p>
            @endif

        </div>
    </div>
</div>
@endsection
