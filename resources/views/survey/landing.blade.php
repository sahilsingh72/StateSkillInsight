@extends('layouts.survey')

@section('title', 'Institutional Research & Career Survey 2026')

@section('content')
<div class="container">
    <!-- Hero Section -->
    <div class="survey-card text-center bg-white p-5 border-0 shadow-sm" style="border-radius:24px;">
        <div class="badge px-3 py-2 rounded-pill mb-3" style="background:#eff6ff; color:var(--uni-primary); font-weight:600;">
            <i class="bi bi-award me-1"></i> Official Institutional Research Study
        </div>
        <h1 class="fw-bold display-6 mb-3 text-dark">{{ $survey->title ?? 'National University Education–Employment Continuum Research Study' }}</h1>
        <p class="lead text-secondary mx-auto mb-4" style="max-width: 800px;">
            {{ $survey->description ?? 'Empirical study connecting academic learning, contemporary workplace practice, employability barriers, and interrupted education pathways.' }}
        </p>

        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="#categories" class="btn btn-uni-primary btn-lg shadow-sm px-4">
                Select Your Category & Start Survey <i class="bi bi-arrow-down-circle ms-2"></i>
            </a>
        </div>
    </div>

    <!-- Education-Employment Continuum Explanation -->
    <div class="my-5 text-center">
        <h4 class="fw-bold text-dark mb-2">The Four-Category Research Continuum</h4>
        <p class="text-secondary">Connecting student preparation to career reality for data-driven curriculum evolution.</p>
        
        <div class="p-3 bg-light rounded-4 border d-flex justify-content-around flex-wrap text-center gap-2">
            <div><strong class="text-primary">1. Working Alumni</strong> <br><small class="text-muted">Practice Evidence</small></div>
            <div><i class="bi bi-arrow-right text-muted fs-4"></i></div>
            <div><strong class="text-teal" style="color:var(--uni-secondary);">2. Job-Seeking Alumni</strong> <br><small class="text-muted">Transition Evidence</small></div>
            <div><i class="bi bi-arrow-right text-muted fs-4"></i></div>
            <div><strong class="text-warning">3. Current Students</strong> <br><small class="text-muted">Pre-Graduation Readiness</small></div>
            <div><i class="bi bi-arrow-right text-muted fs-4"></i></div>
            <div><strong class="text-danger">4. Dropped-out Students</strong> <br><small class="text-muted">Re-engagement Pathways</small></div>
        </div>
    </div>

    <!-- 4 Category Cards -->
    <div id="categories" class="row g-4 mb-5">
        @foreach($categories as $cat)
            <div class="col-md-6 col-lg-3">
                <div class="card h-100 border-0 shadow-sm rounded-4 p-4 text-center d-flex flex-column justify-content-between" style="background:#ffffff; transition:transform 0.2s ease;" onmouseover="this.style.transform='translateY(-5px)'" onmouseout="this.style.transform='translateY(0)'">
                    <div>
                        <div class="rounded-circle p-3 mx-auto mb-3 d-flex align-items-center justify-content-center" style="background:#eff6ff; color:var(--uni-primary); width:64px; height:64px;">
                            <i class="bi {{ $cat->icon ?? 'bi-person-badge' }} fs-2"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">{{ $cat->name }}</h5>
                        <p class="text-secondary small mb-3">{{ $cat->description }}</p>
                    </div>
                    <a href="{{ route('survey.register', $cat->code) }}" class="btn btn-uni-primary w-100">
                        Start The Survey <i class="bi bi-chevron-right ms-1"></i>
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Research Purpose & Privacy -->
    <div class="row g-4 mb-5">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <h5 class="fw-bold text-dark mb-3"><i class="bi bi-journal-check text-primary me-2"></i> Research Purpose</h5>
                <ul class="text-secondary small mb-0 ps-3">
                    <li class="mb-2">Evaluate academic knowledge relevance in contemporary workplaces.</li>
                    <li class="mb-2">Identify recruitment-stage bottlenecks for job-seeking graduates.</li>
                    <li class="mb-2">Assess student practical, AI, and digital competency pre-graduation.</li>
                    <li>Re-engage interrupted learners through skill mapping and credit transfer.</li>
                </ul>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 p-4 h-100">
                <h5 class="fw-bold text-dark mb-3"><i class="bi bi-shield-check text-success me-2"></i> Confidentiality & Privacy</h5>
                <p class="text-secondary small mb-3">
                    All responses are strictly used for institutional research, curriculum refinement, and state career support policies. Individual respondent data confidentiality is strictly guaranteed.
                </p>
                <div class="text-muted small">
                    <i class="bi bi-lock me-1"></i> SSL Encrypted & Token Secured Submission
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
