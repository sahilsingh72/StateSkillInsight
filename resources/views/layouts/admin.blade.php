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
    
    <style>
        :root {
            --uni-primary: {{ $university->primary_color ?? '#1e40af' }};
            --uni-secondary: {{ $university->secondary_color ?? '#0d9488' }};
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            color: #1e293b;
        }
        .sidebar {
            width: 260px;
            background-color: #ffffff;
            border-right: 1px solid #e2e8f0;
            height: 100vh;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 100;
            display: flex;
            flex-direction: column;
        }
        .sidebar-brand {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #f1f5f9;
            flex-shrink: 0;
        }
        .sidebar-menu {
            padding: 1rem 0;
            overflow-y: auto;
            flex: 1 1 auto;
        }
        .sidebar-menu::-webkit-scrollbar {
            width: 5px;
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
        }
        .sidebar-menu .nav-link {
            padding: 0.65rem 1.5rem;
            color: #475569;
            font-weight: 500;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            transition: all 0.2s ease;
        }
        .sidebar-menu .nav-link:hover, .sidebar-menu .nav-link.active {
            color: var(--uni-primary);
            background-color: #eff6ff;
            border-left: 3px solid var(--uni-primary);
        }
        .main-content {
            margin-left: 260px;
            padding: 2rem;
        }
        .topbar {
            background-color: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.75rem 2rem;
            margin-left: 260px;
            display: flex;
            justify-content: space-between;
            align-items: center;
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

    <!-- Sidebar -->
    <div class="sidebar d-flex flex-column">
        <div class="sidebar-brand d-flex align-items-center gap-2">
            <div class="rounded-circle p-2 text-white d-flex align-items-center justify-content-center" style="background: var(--uni-primary); width:38px; height:38px;">
                <i class="bi bi-bank fs-5"></i>
            </div>
            <div>
                <h6 class="fw-bold mb-0 text-dark">{{ $university->short_name ?? 'Uni' }} Research</h6>
                <small class="text-muted" style="font-size:0.75rem;">Intelligence Hub</small>
            </div>
        </div>

        <div class="sidebar-menu flex-grow-1 overflow-auto">
            <a href="{{ route('admin.dashboard') }}" class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="bi bi-grid"></i> Dashboard
            </a>

            <div class="nav-header">Research Surveys</div>
            <a href="{{ route('admin.surveys.index') }}" class="nav-link {{ request()->routeIs('admin.surveys.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-text"></i> All Surveys
            </a>
            <a href="{{ route('admin.categories.index') }}" class="nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}">
                <i class="bi bi-layers"></i> Categories
            </a>
            <a href="{{ route('admin.questions.index') }}" class="nav-link {{ request()->routeIs('admin.questions.*') ? 'active' : '' }}">
                <i class="bi bi-question-square"></i> Question Bank
            </a>

            <div class="nav-header">Respondents & Invitations</div>
            <a href="{{ route('admin.respondents.index') }}" class="nav-link {{ request()->routeIs('admin.respondents.*') ? 'active' : '' }}">
                <i class="bi bi-people"></i> Respondents
            </a>
            <a href="{{ route('admin.invitations.index') }}" class="nav-link {{ request()->routeIs('admin.invitations.*') ? 'active' : '' }}">
                <i class="bi bi-envelope-paper"></i> Invitations
            </a>
            <a href="{{ route('admin.responses.voice') }}" class="nav-link {{ request()->routeIs('admin.responses.voice') ? 'active' : '' }}">
                <i class="bi bi-mic"></i> Voice Recordings
            </a>

            <div class="nav-header">Analytics & Insights</div>
            <a href="{{ route('admin.analytics.overview') }}" class="nav-link {{ request()->routeIs('admin.analytics.overview') ? 'active' : '' }}">
                <i class="bi bi-pie-chart"></i> Overview Charts
            </a>
            <a href="{{ route('admin.analytics.category1') }}" class="nav-link {{ request()->routeIs('admin.analytics.category1') ? 'active' : '' }}">
                <i class="bi bi-briefcase"></i> Working Alumni
            </a>
            <a href="{{ route('admin.analytics.category2') }}" class="nav-link {{ request()->routeIs('admin.analytics.category2') ? 'active' : '' }}">
                <i class="bi bi-person-search"></i> Job-Seeking Alumni
            </a>
            <a href="{{ route('admin.analytics.category3') }}" class="nav-link {{ request()->routeIs('admin.analytics.category3') ? 'active' : '' }}">
                <i class="bi bi-mortarboard"></i> Current Students
            </a>
            <a href="{{ route('admin.analytics.category4') }}" class="nav-link {{ request()->routeIs('admin.analytics.category4') ? 'active' : '' }}">
                <i class="bi bi-arrow-counterclockwise"></i> Interrupted Students
            </a>
            <a href="{{ route('admin.analytics.cross_analysis') }}" class="nav-link {{ request()->routeIs('admin.analytics.cross_analysis') ? 'active' : '' }}">
                <i class="bi bi-funnel"></i> Cross Analysis
            </a>
            <a href="{{ route('admin.analytics.comparison') }}" class="nav-link {{ request()->routeIs('admin.analytics.comparison') ? 'active' : '' }}">
                <i class="bi bi-segmented-nav"></i> Category Comparison
            </a>
            <a href="{{ route('admin.analytics.interventions') }}" class="nav-link {{ request()->routeIs('admin.analytics.interventions') ? 'active' : '' }}">
                <i class="bi bi-lightbulb"></i> Interventions
            </a>

            <div class="nav-header">Reports & Exports</div>
            <a href="{{ route('admin.reports.index') }}" class="nav-link {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">
                <i class="bi bi-file-earmark-pdf"></i> Reports Hub
            </a>
            <a href="{{ route('admin.exports.csv') }}" class="nav-link">
                <i class="bi bi-download"></i> CSV Export
            </a>

            <div class="nav-header">Settings & Security</div>
            @if(auth()->check() && auth()->user()->isSuperAdmin())
                <a href="{{ route('admin.university.index') }}" class="nav-link {{ request()->routeIs('admin.university.index') ? 'active' : '' }}">
                    <i class="bi bi-diagram-3"></i> Institutions & Colleges
                </a>
            @endif
            <a href="{{ route('admin.university.edit') }}" class="nav-link {{ request()->routeIs('admin.university.edit') ? 'active' : '' }}">
                <i class="bi bi-building"></i> Institution Profile
            </a>
            <a href="{{ route('admin.users.index') }}" class="nav-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
                <i class="bi bi-shield-lock"></i> Users & Roles
            </a>
            <a href="{{ route('admin.audit_logs.index') }}" class="nav-link {{ request()->routeIs('admin.audit_logs.*') ? 'active' : '' }}">
                <i class="bi bi-clock-history"></i> Audit Logs
            </a>
        </div>
    </div>

    <!-- Topbar -->
    <div class="topbar">
        <div class="d-flex align-items-center gap-3">
            <span class="badge bg-light text-secondary border">
                <i class="bi bi-building me-1"></i> {{ auth()->user()->university->name ?? 'System-Wide Administration' }}
            </span>
        </div>
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('survey.landing') }}" target="_blank" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-box-arrow-up-right me-1"></i> Public Site
            </a>
            <div class="dropdown">
                <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle me-1"></i> {{ auth()->user()->name ?? 'User' }} ({{ strtoupper(auth()->user()->roleRelation->display_name ?? auth()->user()->roleRelation->name ?? 'Admin') }})
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li><a class="dropdown-item" href="{{ route('admin.university.edit') }}">Institution Profile</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">Logout</button>
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

        @yield('content')
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    @stack('scripts')
</body>
</html>
