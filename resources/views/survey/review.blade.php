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

        @foreach($sections as $sec)
            <div class="mb-4 p-3 bg-light rounded-4 border">
                <h6 class="fw-bold text-dark mb-2"><i class="bi bi-folder2-open me-2 text-primary"></i> {{ $sec->title }}</h6>
                <div class="list-group list-group-flush rounded-3">
                    @foreach($sec->questions as $q)
                        <div class="list-group-item bg-white py-3">
                            <div class="fw-semibold text-dark small mb-1">{{ $q->question_text }}</div>
                            <div class="text-primary small">
                                <i class="bi bi-chat-left-text me-1"></i>
                                {{ $responses[$q->id]->text_value ?? 'No response provided' }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach

        <form action="{{ route('survey.submit', $respondent->token) }}" method="POST">
            @csrf
            <div class="d-flex justify-content-between align-items-center pt-3 border-top">
                <a href="{{ route('survey.take', ['token' => $respondent->token]) }}" class="btn btn-outline-secondary px-4">
                    <i class="bi bi-arrow-left me-1"></i> Edit Answers
                </a>
                <button type="submit" class="btn btn-success btn-lg px-5 fw-bold shadow-sm">
                    Submit Final Survey <i class="bi bi-send ms-2"></i>
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
