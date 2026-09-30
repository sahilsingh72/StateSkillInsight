<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Dashboard') - {{ $university->name ?? config('app.name') }}</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.2/font/bootstrap-icons.min.css">
    
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        (function() {
            if (window.innerWidth >= 992 && localStorage.getItem('sidebar_minimized') === 'true') {
                document.documentElement.classList.add('sidebar-minimized-preload');
            }
        })();
    </script>

    <style>
        :root {
            --uni-primary: {{ $university->primary_color ?? '#1e40af' }};
            --uni-secondary: {{ $university->secondary_color ?? '#0d9488' }};
            --sidebar-width: 260px;
            --sidebar-mini-width: 72px;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
            overflow-x: hidden;
        }

        /* Sidebar Styles */
        .sidebar {
            width: var(--sidebar-width);
            background-color: #ffffff;
            border-right: 1px solid #e2e8f0;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1040;
            display: flex;
            flex-direction: column;
            transition: width 0.25s cubic-bezier(0.4, 0, 0.2, 1), transform 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            overflow: hidden;
        }
        .sidebar-brand {
            padding: 1.15rem 1.25rem;
            border-bottom: 1px solid #f1f5f9;
            flex-shrink: 0;
            height: 70px;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            white-space: nowrap;
            transition: padding 0.25s ease;
        }
        .sidebar-menu {
            padding: 0.75rem 0;
            overflow-y: auto;
            overflow-x: hidden;
            flex: 1 1 auto;
        }
        .sidebar-menu::-webkit-scrollbar {
            width: 4px;
        }
        .sidebar-menu::-webkit-scrollbar-track {
            background: transparent;
        }
        .sidebar-menu::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 10px;
        }
        .sidebar-menu::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        .nav-header {
            padding: 0.75rem 1.5rem 0.25rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            color: #94a3b8;
            letter-spacing: 0.05em;
            white-space: nowrap;
            transition: all 0.2s ease;
        }
        .sidebar-menu .nav-link {
            padding: 0.65rem 1.25rem;
            color: #475569;
            font-weight: 500;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.2s ease;
            white-space: nowrap;
        }
        .sidebar-menu .nav-link i {
            font-size: 1.15rem;
            min-width: 24px;
            text-align: center;
            flex-shrink: 0;
        }
        .sidebar-menu .nav-link:hover, .sidebar-menu .nav-link.active {
            color: var(--uni-primary);
            background-color: #eff6ff;
            border-left: 3px solid var(--uni-primary);
        }

        /* Layout Main Content & Topbar */
        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            transition: margin-left 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            min-height: calc(100vh - 70px);
        }
        .topbar {
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.75rem 2rem;
            margin-left: var(--sidebar-width);
            display: flex;
            justify-content: space-between;
            align-items: center;
            height: 70px;
            position: sticky;
            top: 0;
            z-index: 1020;
            transition: margin-left 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Minimized Sidebar State (Desktop) */
        body.sidebar-minimized .sidebar,
        html.sidebar-minimized-preload body .sidebar {
            width: var(--sidebar-mini-width);
        }
        body.sidebar-minimized .sidebar-brand,
        html.sidebar-minimized-preload body .sidebar-brand {
            padding: 1.15rem 0.85rem;
            justify-content: center;
        }
        body.sidebar-minimized .sidebar-brand .brand-details,
        html.sidebar-minimized-preload body .sidebar-brand .brand-details {
            display: none !important;
        }
        body.sidebar-minimized .nav-header,
        html.sidebar-minimized-preload body .nav-header {
            padding: 0.5rem 0.25rem;
            text-align: center;
            font-size: 0.55rem;
            opacity: 0.6;
        }
        body.sidebar-minimized .sidebar-menu .nav-link,
        html.sidebar-minimized-preload body .sidebar-menu .nav-link {
            padding: 0.75rem 0;
            justify-content: center;
            gap: 0;
        }
        body.sidebar-minimized .sidebar-menu .nav-link span,
        html.sidebar-minimized-preload body .sidebar-menu .nav-link span {
            display: none !important;
        }
        body.sidebar-minimized .sidebar-menu .nav-link i,
        html.sidebar-minimized-preload body .sidebar-menu .nav-link i {
            font-size: 1.25rem;
            min-width: unset;
        }
        body.sidebar-minimized .topbar,
        body.sidebar-minimized .main-content,
        html.sidebar-minimized-preload body .topbar,
        html.sidebar-minimized-preload body .main-content {
            margin-left: var(--sidebar-mini-width);
        }

        /* Sidebar Toggle Button */
        .btn-sidebar-toggle {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            color: #475569;
            width: 36px;
            height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            transition: all 0.2s ease;
        }
        .btn-sidebar-toggle:hover {
            background-color: #e2e8f0;
            color: var(--uni-primary);
        }

        /* Mobile & Tablet Responsiveness */
        @media (max-width: 991.98px) {
            .sidebar {
                transform: translateX(-100%);
                width: var(--sidebar-width) !important;
            }
            body.sidebar-mobile-open .sidebar {
                transform: translateX(0);
                box-shadow: 0 10px 25px rgba(0,0,0,0.15);
            }
            .topbar, .main-content {
                margin-left: 0 !important;
                padding-left: 1rem;
                padding-right: 1rem;
            }
            .sidebar-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(15, 23, 42, 0.45);
                backdrop-filter: blur(2px);
                z-index: 1035;
            }
            body.sidebar-mobile-open .sidebar-overlay {
                display: block;
            }
        }

        .card-custom {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .btn-primary-custom {
            background-color: var(--uni-primary);
            border-color: var(--uni-primary);
            color: #ffffff;
        }
        .btn-primary-custom:hover {
            background-color: #1d4ed8;
            color: #ffffff;
        }
        .badge-primary-custom {
            background-color: #dbeafe;
            color: var(--uni-primary);
        }
        /* Pagination Styling & SVG Sizing Fix */
        .pagination {
            margin-bottom: 0;
            gap: 2px;
        }
        .pagination svg {
            width: 1rem !important;
            height: 1rem !important;
            max-width: 16px !important;
            max-height: 16px !important;
            vertical-align: middle;
        }
        .page-link {
            border-radius: 6px;
            color: #475569;
            padding: 0.4rem 0.75rem;
            font-size: 0.875rem;
        }
        .page-item.active .page-link {
            background-color: var(--uni-primary);
            border-color: var(--uni-primary);
            color: #ffffff;
        }
    </style>
    @stack('styles')
</head>
<body>

    <!-- Mobile Backdrop Overlay -->
    <div id="sidebarOverlay" class="sidebar-overlay"></div>

    <!-- Sidebar -->
    <div class="sidebar d-flex flex-column">
        <div class="sidebar-brand d-flex align-items-center">
            <div class="rounded-circle p-2 text-white d-flex align-items-center justify-content-center flex-shrink-0" style="background: var(--uni-primary); width:38px; height:38px;">
                <i class="bi bi-bank fs-5"></i>
            </div>
            <div class="brand-details overflow-hidden">
                <h6 class="fw-bold mb-0 text-dark text-truncate">{{ $university->short_name ?? 'Uni' }} Research</h6>
                <small class="text-muted text-truncate d-block" style="font-size:0.75rem;">Intelligence Hub</small>
            </div>
        </div>

        <div class="sidebar-menu flex-grow-1 overflow-auto">
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" title="Dashboard">
                <i class="bi bi-grid"></i> <span>Dashboard</span>
            </a>

            <div class="nav-header">Surveys</div>
            <a href="{{ route('admin.surveys.index') }}" class="nav-link {{ request()->routeIs('admin.surveys.*') ? 'active' : '' }}" title="All Surveys">
                <i class="bi bi-file-earmark-text"></i> <span>All Surveys</span>
            </a>
            <a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}" title="Categories">
                <i class="bi bi-layers"></i> <span>Categories</span>
            </a>
            <a href="{{ route('admin.sections.index') }}" class="nav-link {{ request()->routeIs('admin.sections.*') ? 'active' : '' }}" title="Sections">
                <i class="bi bi-folder2-open"></i> <span>Sections</span>
            </a>
            <a href="{{ route('admin.questions.index') }}" class="nav-link {{ request()->routeIs('admin.questions.*') ? 'active' : '' }}" title="Question Bank">
                <i class="bi bi-question-square"></i> <span>Question Bank</span>
            </a>

            <div class="nav-header">Respondents</div>
            <a href="{{ route('admin.respondents.index') }}" class="nav-link {{ request()->routeIs('admin.respondents.*') ? 'active' : '' }}" title="Respondents">
                <i class="bi bi-people"></i> <span>Respondents</span>
            </a>
            <a href="{{ route('admin.invitations.index') }}" class="nav-link {{ request()->routeIs('admin.invitations.*') ? 'active' : '' }}" title="Invitations">
                <i class="bi bi-envelope-paper"></i> <span>Invitations</span>
            </a>
            <a href="{{ route('admin.responses.voice') }}" class="nav-link {{ request()->routeIs('admin.responses.voice') ? 'active' : '' }}" title="Voice Recordings">
                <i class="bi bi-mic"></i> <span>Voice Recordings</span>
            </a>

            <div class="nav-header">Analytics</div>
            <a href="{{ route('admin.analytics.overview') }}" class="nav-link {{ request()->routeIs('admin.analytics.overview') ? 'active' : '' }}" title="Overview Charts">
                <i class="bi bi-pie-chart"></i> <span>Overview Charts</span>
            </a>
            <a href="{{ route('admin.analytics.category1') }}" class="nav-link {{ request()->routeIs('admin.analytics.category1') ? 'active' : '' }}" title="Working Alumni">
                <i class="bi bi-briefcase"></i> <span>Working Alumni</span>
            </a>
            <a href="{{ route('admin.analytics.category2') }}" class="nav-link {{ request()->routeIs('admin.analytics.category2') ? 'active' : '' }}" title="Job-Seeking Alumni">
                <i class="bi bi-person-vcard"></i> <span>Job-Seeking Alumni</span>
            </a>
            <a href="{{ route('admin.analytics.category3') }}" class="nav-link {{ request()->routeIs('admin.analytics.category3') ? 'active' : '' }}" title="Current Students">
                <i class="bi bi-mortarboard"></i> <span>Current Students</span>
            </a>
            <a href="{{ route('admin.analytics.category4') }}" class="nav-link {{ request()->routeIs('admin.analytics.category4') ? 'active' : '' }}" title="Interrupted Students">
                <i class="bi bi-arrow-counterclockwise"></i> <span>Interrupted Students</span>
            </a>
            <a href="{{ route('admin.analytics.cross_analysis') }}" class="nav-link {{ request()->routeIs('admin.analytics.cross_analysis') ? 'active' : '' }}" title="Cross Analysis">
                <i class="bi bi-funnel"></i> <span>Cross Analysis</span>
            </a>
            <a href="{{ route('admin.analytics.comparison') }}" class="nav-link {{ request()->routeIs('admin.analytics.comparison') ? 'active' : '' }}" title="Category Comparison">
                <i class="bi bi-segmented-nav"></i> <span>Category Comparison</span>
            </a>
            <a href="{{ route('admin.analytics.interventions') }}" class="nav-link {{ request()->routeIs('admin.analytics.interventions') ? 'active' : '' }}" title="Interventions">
                <i class="bi bi-lightbulb"></i> <span>Interventions</span>
            </a>

            <div class="nav-header">Reports</div>
            <a href="{{ route('admin.reports.index') }}" class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}" title="Reports Hub">
                <i class="bi bi-file-earmark-pdf"></i> <span>Reports Hub</span>
            </a>
            <a href="{{ route('admin.exports.csv') }}" class="nav-link" title="CSV Export">
                <i class="bi bi-download"></i> <span>CSV Export</span>
            </a>

            <div class="nav-header">Settings</div>
            @if(auth()->check() && auth()->user()->isSuperAdmin())
                <a href="{{ route('admin.university.index') }}" class="nav-link {{ request()->routeIs('admin.university.index') ? 'active' : '' }}" title="Institutions & Colleges">
                    <i class="bi bi-diagram-3"></i> <span>Institutions & Colleges</span>
                </a>
            @endif
            @if(auth()->check() && auth()->user()->hasRole('university_admin'))
                <a href="{{ route('admin.university.edit') }}" class="nav-link {{ request()->routeIs('admin.university.edit') ? 'active' : '' }}" title="University Profile & Branding">
                    <i class="bi bi-building"></i> <span>University Profile</span>
                </a>
            @endif
            @if(auth()->check() && auth()->user()->isUniversityAdmin())
                <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" title="Users & Roles">
                    <i class="bi bi-shield-lock"></i> <span>Users & Roles</span>
                </a>
            @endif
            @if(auth()->check() && auth()->user()->isSuperAdmin())
                <a href="{{ route('admin.audit_logs.index') }}" class="nav-link {{ request()->routeIs('admin.audit_logs.*') ? 'active' : '' }}" title="Audit Logs">
                    <i class="bi bi-clock-history"></i> <span>Audit Logs</span>
                </a>
            @endif
        </div>
    </div>

    <!-- Topbar -->
    <div class="topbar">
        <div class="d-flex align-items-center gap-2">
            <!-- Sidebar Toggle Button -->
            <button id="sidebarToggle" type="button" class="btn-sidebar-toggle me-2" title="Toggle Sidebar (Minimise / Expand)" aria-label="Toggle Sidebar">
                <i class="bi bi-list fs-5"></i>
            </button>

            @if(auth()->check() && auth()->user()->isSuperAdmin())
                <span class="badge bg-primary text-white border shadow-2xs py-2 px-3 d-none d-sm-inline-flex align-items-center">
                    <i class="bi bi-shield-lock me-1"></i> Global System Administrator (Software Apex Control)
                </span>
                <span class="badge bg-primary text-white border shadow-2xs py-2 px-2 d-inline-flex d-sm-none align-items-center">
                    <i class="bi bi-shield-lock"></i> Super Admin
                </span>
            @else
                <span class="badge bg-light text-secondary border py-2 px-3 d-none d-sm-inline-flex align-items-center">
                    <i class="bi bi-building me-1"></i> {{ auth()->user()->university->name ?? 'Institutional Portal' }}
                </span>
                <span class="badge bg-light text-secondary border py-2 px-2 d-inline-flex d-sm-none align-items-center">
                    <i class="bi bi-building me-1"></i> {{ auth()->user()->university->short_name ?? 'Portal' }}
                </span>
            @endif
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('survey.landing') }}" target="_blank" class="btn btn-sm btn-outline-secondary d-none d-md-inline-flex align-items-center">
                <i class="bi bi-box-arrow-up-right me-1"></i> Public Site
            </a>
            <div class="dropdown">
                <button class="btn btn-sm btn-light border dropdown-toggle d-flex align-items-center gap-1" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle"></i>
                    <span class="d-none d-sm-inline">{{ auth()->user()->name ?? 'User' }} ({{ strtoupper(auth()->user()->roleRelation->display_name ?? auth()->user()->roleRelation->name ?? 'Admin') }})</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                    @if(auth()->check() && auth()->user()->isSuperAdmin())
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.university.index') }}">
                                <i class="bi bi-diagram-3 me-2 text-primary"></i> Institutions Directory & Enrollment
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                    @elseif(auth()->check() && auth()->user()->hasRole('university_admin'))
                        <li>
                            <a class="dropdown-item" href="{{ route('admin.university.edit') }}">
                                <i class="bi bi-building me-2 text-primary"></i> University Profile & Branding
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                    @endif
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="bi bi-box-arrow-right me-2"></i> Logout
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="main-content">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('import_errors'))
            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
                <h6 class="fw-bold mb-2"><i class="bi bi-exclamation-octagon-fill me-2"></i> Import Issues & Row Validation Errors:</h6>
                <ul class="mb-0 ps-3 small">
                    @foreach(session('import_errors') as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <!-- Bootstrap & Global Layout Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function() {
            const toggleBtn = document.getElementById('sidebarToggle');
            const overlay = document.getElementById('sidebarOverlay');

            function isMobile() {
                return window.innerWidth < 992;
            }

            // Apply saved desktop minimization state
            if (!isMobile() && localStorage.getItem('sidebar_minimized') === 'true') {
                document.body.classList.add('sidebar-minimized');
            }

            if (toggleBtn) {
                toggleBtn.addEventListener('click', function() {
                    if (isMobile()) {
                        document.body.classList.toggle('sidebar-mobile-open');
                    } else {
                        document.body.classList.toggle('sidebar-minimized');
                        localStorage.setItem('sidebar_minimized', document.body.classList.contains('sidebar-minimized'));
                        
                        // Trigger window resize event so responsive charts adapt instantly
                        window.dispatchEvent(new Event('resize'));
                    }
                });
            }

            if (overlay) {
                overlay.addEventListener('click', function() {
                    document.body.classList.remove('sidebar-mobile-open');
                });
            }

            // Close mobile sidebar when clicking on a link
            document.querySelectorAll('.sidebar-menu .nav-link').forEach(function(link) {
                link.addEventListener('click', function() {
                    if (isMobile()) {
                        document.body.classList.remove('sidebar-mobile-open');
                    }
                });
            });
        })();
    </script>
    @stack('scripts')
</body>
</html>
