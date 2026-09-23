@extends('layouts.survey')

@section('title', 'Review Answers & Submit')

@section('content')
<div class="container" style="max-width: 850px;">
    <div class="survey-card">
        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
            <div>
                <span class="badge bg-light text-primary border mb-1">Final Step</span>
                <h4 class="fw-bold text-dark mb-0">Review Your Responses</h4>
            </div>
            <div class="badge bg-success px-3 py-2 fs-6">
                100% Completed
            </div>
        </div>

        <p class="text-secondary mb-4">
            Please review your answers below before submitting your survey response. You can return to any section to edit your answers.
        </p>

        @php
            $hasUnansweredRequired = false;
        @endphp

        @foreach($sections as $sec)
            <div class="mb-4 p-3 bg-light rounded-4 border">
                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-folder2-open me-2 text-primary"></i> {{ $sec->title }}</h6>
                <div class="list-group list-group-flush rounded-3">
                    @foreach($sec->questions as $q)
                        @php
                            $resp = $responses[$q->id] ?? null;
                            $isAnswered = $resp && (trim($resp->text_value ?? '') !== '' || !empty($resp->json_value));
                            if ($q->is_required && !$isAnswered) {
                                $hasUnansweredRequired = true;
                            }
                        @endphp
                        <div class="list-group-item bg-white py-3 {{ ($q->is_required && !$isAnswered) ? 'border border-danger rounded-3 mb-2 bg-danger-subtle' : '' }}">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <div class="fw-semibold text-dark small">
                                    {{ $q->question_text }}
                                    @if($q->is_required)
                                        <span class="text-danger" title="Required Question">*</span>
                                        <span class="badge bg-danger-subtle text-danger border ms-1" style="font-size:0.65rem;">Must to answer</span>
                                    @endif
                                </div>
                                @if($q->is_required && !$isAnswered)
                                    <span class="badge bg-danger text-white small"><i class="bi bi-exclamation-circle me-1"></i> Missing Required Answer</span>
                                @endif
                            </div>
                            <div class="{{ $isAnswered ? 'text-primary' : 'text-danger font-monospace' }} small">
                                <i class="bi bi-chat-left-text me-1"></i>
                                {{ $isAnswered ? $resp->text_value : 'No response provided' }}
                            </div>
                            @if($q->is_required && !$isAnswered)
                                <div class="mt-2">
                                    <a href="{{ route('survey.take', ['token' => $respondent->token, 'section' => $sec->id]) }}" class="btn btn-outline-danger btn-sm py-1 px-3">
                                        <i class="bi bi-pencil-square me-1"></i> Answer this question in {{ $sec->title }}
                                    </a>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        @if($hasUnansweredRequired)
            <div class="alert alert-danger d-flex align-items-center mb-4 rounded-3 border-danger shadow-sm">
                <i class="bi bi-exclamation-triangle-fill fs-4 me-3 text-danger"></i>
                <div>
                    <strong class="d-block">Required Questions Incomplete</strong>
                    <span>You have unanswered required questions. Please complete all questions marked as <strong>Must to answer (*)</strong> before submitting.</span>
                </div>
            </div>
        @endif

        <form action="{{ route('survey.submit', $respondent->token) }}" method="POST">
            @csrf
            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                <a href="{{ route('survey.take', ['token' => $respondent->token]) }}" class="btn btn-outline-secondary px-4">
                    <i class="bi bi-arrow-left me-1"></i> Edit Answers
                </a>
                <button type="submit" class="btn btn-success btn-lg px-5 fw-bold shadow-sm" {{ $hasUnansweredRequired ? 'disabled' : '' }}>
                    Submit Final Survey <i class="bi bi-send ms-2"></i>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
