<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Official Login - State Skill & Higher Education Portal</title>

    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --gov-navy: #0f2942;
            --gov-blue: #1e40af;
            --gov-bg: #f8fafc;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--gov-bg);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* Top Government Bar with Subtle Tricolor Accent */
        .gov-top-bar {
            background-color: var(--gov-navy);
            color: #ffffff;
            font-size: 0.8rem;
            padding: 0.4rem 0;
            border-top: 3px solid;
            border-image: linear-gradient(to right, #ff9933 33%, #ffffff 33%, #ffffff 66%, #128807 66%) 1;
        }

        /* Header Branding */
        .gov-header {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 1rem 0;
        }

        .emblem-icon {
            width: 44px;
            height: 44px;
            background: var(--gov-navy);
            color: #ffffff;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Centered Simple Card */
        .simple-login-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
            padding: 2.5rem;
            max-width: 440px;
            width: 100%;
            margin: 0 auto;
        }

        .btn-gov-primary {
            background-color: var(--gov-blue);
            border-color: var(--gov-blue);
            color: #ffffff;
            font-weight: 600;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            transition: all 0.2s ease;
        }

        .btn-gov-primary:hover {
            background-color: #1d4ed8;
            border-color: #1d4ed8;
            color: #ffffff;
        }

        .input-group-text {
            background-color: #f8fafc;
            border-right: none;
            color: #64748b;
        }

        .input-with-icon {
            border-left: none;
        }

        .footer-gov {
            margin-top: auto;
            background: #ffffff;
            border-top: 1px solid #e2e8f0;
            padding: 1.25rem 0;
            font-size: 0.8rem;
            color: #64748b;
        }
    </style>
</head>
<body>

    <!-- Top Government Notification Bar -->
    <div class="gov-top-bar">
        <div class="container d-flex justify-content-between align-items-center">
            <div>
                <i class="bi bi-shield-check me-1 text-warning"></i> Official Portal | State Higher Education & Skill Research System
            </div>
            <div>
                <span><i class="bi bi-lock-fill me-1 text-success"></i> 256-Bit SSL Encrypted SSO</span>
            </div>
        </div>
    </div>

    <!-- Header -->
    <header class="gov-header">
        <div class="container d-flex align-items-center justify-content-between">
            <a href="{{ route('survey.landing') }}" class="d-flex align-items-center gap-3 text-decoration-none">
                <div class="emblem-icon">
                    <i class="bi bi-bank fs-4"></i>
                </div>
                <div>
                    <h5 class="fw-bold text-dark mb-0">State Skill & Higher Education Portal</h5>
                    <small class="text-secondary">Institutional Research & Planning Portal</small>
                </div>
            </a>
            <a href="{{ route('survey.landing') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-house me-1"></i> Public Site
            </a>
        </div>
    </header>

    <!-- Main Content Area: Simple Centered Form -->
    <main class="flex-grow-1 d-flex align-items-center py-5">
        <div class="container">
            <div class="simple-login-card">
                
                <div class="text-center mb-4">
                    <div class="rounded-circle p-3 text-white d-inline-flex align-items-center justify-content-center mb-3" style="background: var(--gov-navy); width: 56px; height: 56px;">
                        <i class="bi bi-person-lock fs-3"></i>
                    </div>
                    <h4 class="fw-bold text-dark mb-1">Sign In</h4>
                    <p class="text-secondary small mb-0">Enter your credentials to access the portal</p>
                </div>

                <!-- Session Alerts -->
                @if (session('warning'))
                    <div class="alert alert-warning alert-dismissible fade show rounded-3 mb-3 small" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ session('warning') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-3 small" role="alert">
                        <i class="bi bi-x-circle-fill me-1"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('status'))
                    <div class="alert alert-info alert-dismissible fade show rounded-3 mb-3 small" role="alert">
                        <i class="bi bi-info-circle me-1"></i> {{ session('status') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-3 small" role="alert">
                        <i class="bi bi-exclamation-triangle me-1"></i> {{ $errors->first() }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email Field -->
                    <div class="mb-3">
                        <label for="email" class="form-label fw-semibold text-dark small">Email Address</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                            <input type="email" id="email" name="email" class="form-control input-with-icon" value="{{ old('email') }}" placeholder="name@domain.com" required autofocus autocomplete="username">
                        </div>
                    </div>

                    <!-- Password Field -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="password" class="form-label fw-semibold text-dark small mb-0">Password</label>
                            @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="text-primary text-decoration-none small">Forgot password?</a>
                            @endif
                        </div>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock"></i></span>
                            <input type="password" id="password" name="password" class="form-control input-with-icon" placeholder="••••••••" required autocomplete="current-password">
                            <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                <i class="bi bi-eye" id="toggleIcon"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Remember Me Checkbox -->
                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember_me">
                        <label class="form-check-label text-secondary small" for="remember_me">
                            Remember me on this device
                        </label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-gov-primary w-100">
                        Sign In <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </form>

            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer-gov">
        <div class="container text-center">
            <p class="mb-1">© 2026 State Higher Education & Skill Development Research Platform.</p>
            <small class="text-muted">Authorized Personnel Portal</small>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Password visibility toggle
        document.getElementById('togglePassword').addEventListener('click', function() {
            const pwdInput = document.getElementById('password');
            const icon = document.getElementById('toggleIcon');
            if (pwdInput.type === 'password') {
                pwdInput.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                pwdInput.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        });
    </script>
</body>
</html>
