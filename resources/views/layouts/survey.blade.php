<!DOCTYPE html>
<html lang="{{ session('survey_locale', 'en') }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', $university->survey_header ?? 'Research Survey') - {{ $university->name ?? 'University' }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --uni-primary: {{ $university->primary_color ?? '#1e40af' }};
            --uni-secondary: {{ $university->secondary_color ?? '#0d9488' }};
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }
        .header-navbar {
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 0;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        }
        .survey-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            padding: 2rem;
            margin-bottom: 1.5rem;
        }
        .btn-uni-primary {
            background-color: var(--uni-primary);
            border-color: var(--uni-primary);
            color: #ffffff;
            font-weight: 600;
            border-radius: 10px;
            padding: 0.65rem 1.5rem;
            transition: all 0.2s ease;
        }
        .btn-uni-primary:hover {
            background-color: #1d4ed8;
            color: #ffffff;
            transform: translateY(-1px);
        }
        .btn-uni-outline {
            border: 2px solid var(--uni-primary);
            color: var(--uni-primary);
            font-weight: 600;
            border-radius: 10px;
        }
        .btn-uni-outline:hover {
            background-color: var(--uni-primary);
            color: #ffffff;
        }
        .footer {
            margin-top: auto;
            background: #ffffff;
            border-top: 1px solid #e2e8f0;
            padding: 1.5rem 0;
            color: #64748b;
            font-size: 0.875rem;
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Header Navbar -->
    <header class="header-navbar">
        <div class="container d-flex align-items-center justify-content-between">
            <a href="{{ route('survey.landing') }}" class="d-flex align-items-center gap-3 text-decoration-none">
                <div class="rounded-circle p-2 text-white d-flex align-items-center justify-content-center" style="background: var(--uni-primary); width:44px; height:44px;">
                    <i class="bi bi-bank fs-4"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0 text-dark">{{ isset($university) ? $university->name : 'State Skill & Higher Education Portal' }}</h5>
                    <small class="text-secondary" style="font-size:0.8rem;">{{ isset($university) ? ($university->tagline ?? 'Institutional Research & Planning Study') : 'National Institutional Research & Employability Continuum Platform' }}</small>
                </div>
            </a>

            <div class="d-flex align-items-center gap-2">
                @auth
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-uni-primary">
                        <i class="bi bi-speedometer2 me-1"></i> Admin Dashboard
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-sm btn-outline-primary">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Admin Login
                    </a>
                @endauth

                <!-- Language Switcher -->
                <div class="dropdown">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-translate me-1"></i> Language: {{ strtoupper(session('survey_locale', 'en')) }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ route('survey.locale', 'en') }}">English</a></li>
                        <li><a class="dropdown-item" href="{{ route('survey.locale', 'hi') }}">हिंदी (Hindi)</a></li>
                        <li><a class="dropdown-item" href="{{ route('survey.locale', 'or') }}">ଓଡ଼ିଆ (Odia)</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Container -->
    <main class="py-4">
        @yield('content')
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container text-center">
            <p class="mb-1">{{ isset($university) ? ($university->footer_text ?? '© 2026 Institutional Research Platform.') : '© 2026 Higher Education & Employability Continuum Research Portal.' }}</p>
            <small class="text-muted">{{ isset($university) ? ($university->address ?? '') : 'State Department of Higher Education & Skill Development' }}</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
