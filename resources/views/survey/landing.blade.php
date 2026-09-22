@extends('layouts.survey')

@section('title', 'Institutional Research & Career Survey 2026')

@push('styles')
<style>
    /* Ultra Modern Blue & White Palette & Animations */
    :root {
        --bw-navy-dark: #0f172a;
        --bw-blue-deep: #1e3a8a;
        --bw-blue-primary: #1e40af;
        --bw-blue-vibrant: #2563eb;
        --bw-blue-accent: #3b82f6;
        --bw-blue-light: #60a5fa;
        --bw-blue-soft: #dbeafe;
        --bw-blue-subtle: #eff6ff;
        --bw-white: #ffffff;
    }

    body {
        background-color: #f8fafc;
        color: #1e293b;
        overflow-x: hidden;
    }

    /* Keyframe Animations */
    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(24px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    @keyframes floatSlow {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(-14px) rotate(3deg); }
    }

    @keyframes floatReverse {
        0%, 100% { transform: translateY(0px) rotate(0deg); }
        50% { transform: translateY(14px) rotate(-3deg); }
    }

    @keyframes liveDotPulse {
        0% { transform: scale(0.95); opacity: 0.8; }
        50% { transform: scale(1.35); opacity: 1; }
        100% { transform: scale(0.95); opacity: 0.8; }
    }

    @keyframes lineFlow {
        0% { background-position: 0% 50%; }
        100% { background-position: 200% 50%; }
    }

    .animated-fade-in {
        animation: fadeInUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }

    .delay-1 { animation-delay: 0.15s; }
    .delay-2 { animation-delay: 0.3s; }
    .delay-3 { animation-delay: 0.45s; }

    /* Full Width Hero Container */
    .hero-full-width {
        width: 100%;
        background: linear-gradient(135deg, #ffffff 0%, #f0f7ff 40%, #e0f2fe 100%);
        border-bottom: 1px solid #bfdbfe;
        box-shadow: 0 20px 40px -15px rgba(30, 64, 175, 0.07);
        position: relative;
        overflow: hidden;
        padding: 4.5rem 1.5rem 5rem 1.5rem;
        margin-bottom: 3.5rem;
    }

    #particles-js {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        z-index: 1;
        pointer-events: none;
    }

    .hero-content-wrapper {
        position: relative;
        z-index: 2;
        max-width: 1100px;
        margin: 0 auto;
    }

    /* Decorative background glowing shapes */
    .hero-glow-1 {
        position: absolute;
        width: 400px;
        height: 400px;
        top: -120px;
        left: -100px;
        background: radial-gradient(circle, rgba(59, 130, 246, 0.22) 0%, rgba(255, 255, 255, 0) 70%);
        animation: floatSlow 8s ease-in-out infinite;
        pointer-events: none;
        z-index: 1;
    }

    .hero-glow-2 {
        position: absolute;
        width: 450px;
        height: 450px;
        bottom: -150px;
        right: -100px;
        background: radial-gradient(circle, rgba(30, 58, 138, 0.15) 0%, rgba(255, 255, 255, 0) 70%);
        animation: floatReverse 10s ease-in-out infinite;
        pointer-events: none;
        z-index: 1;
    }

    .hero-top-border {
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 6px;
        background: linear-gradient(90deg, #1e3a8a, #2563eb, #60a5fa, #2563eb, #1e3a8a);
        background-size: 200% 100%;
        animation: lineFlow 4s linear infinite;
        z-index: 3;
    }

    .hero-badge-pill {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(8px);
        color: var(--bw-blue-primary);
        font-weight: 700;
        font-size: 0.9rem;
        padding: 0.55rem 1.35rem;
        border-radius: 50px;
        border: 1px solid #bfdbfe;
        box-shadow: 0 6px 18px rgba(37, 99, 235, 0.12);
        display: inline-flex;
        align-items: center;
        gap: 8px;
    }

    .live-indicator {
        width: 10px;
        height: 10px;
        background-color: var(--bw-blue-vibrant);
        border-radius: 50%;
        display: inline-block;
        animation: liveDotPulse 2s infinite ease-in-out;
    }

    .hero-heading {
        color: var(--bw-navy-dark);
        font-weight: 800;
        letter-spacing: -0.8px;
        line-height: 1.25;
        font-size: calc(1.6rem + 1.2vw);
    }

    .btn-hero-primary {
        background: linear-gradient(135deg, var(--bw-blue-primary) 0%, var(--bw-blue-vibrant) 100%);
        color: #ffffff !important;
        border: none;
        border-radius: 16px;
        padding: 1.05rem 2.5rem;
        font-weight: 700;
        font-size: 1.05rem;
        box-shadow: 0 12px 28px -6px rgba(37, 99, 235, 0.4);
        transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
        overflow: hidden;
    }

    .btn-hero-primary:hover {
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 18px 36px -6px rgba(37, 99, 235, 0.5);
        background: linear-gradient(135deg, var(--bw-blue-deep) 0%, var(--bw-blue-primary) 100%);
    }

    .btn-hero-primary i {
        transition: transform 0.3s ease;
    }

    .btn-hero-primary:hover i {
        transform: translateY(4px);
    }

    /* Continuum Step Line */
    .continuum-card-box {
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 24px;
        padding: 2rem;
        box-shadow: 0 15px 35px -10px rgba(15, 23, 42, 0.05);
        position: relative;
    }

    .step-item {
        background: #eff6ff;
        border: 1.5px solid #dbeafe;
        border-radius: 18px;
        padding: 1.5rem 1.25rem;
        transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        position: relative;
        z-index: 2;
    }

    .step-item:hover {
        background: #ffffff;
        border-color: var(--bw-blue-accent);
        transform: translateY(-6px);
        box-shadow: 0 14px 30px -8px rgba(37, 99, 235, 0.18);
    }

    .step-badge {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--bw-blue-primary), var(--bw-blue-vibrant));
        color: #ffffff;
        font-weight: 800;
        font-size: 1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 6px 14px rgba(37, 99, 235, 0.3);
        margin-bottom: 0.75rem;
        transition: transform 0.3s ease;
    }

    .step-item:hover .step-badge {
        transform: scale(1.15) rotate(360deg);
    }

    /* Category Cards Interactive Design */
    .innovative-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 24px;
        transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.03);
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .innovative-card::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 4px;
        background: transparent;
        transition: background 0.3s ease;
    }

    .innovative-card:hover {
        transform: translateY(-10px);
        border-color: #93c5fd;
        box-shadow: 0 25px 45px -12px rgba(37, 99, 235, 0.18);
    }

    .innovative-card:hover::before {
        background: linear-gradient(90deg, var(--bw-blue-primary), var(--bw-blue-accent));
    }

    .icon-box-glow {
        width: 76px;
        height: 76px;
        border-radius: 22px;
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        color: var(--bw-blue-primary);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem auto;
        border: 1px solid #bfdbfe;
        transition: all 0.35s cubic-bezier(0.16, 1, 0.3, 1);
    }

    .innovative-card:hover .icon-box-glow {
        background: linear-gradient(135deg, var(--bw-blue-primary) 0%, var(--bw-blue-vibrant) 100%);
        color: #ffffff;
        transform: scale(1.1) rotate(-4deg);
        box-shadow: 0 10px 25px rgba(37, 99, 235, 0.35);
    }

    .btn-action-glow {
        background: linear-gradient(135deg, var(--bw-blue-primary) 0%, var(--bw-blue-vibrant) 100%);
        color: #ffffff !important;
        border: none;
        border-radius: 14px;
        font-weight: 700;
        padding: 0.85rem 1.25rem;
        transition: all 0.3s ease;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }

    .btn-action-glow:hover {
        background: linear-gradient(135deg, var(--bw-blue-deep) 0%, var(--bw-blue-primary) 100%);
        box-shadow: 0 10px 22px -4px rgba(30, 64, 175, 0.45);
    }

    .btn-action-glow i {
        transition: transform 0.3s ease;
    }

    .btn-action-glow:hover i {
        transform: translateX(5px);
    }

    /* Purpose & Privacy Feature Cards */
    .glass-feature-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 24px;
        box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
        transition: all 0.35s ease;
        height: 100%;
    }

    .glass-feature-card:hover {
        border-color: #bfdbfe;
        box-shadow: 0 18px 36px -10px rgba(30, 64, 175, 0.1);
        transform: translateY(-4px);
    }

    .feature-icon-box {
        width: 48px;
        height: 48px;
        border-radius: 14px;
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        color: var(--bw-blue-primary);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        border: 1px solid #bfdbfe;
    }

    .animated-list-item {
        position: relative;
        padding-left: 2rem;
        margin-bottom: 0.9rem;
        color: #334155;
        font-size: 0.95rem;
        transition: transform 0.25s ease;
    }

    .animated-list-item:hover {
        transform: translateX(4px);
        color: var(--bw-blue-primary);
    }

    .animated-list-item::before {
        content: "\F26A"; /* Bootstrap Icon bi-check-circle-fill */
        font-family: "bootstrap-icons";
        position: absolute;
        left: 0;
        top: 2px;
        color: var(--bw-blue-vibrant);
        font-size: 1.15rem;
    }

    .security-badge {
        background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);
        border: 1px dashed #93c5fd;
        border-radius: 16px;
        padding: 1rem 1.25rem;
        color: var(--bw-blue-primary);
    }
</style>
@endpush

@section('content')
<!-- Full Width Hero Banner Section with Particles JS -->
<div class="hero-full-width text-center animated-fade-in">
    <div class="hero-top-border"></div>
    <div id="particles-js"></div>
    <div class="hero-glow-1"></div>
    <div class="hero-glow-2"></div>

    <div class="hero-content-wrapper">
        <div class="hero-badge-pill mb-4">
            <span class="live-indicator"></span>
            <i class="bi bi-award-fill me-1" style="color:var(--bw-blue-vibrant);"></i> Official Institutional Research Study
        </div>

        <h1 class="hero-heading mb-3">
            {{ $survey->title ?? 'National University Education–Employment Continuum Research Study' }}
        </h1>
        
        <p class="lead text-secondary mx-auto mb-4" style="max-width: 860px; font-size: 1.15rem; line-height: 1.75;">
            {{ $survey->description ?? 'Empirical study connecting academic learning, contemporary workplace practice, employability barriers, and interrupted education pathways.' }}
        </p>

        <div class="d-flex justify-content-center gap-3 flex-wrap">
            <a href="#categories" class="btn btn-hero-primary px-4">
                Select Your Category & Start Survey <i class="bi bi-arrow-down-circle-fill ms-2 fs-5 align-middle"></i>
            </a>
        </div>
    </div>
</div>

<div class="container pb-4">
    <!-- Education-Employment Continuum Explanation -->
    <div class="mb-5 text-center animated-fade-in delay-1">
        <h4 class="fw-bold text-dark mb-2" style="letter-spacing: -0.3px;">The Four-Category Research Continuum</h4>
        <p class="text-secondary mb-4">Connecting student preparation to career reality for data-driven curriculum evolution.</p>
        
        <div class="continuum-card-box">
            <div class="row g-3 align-items-center justify-content-center">
                <div class="col-6 col-md-3">
                    <div class="step-item text-center">
                        <div class="step-badge">1</div>
                        <h6 class="fw-bold mb-1" style="color:var(--bw-blue-deep);">Working Alumni</h6>
                        <small class="text-secondary fw-semibold">Practice Evidence</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="step-item text-center">
                        <div class="step-badge">2</div>
                        <h6 class="fw-bold mb-1" style="color:var(--bw-blue-deep);">Job-Seeking Alumni</h6>
                        <small class="text-secondary fw-semibold">Transition Evidence</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="step-item text-center">
                        <div class="step-badge">3</div>
                        <h6 class="fw-bold mb-1" style="color:var(--bw-blue-deep);">Current Students</h6>
                        <small class="text-secondary fw-semibold">Pre-Graduation Readiness</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="step-item text-center">
                        <div class="step-badge">4</div>
                        <h6 class="fw-bold mb-1" style="color:var(--bw-blue-deep);">Dropped-out Students</h6>
                        <small class="text-secondary fw-semibold">Re-engagement Pathways</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 4 Category Cards -->
    <div id="categories" class="row g-4 mb-5 animated-fade-in delay-2">
        @foreach($categories as $cat)
            <div class="col-md-6 col-lg-3">
                <div class="innovative-card h-100 p-4 text-center">
                    <div>
                        <div class="icon-box-glow">
                            <i class="bi {{ $cat->icon ?? 'bi-person-badge' }} fs-1"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-2">{{ $cat->name }}</h5>
                        <p class="text-secondary small mb-4" style="line-height: 1.6;">{{ $cat->description }}</p>
                    </div>
                    <a href="{{ route('survey.register', $cat->code) }}" class="btn btn-action-glow w-100">
                        Start The Survey <i class="bi bi-chevron-right ms-1"></i>
                    </a>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Research Purpose & Privacy -->
    <div class="row g-4 mb-4 animated-fade-in delay-3">
        <div class="col-md-6">
            <div class="glass-feature-card p-4 p-md-5">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="feature-icon-box">
                        <i class="bi bi-journal-check"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-0">Research Purpose</h5>
                </div>
                <ul class="list-unstyled mb-0">
                    <li class="animated-list-item">Evaluate academic knowledge relevance in contemporary workplaces.</li>
                    <li class="animated-list-item">Identify recruitment-stage bottlenecks for job-seeking graduates.</li>
                    <li class="animated-list-item">Assess student practical, AI, and digital competency pre-graduation.</li>
                    <li class="animated-list-item">Re-engage interrupted learners through skill mapping and credit transfer.</li>
                </ul>
            </div>
        </div>
        <div class="col-md-6">
            <div class="glass-feature-card p-4 p-md-5">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="feature-icon-box">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-0">Confidentiality & Privacy</h5>
                </div>
                <p class="text-secondary small mb-4" style="line-height: 1.75; font-size: 0.95rem;">
                    All responses are strictly used for institutional research, curriculum refinement, and state career support policies. Individual respondent data confidentiality is strictly guaranteed.
                </p>
                <div class="security-badge d-flex align-items-center gap-3">
                    <i class="bi bi-lock-fill fs-4" style="color:var(--bw-blue-vibrant);"></i>
                    <div>
                        <div class="fw-bold small text-dark">SSL Encrypted & Token Secured Submission</div>
                        <small class="text-secondary" style="font-size:0.78rem;">Your data privacy is fully protected under institutional protocols.</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<!-- Particles JS Library -->
<script src="https://cdn.jsdelivr.net/particles.js/2.0.0/particles.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        if (typeof particlesJS !== 'undefined') {
            particlesJS('particles-js', {
                "particles": {
                    "number": {
                        "value": 50,
                        "density": { "enable": true, "value_area": 800 }
                    },
                    "color": { "value": ["#2563eb", "#3b82f6", "#60a5fa", "#1e40af"] },
                    "shape": { "type": "circle" },
                    "opacity": {
                        "value": 0.35,
                        "random": true,
                        "anim": { "enable": true, "speed": 1, "opacity_min": 0.1, "sync": false }
                    },
                    "size": {
                        "value": 4.5,
                        "random": true,
                        "anim": { "enable": true, "speed": 2, "size_min": 1, "sync": false }
                    },
                    "line_linked": {
                        "enable": true,
                        "distance": 150,
                        "color": "#3b82f6",
                        "opacity": 0.22,
                        "width": 1.2
                    },
                    "move": {
                        "enable": true,
                        "speed": 1.5,
                        "direction": "none",
                        "random": true,
                        "straight": false,
                        "out_mode": "out",
                        "bounce": false
                    }
                },
                "interactivity": {
                    "detect_on": "canvas",
                    "events": {
                        "onhover": { "enable": true, "mode": "grab" },
                        "onclick": { "enable": true, "mode": "push" },
                        "resize": true
                    },
                    "modes": {
                        "grab": { "distance": 160, "line_linked": { "opacity": 0.45 } },
                        "push": { "particles_nb": 3 }
                    }
                },
                "retina_detect": true
            });
        }
    });
</script>
@endpush



