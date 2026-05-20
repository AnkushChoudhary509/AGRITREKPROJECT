<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Agri-Trek — @yield('title', 'Smart Agriculture Monitoring')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <style>
        :root {
            --sidebar-w: 265px;
            --green-dark: #1b5e20;
            --green-mid:  #2e7d32;
            --green-light:#4caf50;
        }
        body { background:#f0f2f0; font-family:'Segoe UI',sans-serif; }

        /* ── Sidebar ─────────────────────────────── */
        #sidebar {
            width: var(--sidebar-w);
            background: linear-gradient(175deg, #0d3b12 0%, #1b5e20 45%, #2e7d32 100%);
            min-height: 100vh; position: fixed; top:0; left:0;
            z-index:200; transition:.3s; overflow-y:auto;
            display:flex; flex-direction:column;
        }
        .sidebar-brand {
            padding:18px 16px 16px;
            border-bottom:1px solid rgba(255,255,255,.12);
        }
        .sidebar-brand h4 { color:#fff; margin:0; font-weight:800; font-size:17px; letter-spacing:.3px; }
        .sidebar-brand small { color:rgba(255,255,255,.55); font-size:10.5px; }

        .nav-section {
            color:rgba(255,255,255,.4); font-size:9.5px; font-weight:700;
            text-transform:uppercase; letter-spacing:1.8px;
            padding:14px 20px 4px;
        }
        .nav-link-item {
            color:rgba(255,255,255,.78); padding:9px 14px;
            border-radius:9px; margin:1px 8px;
            display:flex; align-items:center; gap:10px;
            transition:.18s; font-size:13.5px; text-decoration:none;
        }
        .nav-link-item:hover { background:rgba(255,255,255,.15); color:#fff; }
        .nav-link-item.active { background:rgba(255,255,255,.22); color:#fff; font-weight:600; }
        .nav-link-item i { font-size:15px; width:18px; text-align:center; }
        .nav-badge {
            margin-left:auto; background:rgba(255,255,255,.2);
            color:#fff; font-size:10px; padding:2px 7px; border-radius:10px;
        }

        .sidebar-user {
            margin-top:auto; padding:14px 16px;
            border-top:1px solid rgba(255,255,255,.12);
        }
        .user-avatar {
            width:38px; height:38px; border-radius:50%;
            background:rgba(255,255,255,.2); border:2px solid rgba(255,255,255,.3);
            display:flex; align-items:center; justify-content:center;
            font-weight:700; font-size:15px; color:#fff; flex-shrink:0;
        }

        /* ── Main ────────────────────────────────── */
        #main-content { margin-left:var(--sidebar-w); min-height:100vh; }
        .topbar {
            background:#fff; border-bottom:1px solid #e8e8e8;
            padding:10px 24px; position:sticky; top:0; z-index:99;
            display:flex; align-items:center; justify-content:space-between;
        }
        .page-content { padding:22px 24px; }

        /* ── Cards ───────────────────────────────── */
        .stat-card {
            border:none; border-radius:14px; padding:22px;
            color:#fff; position:relative; overflow:hidden;
            box-shadow:0 4px 16px rgba(0,0,0,.12);
        }
        .stat-card .stat-icon {
            position:absolute; right:14px; top:14px;
            font-size:42px; opacity:.25;
        }
        .stat-card h3 { font-size:34px; font-weight:800; margin:0; }
        .stat-card p  { margin:4px 0 0; opacity:.85; font-size:12.5px; }

        .card { border:none; border-radius:12px; box-shadow:0 2px 10px rgba(0,0,0,.07); }
        .card-header { border-radius:12px 12px 0 0 !important; font-weight:600; font-size:14px; }

        .table th { font-size:11px; text-transform:uppercase; letter-spacing:.5px; color:#777; font-weight:600; }
        .badge { border-radius:6px; }

        /* ── Role badge colors ───────────────────── */
        .role-admin  { background:#dc3545!important; }
        .role-expert { background:#0d6efd!important; }
        .role-farmer { background:#198754!important; }

        /* ── Alerts ──────────────────────────────── */
        .alert { border-radius:10px; }

        @media(max-width:768px){
            #sidebar { transform:translateX(-100%); }
            #sidebar.show { transform:translateX(0); }
            #main-content { margin-left:0; }
        }
    </style>
    @stack('styles')
</head>
<body>

@php
    $user = auth()->user();
    $isExpert = $user->isExpert();
@endphp

<!-- ══ Sidebar ══════════════════════════════════════════════════════ -->
<nav id="sidebar">
    <div class="sidebar-brand">
        <h4><i class="bi bi-airplane-engines-fill me-2"></i>Agri-Trek</h4>
        <small>Precision Agriculture System</small>
    </div>

    <ul class="nav flex-column pt-1 flex-grow-1">

        <!-- Main -->
        <li class="nav-section">Main</li>
        <li class="nav-item">
            <a class="nav-link-item {{ request()->routeIs('dashboard') ? 'active' : '' }}"
               href="{{ route('dashboard') }}">
                <i class="bi bi-speedometer2"></i> Dashboard
            </a>
        </li>

        @if($isExpert)
        <!-- Farmer Management -->
        <li class="nav-section">Farmer Management</li>
        <li class="nav-item">
            <a class="nav-link-item {{ request()->routeIs('farmers.*') ? 'active' : '' }}"
               href="{{ route('farmers.index') }}">
                <i class="bi bi-people-fill"></i> Farmers
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link-item {{ request()->routeIs('lands.*') ? 'active' : '' }}"
               href="{{ route('lands.index') }}">
                <i class="bi bi-map-fill"></i> Land Records
            </a>
        </li>
        @endif

        <!-- Schemes -->
        <li class="nav-section">Schemes</li>
        <li class="nav-item">
            <a class="nav-link-item {{ request()->routeIs('schemes.index','schemes.show') ? 'active' : '' }}"
               href="{{ route('schemes.index') }}">
                <i class="bi bi-award-fill"></i> Beneficiary Schemes
            </a>
        </li>
        @if($isExpert)
        <li class="nav-item">
            <a class="nav-link-item {{ request()->routeIs('schemes.applications') ? 'active' : '' }}"
               href="{{ route('schemes.applications') }}">
                <i class="bi bi-file-earmark-check-fill"></i> Applications
            </a>
        </li>
        @endif

        @if($isExpert)
        <!-- Drone Operations -->
        <li class="nav-section">Drone Operations</li>
        <li class="nav-item">
            <a class="nav-link-item {{ request()->routeIs('drones.*') ? 'active' : '' }}"
               href="{{ route('drones.index') }}">
                <i class="bi bi-airplane-engines-fill"></i> Drone Monitoring
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link-item {{ request()->routeIs('waypoints.*') ? 'active' : '' }}"
               href="{{ route('waypoints.index') }}">
                <i class="bi bi-pin-map-fill"></i> Waypoints
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link-item {{ request()->routeIs('clustering.*') ? 'active' : '' }}"
               href="{{ route('clustering.index') }}">
                <i class="bi bi-diagram-3-fill"></i> Clustering
            </a>
        </li>

        <!-- AI Modules -->
        <li class="nav-section">AI Modules</li>
        <li class="nav-item">
            <a class="nav-link-item {{ request()->routeIs('vision.*') ? 'active' : '' }}"
               href="{{ route('vision.index') }}">
                <i class="bi bi-camera-fill"></i> Computer Vision
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link-item {{ request()->routeIs('sensors.*') ? 'active' : '' }}"
               href="{{ route('sensors.index') }}">
                <i class="bi bi-activity"></i> Sensor Fusion
            </a>
        </li>
        @endif

    </ul>

    <!-- User Footer -->
    <div class="sidebar-user">
        <div class="d-flex align-items-center gap-2 mb-2">
            <div class="user-avatar">{{ strtoupper(substr($user->name,0,1)) }}</div>
            <div style="min-width:0;">
                <div style="color:#fff;font-size:13px;font-weight:600;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                    {{ $user->name }}
                </div>
                <span class="badge badge role-{{ $user->role }}" style="font-size:9.5px;">
                    {{ $user->role_label }}
                </span>
            </div>
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-sm btn-outline-light w-100" style="font-size:12px;">
                <i class="bi bi-box-arrow-left me-1"></i>Logout
            </button>
        </form>
    </div>
</nav>

<!-- ══ Main Content ══════════════════════════════════════════════════ -->
<div id="main-content">
    <!-- Topbar -->
    <div class="topbar">
        <div class="d-flex align-items-center gap-3">
            <button class="btn btn-sm btn-light d-md-none" id="sidebarToggle">
                <i class="bi bi-list fs-5"></i>
            </button>
            <div>
                <h6 class="mb-0 fw-semibold text-dark">@yield('page-title', 'Dashboard')</h6>
                <small class="text-muted" style="font-size:11px;">{{ now()->format('l, d M Y') }}</small>
            </div>
        </div>
        <div class="d-flex align-items-center gap-3">
            <span class="badge bg-success d-flex align-items-center gap-1">
                <i class="bi bi-circle-fill" style="font-size:7px;"></i> System Online
            </span>
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center"
                     style="width:32px;height:32px;font-size:13px;font-weight:700;">
                    {{ strtoupper(substr($user->name,0,1)) }}
                </div>
                <div class="d-none d-md-block">
                    <div style="font-size:13px;font-weight:600;line-height:1.2;">{{ $user->name }}</div>
                    <div style="font-size:11px;color:#888;">{{ $user->role_label }}</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    <div class="page-content pb-0">
        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center gap-2 py-2 mb-3">
            <i class="bi bi-check-circle-fill"></i>
            <span>{{ session('success') }}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center gap-2 py-2 mb-3">
            <i class="bi bi-exclamation-circle-fill"></i>
            <span>{{ session('error') }}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
        @endif
        @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show d-flex align-items-center gap-2 py-2 mb-3">
            <i class="bi bi-exclamation-triangle-fill"></i>
            <span>{{ session('warning') }}</span>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
        @endif
    </div>

    <div class="page-content pt-2">
        @yield('content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    document.getElementById('sidebarToggle')?.addEventListener('click', () => {
        document.getElementById('sidebar').classList.toggle('show');
    });
    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', e => {
        const sb = document.getElementById('sidebar');
        if(window.innerWidth < 768 && !sb.contains(e.target) &&
           !e.target.closest('#sidebarToggle')) {
            sb.classList.remove('show');
        }
    });
</script>
@stack('scripts')
</body>
</html>
