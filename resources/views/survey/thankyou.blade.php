@extends('layouts.survey')

@section('title', 'Survey Submitted - Thank You')

@section('content')
<div class="container" style="max-width: 650px;">
    <div class="survey-card text-center p-5">
        <div class="rounded-circle bg-success-subtle text-success p-4 d-inline-flex mb-4">
            <i class="bi bi-check-circle-fill display-3"></i>
        </div>
        <h2 class="fw-bold text-dark mb-2">Thank You for Your Valuable Response!</h2>
        <p class="text-secondary mb-4">
            Your survey response has been securely recorded and processed by the {{ $university->name ?? 'University' }} Institutional Research & Career Planning Cell.
        </p>

        <div class="p-4 bg-light rounded-4 border mb-4 text-start small text-secondary">
            <div class="d-flex justify-content-between mb-2">
                <span>Respondent Token:</span>
                <strong class="text-dark">{{ $respondent->token }}</strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span>Category:</span>
                <strong class="text-dark">{{ $respondent->category_code }}</strong>
            </div>
            <div class="d-flex justify-content-between">
                <span>Submission Time:</span>
                <strong class="text-dark">{{ now()->format('d M Y, H:i') }}</strong>
            </div>
        </div>

        <a href="{{ route('survey.landing') }}" class="btn btn-uni-primary px-4">
            <i class="bi bi-house me-1"></i> Return to University Home
        </a>
    </div>
</div>
@endsection
