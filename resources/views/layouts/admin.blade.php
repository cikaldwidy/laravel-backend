<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Admin Panel')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="manifest" href="/manifest.json">
    <meta name="theme-color" content="#0f172a">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Presensi">
    <link rel="icon" type="image/png" sizes="192x192" href="/icons/icon-192.png">
    <link rel="icon" type="image/png" sizes="512x512" href="/icons/icon-512.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="apple-touch-icon-precomposed" href="/apple-touch-icon-precomposed.png">
    <link rel="apple-touch-icon" sizes="192x192" href="/icons/icon-192.png">
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
    /* Custom Scrollbar */
    .sidebar-scroll {
        scrollbar-width: thin;
        scrollbar-color: rgba(255,255,255,.1) transparent;
    }
    .sidebar-scroll::-webkit-scrollbar { width: 3px; }
    .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
    .sidebar-scroll::-webkit-scrollbar-thumb {
        background: rgba(255,255,255,.15);
        border-radius: 10px;
    }
    .sidebar-scroll::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,.25); }
 
    :root {
        --sidebar-expanded-width: 16rem;
        --sidebar-collapsed-width: 4.5rem;
        --admin-bg: #f8fafc;
        --admin-surface: #ffffff;
        --admin-card: #ffffff;
        --admin-ink: #0f172a;
        --admin-muted: #64748b;
        --admin-border: rgba(37, 99, 235, .12);
        --admin-navy: #1d4ed8;
        --admin-blue: #2563eb;
        --admin-cyan: #38bdf8;
    }

    body {
        background: var(--admin-bg);
        color: var(--admin-ink);
    }
 
    /* ===== SIDEBAR ===== */
    #sidebar {
        transition: width .3s ease, transform .3s ease;
        width: var(--sidebar-expanded-width);
        background: var(--admin-surface) !important;
        border: 1px solid rgba(219,234,254,.90);
        box-shadow: 18px 0 44px rgba(37,99,235,.08);
    }
    #sidebar.sidebar-collapsed { width: var(--sidebar-collapsed-width); }
 
    /* brand & text fade */
    #sidebar.sidebar-collapsed .sidebar-text,
    #sidebar.sidebar-collapsed .brand-text {
        opacity: 0;
        width: 0;
        overflow: hidden;
        pointer-events: none;
    }
    #sidebar.sidebar-collapsed .brand-wrapper {
        justify-content: center;
        padding-left: 0;
        padding-right: 0;
    }
    .sidebar-text {
        transition: opacity .3s ease, width .3s ease;
        white-space: nowrap;
        font-size: 15px;
        font-weight: 500;
    }
    .brand-text { transition: opacity .3s ease, width .3s ease; }

    .tooltip {
        position: absolute;
        left: calc(100% + .75rem);
        top: 50%;
        transform: translateY(-50%);
        padding: .45rem .7rem;
        border-radius: .5rem;
        background: var(--admin-navy);
        color: #fff;
        font-size: .8rem;
        line-height: 1;
        white-space: nowrap;
        box-shadow: 0 10px 24px rgba(15,23,42,.28);
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        z-index: 120;
        transition: opacity .18s ease, visibility .18s ease;
    }
 
    /* ===== SIDEBAR ITEM (expanded) ===== */
    .sidebar-item {
        width: 100%;
        box-sizing: border-box;
        border-radius: 9999px !important;
        color: #4b5563 !important;
        transition: background .2s ease, color .2s ease, box-shadow .2s ease, transform .2s ease;
    }
    .sidebar-item:hover {
        background: rgba(37, 99, 235, .08) !important;
        color: var(--admin-blue) !important;
    }
    .sidebar-item:focus {
        outline: none;
    }
    .sidebar-item:focus-visible {
        background: rgba(37, 99, 235, .12) !important;
        color: var(--admin-blue) !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, .16);
    }
    .sidebar-item.active,
    .menu-trigger[aria-expanded="true"] {
        background: linear-gradient(135deg, #1d4ed8, #0284c7) !important;
        color: #fff !important;
        box-shadow: 0 14px 28px rgba(37,99,235,.24);
    }
    .sidebar-item i {
        color: currentColor !important;
    }
 
    /* ===== COLLAPSED: sembunyikan submenu & caret ===== */
    #sidebar.sidebar-collapsed .submenu,
    #sidebar.sidebar-collapsed .menu-caret { display: none !important; }
 
    /* ===== COLLAPSED: icon centering ===== */
    #sidebar.sidebar-collapsed #sidebar-nav {
        padding-left: .75rem;
        padding-right: .75rem;
    }
    #sidebar.sidebar-collapsed .sidebar-item {
        width: 3rem;
        height: 3rem;
        justify-content: center;
        align-items: center;
        padding-left: 0;
        padding-right: 0;
        margin-left: auto;
        margin-right: auto;
        gap: 0;
    }
    #sidebar.sidebar-collapsed .menu-trigger {
        width: 3rem;
    }
    #sidebar.sidebar-collapsed .sidebar-item i.fa-w-5,
    #sidebar.sidebar-collapsed .sidebar-item > i:first-child,
    #sidebar.sidebar-collapsed .sidebar-item .fas,
    #sidebar.sidebar-collapsed .sidebar-item .fa-solid {
        margin: 0 !important;
        width: 1.25rem;
        text-align: center;
        flex-shrink: 0;
    }
    #sidebar.sidebar-collapsed .sidebar-item .sidebar-text,
    #sidebar.sidebar-collapsed .sidebar-item .tooltip {
        pointer-events: none;
    }
    #sidebar.sidebar-collapsed .menu-trigger .sidebar-text,
    #sidebar.sidebar-collapsed > .sidebar-item .sidebar-text,
    #sidebar.sidebar-collapsed .sidebar-item .sidebar-text {
        display: none;
    }
    #sidebar.sidebar-collapsed .sidebar-item:hover .tooltip {
        opacity: 1;
        visibility: visible;
    }
 
    /* ===== FLYOUT DROPDOWN (collapsed state) ===== */
    .flyout-menu {
        position: fixed;          /* fixed agar tidak terpotong sidebar */
        left: calc(var(--sidebar-collapsed-width) + .6rem);
        top: 0;                   /* di-set via JS */
        min-width: 200px;
        background: #fff;
        border: 1px solid var(--admin-border);
        border-radius: 1rem;
        box-shadow: 0 18px 36px rgba(7,18,37,.18);
        z-index: 200;
        overflow: hidden;
 
        /* animasi */
        opacity: 0;
        transform: translateX(-8px);
        pointer-events: none;
        transition: opacity .22s ease .08s, transform .22s ease .08s;
    }
    .flyout-menu.flyout-visible {
        opacity: 1;
        transform: translateX(0);
        pointer-events: auto;
    }
    .flyout-menu.flyout-hiding {
        opacity: 0;
        transform: translateX(-8px);
        pointer-events: none;
        transition: opacity .18s ease, transform .18s ease;
    }
    .flyout-title {
        padding: .55rem 1rem .4rem;
        font-size: .7rem;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
        color: var(--admin-muted);
        border-bottom: 1px solid var(--admin-border);
    }
    .flyout-item + .flyout-item {
        margin-top: .45rem;
    }
    .flyout-item {
        display: flex;
        align-items: center;
        gap: .6rem;
        padding: .55rem 1rem;
        color: #4b5563;
        font-size: .85rem;
        font-weight: 500;
        text-decoration: none;
        transition: color .15s ease, text-decoration-color .15s ease;
    }
    .flyout-item:hover {
        color: var(--admin-ink);
        background: rgba(7,18,37,.05);
    }
    .flyout-item.active {
        color: var(--admin-ink);
        background: rgba(7,18,37,.08);
    }
 
    /* ===== SUBMENU (expanded state) ===== */
    .submenu {
        display: none;
        margin-top: .35rem;
        margin-left: .9rem;
        padding-left: .9rem;
        border-left: 1px solid rgba(7,18,37,.10);
    }
    .submenu.is-open { display: block; }
    .submenu-item + .submenu-item {
        margin-top: .45rem;
    }
    .submenu-item {
        display: flex;
        align-items: center;
        padding: .55rem .65rem;
        border-radius: .5rem;
        color: #6b7280;
        font-size: 14px;
        font-weight: 500;
        text-decoration: none;
        transition: color .2s ease, text-decoration-color .2s ease;
    }
    .submenu-item:hover {
        color: var(--admin-ink);
        background: rgba(7,18,37,.05);
        text-decoration: none;
    }
    .submenu-item.active {
        color: var(--admin-ink);
        background: rgba(7,18,37,.08);
        text-decoration: none;
    }
 
    /* menu caret */
    .menu-caret { transition: transform .2s ease; }
    .menu-trigger[aria-expanded="true"] .menu-caret { transform: rotate(180deg); }
    .menu-trigger { width: 100%; border: 0; background: transparent; }
 
    /* ===== MAIN CONTENT ===== */
    #main-content { transition: margin-left .3s ease; }
 
    /* ===== COLLAPSE BUTTON ===== */
    #collapse-btn { transition: left .3s ease, background-color .2s ease; }

    #sidebar .brand-wrapper {
        background: transparent !important;
    }
    #sidebar .brand-wrapper .w-10 {
        background: linear-gradient(135deg, #1d4ed8, #0284c7) !important;
        color: #fff !important;
        border-radius: .9rem !important;
        box-shadow: 0 12px 24px rgba(37,99,235,.24);
    }
    #sidebar .brand-wrapper .w-10 span {
        color: #fff !important;
        font-size: 1rem !important;
    }
    #sidebar .brand-text h1 {
        color: var(--admin-ink) !important;
        font-weight: 800;
    }
    #sidebar > .h-20 {
        border-color: rgba(7,18,37,.10) !important;
    }

    #collapse-btn {
        background: linear-gradient(135deg, #1d4ed8, #0284c7) !important;
        border: 4px solid var(--admin-bg);
        box-shadow: 0 14px 28px rgba(37,99,235,.24);
    }

    #main-content > header {
        margin: .4rem .4rem 0;
        border: 1px solid rgba(219,234,254,.95) !important;
        border-radius: 1.25rem;
        background: rgba(255,255,255,.92) !important;
        box-shadow: 0 18px 42px rgba(37,99,235,.08);
    }
    #main-content > .flex-1 {
        background: var(--admin-bg) !important;
    }
    main .bg-white {
        border: 1px solid rgba(219,234,254,.90);
        border-radius: 1rem !important;
        box-shadow: 0 18px 42px rgba(37,99,235,.08) !important;
    }
    main .shadow,
    main .shadow-sm,
    main .shadow-md,
    main .shadow-xl,
    main .shadow-2xl {
        box-shadow: 0 18px 42px rgba(37,99,235,.08) !important;
    }
    main table thead,
    main .bg-gray-50 {
        background: #f8fbff !important;
    }
    main input,
    main select,
    main textarea {
        border-color: rgba(7,18,37,.10) !important;
        border-radius: .9rem !important;
        background-color: #f8fafb;
    }
    main input:focus,
    main select:focus,
    main textarea:focus {
        border-color: var(--admin-blue) !important;
        box-shadow: 0 0 0 3px rgba(37,99,235,.12) !important;
    }
    .admin-top-search input,
    .admin-top-search input:focus {
        border: 0 !important;
        border-radius: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
    }
    main .bg-blue-600,
    main .bg-blue-700,
    main .hover\:bg-blue-700:hover {
        background: linear-gradient(135deg, #1d4ed8, #0284c7) !important;
    }
    main .text-blue-600,
    main .text-blue-700 {
        color: var(--admin-blue) !important;
    }
    main .rounded-xl,
    main .rounded-lg,
    main .rounded-md {
        border-radius: 1rem !important;
    }
 
    /* ===== TOAST ===== */
    .admin-toast-stack {
        position: fixed; top: 1.25rem; right: 1.25rem;
        display: flex; flex-direction: column; gap: .75rem;
        width: min(22rem, calc(100vw - 2rem)); z-index: 120;
        pointer-events: none;
    }
    .admin-toast {
        display: flex; flex-direction: column; align-items: flex-start;
        gap: .85rem; padding: .95rem 1rem; border-radius: .375rem; color: #fff;
        box-shadow: 0 18px 38px rgba(15,23,42,.18);
        transform: translate3d(0,-14px,0); opacity: 0;
        transition: opacity .28s ease, transform .28s ease;
        pointer-events: auto;
    }
    .admin-toast.is-visible { opacity:1; transform: translate3d(0,0,0); }
    .admin-toast.is-hiding  { opacity:0; transform: translate3d(0,-10px,0); }
    .admin-toast--success { background: linear-gradient(135deg,#65c26b,#4fae59); }
    .admin-toast--error   { background: linear-gradient(135deg,#ef4444,#dc2626); }
    .admin-toast--warning { background: linear-gradient(135deg,#f59e0b,#d97706); }
    .admin-toast--info    { background: linear-gradient(135deg,#3b82f6,#2563eb); }
    .admin-toast__body    { width:100%; display:flex; align-items:flex-start; gap:.85rem; }
    .admin-toast__icon    {
        width:2rem; height:2rem; border-radius:9999px;
        background:rgba(255,255,255,.16); display:inline-flex;
        align-items:center; justify-content:center; flex-shrink:0; margin-top:.1rem;
    }
    .admin-toast__title   { font-size:.92rem; font-weight:700; line-height:1.2; }
    .admin-toast__message { margin-top:.18rem; font-size:.78rem; line-height:1.35; color:rgba(255,255,255,.9); }
    .admin-toast__close   { margin-left:auto; border:0; background:transparent; color:rgba(255,255,255,.85); padding:0; line-height:1; cursor:pointer; }
    .admin-toast__progress { width:100%; height:.26rem; border-radius:.375rem; overflow:hidden; background:rgba(255,255,255,.2); }
    .admin-toast__progress-bar { width:100%; height:100%; border-radius:inherit; background:rgba(255,255,255,.9); transform-origin:left center; }
    .admin-toast.is-visible .admin-toast__progress-bar {
        animation: toast-progress var(--toast-duration,4200ms) linear forwards;
    }
    @keyframes toast-progress { from { transform:scaleX(1); } to { transform:scaleX(0); } }
 
    /* ===== RESPONSIVE ===== */
    @media (max-width:767px) {
        #sidebar, #sidebar.sidebar-collapsed { width: min(18rem, calc(100vw - 2rem)); }
        #main-content, #sidebar.sidebar-collapsed ~ #main-content { margin-left:0 !important; }
        #main-content > header {
            margin: 0;
            border-radius: 0 0 1rem 1rem;
        }
    }
    </style>
</head>
<body>
@php
    $adminToasts = collect([
        session('success') ? ['type' => 'success', 'title' => 'Berhasil', 'message' => session('success')] : null,
        session('error') ? ['type' => 'error', 'title' => 'Gagal', 'message' => session('error')] : null,
        session('warning') ? ['type' => 'warning', 'title' => 'Perhatian', 'message' => session('warning')] : null,
        session('info') ? ['type' => 'info', 'title' => 'Informasi', 'message' => session('info')] : null,
        $errors->any() ? ['type' => 'error', 'title' => 'Validasi Gagal', 'message' => $errors->first()] : null,
    ])->filter()->values();
@endphp

@if($adminToasts->isNotEmpty())
    <div id="admin-toast-stack" class="admin-toast-stack">
        @foreach($adminToasts as $toast)
            <div class="admin-toast admin-toast--{{ $toast['type'] }}" data-toast style="--toast-duration: 4200ms;">
                <div class="admin-toast__body">
                    <div class="admin-toast__icon">
                        <i class="fas {{ $toast['type'] === 'success' ? 'fa-check' : ($toast['type'] === 'error' ? 'fa-xmark' : ($toast['type'] === 'warning' ? 'fa-exclamation' : 'fa-info')) }}"></i>
                    </div>
                    <div>
                        <p class="admin-toast__title">{{ $toast['title'] }}</p>
                        <p class="admin-toast__message">{{ $toast['message'] }}</p>
                    </div>
                    <button type="button" class="admin-toast__close" data-toast-close aria-label="Tutup notifikasi">
                        <i class="fas fa-xmark"></i>
                    </button>
                </div>
                <div class="admin-toast__progress" aria-hidden="true">
                    <div class="admin-toast__progress-bar"></div>
                </div>
            </div>
        @endforeach
    </div>
@endif

@php
    $adminPageTitle = trim($__env->yieldContent('title', 'Dashboard'));
    $adminSection = match (true) {
        request()->routeIs('admin.dashboard') => 'Dashboard',
        request()->routeIs('admin.departments.*'),
        request()->routeIs('admin.positions.*') => 'Struktur Organisasi',
        request()->routeIs('admin.users.*'),
        request()->routeIs('admin.biodata.*'),
        request()->routeIs('admin.face_data.*') => 'Pegawai',
        request()->routeIs('admin.shifts.*'),
        request()->routeIs('jadwal-dinas.*'),
        request()->routeIs('admin.shift_management.schedules*'),
        request()->routeIs('admin.shift_management.swaps*') => 'Jadwal & Shift',
        request()->routeIs('admin.histories.*'),
        request()->routeIs('admin.leave_requests.*'),
        request()->routeIs('admin.features.show') => 'Absensi & Izin',
        request()->routeIs('admin.announcements.*'),
        request()->routeIs('admin.reports.*'),
        request()->routeIs('admin.notifications.*') => 'Info & Laporan',
        request()->routeIs('admin.settings.*') => 'Pengaturan',
        default => 'Admin',
    };
@endphp

<div class="flex min-h-screen">
    <!-- Sidebar Desktop - Fixed Position -->
    <aside id="sidebar" class="w-64 bg-gray-800 shadow-2xl fixed left-0 top-0 h-screen z-40 transform -translate-x-full md:translate-x-0 transition-all duration-300">
        <!-- Logo/Brand - Fixed at top -->
        <div class="h-20 px-4 border-b border-gray-700 flex items-center relative flex-shrink-0">
            <div class="brand-wrapper flex items-center gap-2 bg-gray-800 px-4 py-2 max-w-full w-full transition-all duration-300">
    
                <!-- Avatar circle -->
                <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <span class="text-gray-700 font-bold text-2xl">A</span>
                </div>

                <!-- Text -->
                <div class="brand-text overflow-hidden">
                    <h1 class="font-3xl text-white whitespace-nowrap tracking-[.3px]">
                        Admin Panel
                    </h1>
                </div>
            </div>
        </div>

        <!-- Navigation with Custom Scrollbar - Scrollable Area -->
        @php
            $employeeOpen = request()->routeIs('admin.users.*') || request()->routeIs('admin.biodata.*') || request()->routeIs('admin.face_data.*');
            $organizationOpen = request()->routeIs('admin.departments.*') || request()->routeIs('admin.positions.*');
            $scheduleOpen = request()->routeIs('admin.shifts.*') || request()->routeIs('jadwal-dinas.*') || request()->routeIs('admin.shift_management.schedules*') || request()->routeIs('admin.shift_management.swaps*');
            $attendanceOpen = request()->routeIs('admin.histories.*') || request()->routeIs('admin.leave_requests.*') || request()->routeIs('admin.features.show');
            $infoOpen = request()->routeIs('admin.announcements.*') || request()->routeIs('admin.reports.*') || request()->routeIs('admin.notifications.*');
            $settingsOpen = request()->routeIs('admin.settings.*');
            $adminFeatureSettings = \App\Models\FeatureSetting::matrix();
            $adminNotifications = app(\App\Services\AdminNotificationService::class)->items(5);
            $adminNotificationCount = app(\App\Services\AdminNotificationService::class)->count();
        @endphp
        <nav id="sidebar-nav" class="md:px-2 sidebar-scroll overflow-y-auto px-4 py-3 space-y-2" style="height: calc(100vh - 5rem);">
            <a href="{{ route('admin.dashboard') }}"
               class="sidebar-item relative flex items-center gap-3 p-3 rounded-md {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fas fa-chart-line w-5 flex-shrink-0 text-center"></i>
                <span class="sidebar-text">Dashboard</span>
                <span class="tooltip">Dashboard</span>
            </a>

               <div class="menu-group">
                <button type="button"
                        data-menu-target="menu-organization"
                        aria-expanded="{{ $organizationOpen ? 'true' : 'false' }}"
                        class="sidebar-item menu-trigger relative flex items-center gap-3 p-3 rounded-md {{ $organizationOpen ? 'active' : '' }}">
                    <i class="fas fa-sitemap w-5 flex-shrink-0 text-center"></i>
                    <span class="sidebar-text flex-1 text-left">Struktur Organisasi</span>
                    <i class="fas fa-chevron-down menu-caret text-xs"></i>
                </button>
                <div id="menu-organization" class="submenu {{ $organizationOpen ? 'is-open' : '' }}">
                    <a href="{{ route('admin.departments.index') }}" class="submenu-item {{ request()->routeIs('admin.departments.*') ? 'active' : '' }}">Master Unit Kerja/Bagian</a>
                    <a href="{{ route('admin.positions.index') }}" class="submenu-item {{ request()->routeIs('admin.positions.*') ? 'active' : '' }}">Master Jabatan</a>
                </div>
            </div>

            <div class="menu-group">
                <button type="button"
                        data-menu-target="menu-employee"
                        aria-expanded="{{ $employeeOpen ? 'true' : 'false' }}"
                        class="sidebar-item menu-trigger relative flex items-center gap-3 p-3 rounded-md {{ $employeeOpen ? 'active' : '' }}">
                    <i class="fas fa-users w-5 flex-shrink-0 text-center"></i>
                    <span class="sidebar-text flex-1 text-left">Pegawai</span>
                    <i class="fas fa-chevron-down menu-caret text-xs"></i>
                </button>
                <div id="menu-employee" class="submenu {{ $employeeOpen ? 'is-open' : '' }}">
                    <a href="{{ route('admin.users.index') }}" class="submenu-item {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.biodata.*') ? 'active' : '' }}">Akun Pegawai</a>
                    <a href="{{ route('admin.face_data.index') }}" class="submenu-item {{ request()->routeIs('admin.face_data.*') ? 'active' : '' }}">Data Wajah</a>
                </div>
            </div>

         

            <div class="menu-group">
                <button type="button"
                        data-menu-target="menu-schedule"
                        aria-expanded="{{ $scheduleOpen ? 'true' : 'false' }}"
                        class="sidebar-item menu-trigger relative flex items-center gap-3 p-3 rounded-md {{ $scheduleOpen ? 'active' : '' }}">
                    <i class="fas fa-calendar-days w-5 flex-shrink-0 text-center"></i>
                    <span class="sidebar-text flex-1 text-left">Jadwal & Shift</span>
                    <i class="fas fa-chevron-down menu-caret text-xs"></i>
                </button>
                <div id="menu-schedule" class="submenu {{ $scheduleOpen ? 'is-open' : '' }}">
                    <a href="{{ route('admin.shifts.index') }}" class="submenu-item {{ request()->routeIs('admin.shifts.*') ? 'active' : '' }}">Master Shift</a>
                    <a href="{{ route('jadwal-dinas.index') }}" class="submenu-item {{ request()->routeIs('jadwal-dinas.*') || request()->routeIs('admin.shift_management.schedules') ? 'active' : '' }}">Jadwal Bulanan</a>
                    <a href="{{ route('admin.shift_management.swaps') }}" class="submenu-item {{ request()->routeIs('admin.shift_management.swaps*') ? 'active' : '' }}">Tukar Shift</a>
                </div>
            </div>

            <div class="menu-group">
                <button type="button"
                        data-menu-target="menu-attendance"
                        aria-expanded="{{ $attendanceOpen ? 'true' : 'false' }}"
                        class="sidebar-item menu-trigger relative flex items-center gap-3 p-3 rounded-md {{ $attendanceOpen ? 'active' : '' }}">
                    <i class="fas fa-clipboard-check w-5 flex-shrink-0 text-center"></i>
                    <span class="sidebar-text flex-1 text-left">Absensi & Izin</span>
                    <i class="fas fa-chevron-down menu-caret text-xs"></i>
                </button>
                <div id="menu-attendance" class="submenu {{ $attendanceOpen ? 'is-open' : '' }}">
                    <a href="{{ route('admin.histories.index') }}" class="submenu-item {{ request()->routeIs('admin.histories.*') ? 'active' : '' }}">Riwayat Absensi</a>
                    <a href="{{ route('admin.leave_requests.index') }}" class="submenu-item {{ request()->routeIs('admin.leave_requests.*') ? 'active' : '' }}">Perizinan</a>
                    @foreach(\App\Models\FeatureSetting::FEATURES as $featureKey => $feature)
                        @continue(!\App\Models\FeatureSetting::availableForRole($featureKey, 'admin'))
                        @if($adminFeatureSettings[$featureKey]['admin'] ?? false)
                            <a href="{{ route('admin.features.show', $featureKey) }}" class="submenu-item {{ request()->routeIs('admin.features.show') && request()->route('featureKey') === $featureKey ? 'active' : '' }}">{{ $feature['label'] }}</a>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="menu-group">
                <button type="button"
                        data-menu-target="menu-info"
                        aria-expanded="{{ $infoOpen ? 'true' : 'false' }}"
                        class="sidebar-item menu-trigger relative flex items-center gap-3 p-3 rounded-md {{ $infoOpen ? 'active' : '' }}">
                    <i class="fas fa-bullhorn w-5 flex-shrink-0 text-center"></i>
                    <span class="sidebar-text flex-1 text-left">Info & Laporan</span>
                    <i class="fas fa-chevron-down menu-caret text-xs"></i>
                </button>
                <div id="menu-info" class="submenu {{ $infoOpen ? 'is-open' : '' }}">
                    <a href="{{ route('admin.notifications.index') }}" class="submenu-item {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">Notifikasi</a>
                    <a href="{{ route('admin.announcements.index') }}" class="submenu-item {{ request()->routeIs('admin.announcements.*') ? 'active' : '' }}">Pengumuman</a>
                    <a href="{{ route('admin.reports.index') }}" class="submenu-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}">Laporan</a>
                </div>
            </div>

            <div class="menu-group">
                <button type="button"
                        data-menu-target="menu-settings"
                        aria-expanded="{{ $settingsOpen ? 'true' : 'false' }}"
                        class="sidebar-item menu-trigger relative flex items-center gap-3 p-3 rounded-md {{ $settingsOpen ? 'active' : '' }}">
                    <i class="fas fa-gear w-5 flex-shrink-0 text-center"></i>
                    <span class="sidebar-text flex-1 text-left">Pengaturan</span>
                    <i class="fas fa-chevron-down menu-caret text-xs"></i>
                </button>
                <div id="menu-settings" class="submenu {{ $settingsOpen ? 'is-open' : '' }}">
                    <a href="{{ route('admin.settings.admin_accounts.index') }}" class="submenu-item {{ request()->routeIs('admin.settings.admin_accounts.*') ? 'active' : '' }}">Akun Admin</a>
                    <a href="{{ route('admin.settings.work.edit') }}" class="submenu-item {{ request()->routeIs('admin.settings.work.*') ? 'active' : '' }}">Lokasi Presensi</a>
                    <a href="{{ route('admin.settings.features.index') }}" class="submenu-item {{ request()->routeIs('admin.settings.features.*') ? 'active' : '' }}">Pengaturan Fitur</a>
                </div>
            </div>
        </nav>
    </aside>

    <button id="collapse-btn" class="hidden md:flex fixed top-10 left-[calc(var(--sidebar-expanded-width)-1rem)] -translate-y-1/2 w-8 h-8 items-center justify-center bg-blue-600 rounded-full text-white hover:bg-blue-700 shadow-md z-50">
        <i class="fas fa-chevron-left text-sm transition-transform duration-300" id="collapse-icon"></i>
    </button>

    <!-- Overlay untuk mobile -->
    <div id="sidebar-overlay" class="fixed inset-0 bg-black bg-opacity-50 z-30 hidden md:hidden"></div>

    <!-- Main Content - Dengan margin untuk sidebar -->
    <main id="main-content" class="flex-1 flex flex-col min-h-screen ml-0 md:ml-64 transition-all duration-300">
        <!-- Header - Sticky -->
        <header class="bg-white border-b border-gray-200 sticky top-0 z-20">
            <div class="px-4 md:px-6 py-4 flex justify-between items-center gap-4">
                <div class="flex items-center gap-4 flex-1">
                    <!-- Mobile Menu Button -->
                    <button id="mobile-menu-btn" class="md:hidden text-gray-600 hover:text-gray-900 focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <div class="admin-top-search hidden xl:flex h-10 w-full max-w-sm items-center gap-2 rounded-md border border-gray-200 bg-white px-3 text-gray-400">
                        <i class="fa-solid fa-magnifying-glass text-gray-400"></i>
                        <input
                            type="search"
                            placeholder="Cari sesuatu"
                            class="h-full w-full border-0 bg-transparent px-0 text-sm text-gray-600 placeholder:text-gray-400 focus:outline-none focus:ring-0"
                        >
                    </div>
                    <div
                        class="hidden md:flex shrink-0 text-sm text-gray-500 tracking-[.2px]"
                        data-admin-clock
                        data-locale="id-ID"
                    >
                        {{ now()->locale('id')->translatedFormat('l, d F H:i:s') }}
                    </div>
                </div>

                <div class="flex items-center gap-3 md:gap-4">
                    <div class="relative group">
                        <button class="relative w-10 h-10 rounded-2xl bg-white border border-blue-100 text-blue-700 hover:bg-blue-50 flex items-center justify-center shadow-sm transition">
                            <i class="fa-solid fa-bell"></i>
                            @if($adminNotificationCount > 0)
                                <span class="absolute -top-1 -right-1 min-w-5 h-5 px-1 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center">
                                    {{ $adminNotificationCount > 99 ? '99+' : $adminNotificationCount }}
                                </span>
                            @endif
                        </button>

                        <div class="absolute right-0 mt-3 w-80 bg-white rounded-2xl shadow-xl border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 overflow-hidden">
                            <div class="p-4 border-b border-slate-100 flex items-center justify-between">
                                <div>
                                    <p class="text-sm font-bold text-slate-900">Notifikasi</p>
                                    <p class="text-xs text-slate-500">{{ $adminNotificationCount }} aktivitas perlu dicek</p>
                                </div>
                                <a href="{{ route('admin.notifications.index') }}" class="text-xs font-bold text-slate-950">Lihat semua</a>
                            </div>

                            <div class="max-h-80 overflow-y-auto">
                                @forelse($adminNotifications as $notification)
                                    <a href="{{ $notification['url'] }}" class="flex items-start gap-3 p-4 hover:bg-slate-50 border-b border-slate-100 transition">
                                        <span class="w-10 h-10 rounded-2xl inline-flex items-center justify-center shrink-0 {{ $notification['tone'] }}">
                                            <i class="{{ $notification['icon'] }} text-sm"></i>
                                        </span>
                                        <span class="min-w-0">
                                            <span class="block text-sm font-bold text-slate-900 truncate">{{ $notification['title'] }}</span>
                                            <span class="block text-xs text-slate-500 mt-1">{{ $notification['message'] }}</span>
                                        </span>
                                    </a>
                                @empty
                                    <div class="p-6 text-center">
                                        <p class="text-sm font-bold text-slate-800">Tidak ada notifikasi</p>
                                        <p class="text-xs text-slate-500 mt-1">Semua aktivitas penting aman.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>

                    <!-- User Profile Dropdown -->
                    <div class="relative group">
                        <button class="flex items-center gap-2 md:gap-3 hover:bg-blue-50 rounded-2xl p-2 transition">
                            <div class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-blue-700 flex items-center justify-center text-white font-bold shadow-lg shadow-blue-700/20 text-xl">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            <div class="hidden md:block text-left">
                                <p class="text-sm font-semibold text-gray-800 tracking-[.3px]">
                                    {{ auth()->user()->name }}
                                </p>
                                <p class="text-xs text-gray-500 tracking-[.3px]">Administrator</p>
                            </div>
                            <i class="fas fa-chevron-down text-gray-400 text-xs hidden md:block"></i>
                        </button>

                        <!-- Dropdown Menu -->
                        <div class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-xl border border-gray-100 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200">
                            <div class="p-3 border-b border-gray-100">
                                <p class="text-sm font-semibold text-gray-800">{{ auth()->user()->name }}</p>
                                <p class="text-xs text-gray-500">{{ auth()->user()->email }}</p>
                            </div>
                            <a href="{{ route('admin.settings.admin_accounts.edit', auth()->user()) }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                                <i class="fas fa-user-circle w-4"></i>
                                Profile
                            </a>
                            <a href="{{ route('admin.settings.admin_accounts.index') }}" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                                <i class="fas fa-cog w-4"></i>
                                Settings
                            </a>
                            <div class="border-t border-gray-100">
                                <form method="POST" action="/logout">
                                    @csrf
                                    <button class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 flex items-center gap-2">
                                        <i class="fas fa-sign-out-alt w-4"></i>
                                        Logout
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Breadcrumb -->
          
        </header>

        <!-- Content Area -->
        <div class="flex-1 p-4 md:p-6 bg-gray-50">
            @yield('content')
        </div>
    </main>
</div>

<script>
   // ─── Mobile menu ────────────────────────────────────────────────────────────
const mobileMenuBtn   = document.getElementById('mobile-menu-btn');
const sidebar         = document.getElementById('sidebar');
const sidebarOverlay  = document.getElementById('sidebar-overlay');
const mainContent     = document.getElementById('main-content');
const collapseBtn     = document.getElementById('collapse-btn');
 
function toggleMobileMenu() {
    sidebar.classList.toggle('-translate-x-full');
    sidebarOverlay.classList.toggle('hidden');
}
mobileMenuBtn?.addEventListener('click', toggleMobileMenu);
sidebarOverlay?.addEventListener('click', toggleMobileMenu);
 
// ─── Expanded submenu accordion ─────────────────────────────────────────────
const menuTriggers = document.querySelectorAll('[data-menu-target]');
 
function toggleSidebarMenu(trigger) {
    if (!trigger) return;
    if (window.innerWidth >= 768 && sidebar.classList.contains('sidebar-collapsed')) return;
 
    const targetId   = trigger.getAttribute('data-menu-target');
    const targetMenu = document.getElementById(targetId);
    if (!targetMenu) return;
 
    const shouldOpen = trigger.getAttribute('aria-expanded') !== 'true';
 
    // tutup semua dulu
    menuTriggers.forEach(item => {
        const m = document.getElementById(item.getAttribute('data-menu-target'));
        item.setAttribute('aria-expanded', 'false');
        m?.classList.remove('is-open');
    });
 
    trigger.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
    targetMenu.classList.toggle('is-open', shouldOpen);
}
 
menuTriggers.forEach(trigger => {
    trigger.addEventListener('click', () => toggleSidebarMenu(trigger));
});
 
// ─── Sidebar collapse (desktop) ─────────────────────────────────────────────
function syncDesktopSidebarState() {
    if (window.innerWidth < 768) {
        mainContent.style.marginLeft = '0';
        sidebar.classList.remove('sidebar-collapsed');
        destroyAllFlyouts();
        return;
    }
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    sidebar.classList.toggle('sidebar-collapsed', isCollapsed);
    collapseBtn?.classList.toggle('is-collapsed', isCollapsed);
    mainContent.style.marginLeft = isCollapsed ? '4.5rem' : '16rem';
    if (collapseBtn) {
        collapseBtn.style.left = isCollapsed ? '3.5rem' : '15rem';
    }
    if (!isCollapsed) destroyAllFlyouts();
}
 
collapseBtn?.addEventListener('click', () => {
    if (window.innerWidth < 768) return;
    const next = !sidebar.classList.contains('sidebar-collapsed');
    localStorage.setItem('sidebarCollapsed', next);
    syncDesktopSidebarState();
});
 
window.addEventListener('DOMContentLoaded', syncDesktopSidebarState);
window.addEventListener('resize', () => {
    if (window.innerWidth >= 768) {
        sidebar.classList.remove('-translate-x-full');
        sidebarOverlay.classList.add('hidden');
        syncDesktopSidebarState();
    } else {
        mainContent.style.marginLeft = '0';
        destroyAllFlyouts();
    }
});
 
// ─── FLYOUT DROPDOWN (collapsed state) ──────────────────────────────────────
/*
  Setiap .menu-group memiliki:
    - button[data-menu-target]   → trigger
    - div#menu-*                 → konten submenu (sudah ada di HTML)
 
  Flyout dibuat secara dinamis lalu di-append ke <body>.
  Muncul saat hover pada trigger, hilang saat meninggalkan area trigger+flyout.
*/
 
let activeFlyout   = null;  // elemen flyout yang sedang tampil
let hideTimer      = null;  // timer delay sebelum sembunyi
const HIDE_DELAY   = 180;   // ms — delay sebelum flyout hilang
const SHOW_DELAY   = 80;    // ms — delay sebelum flyout muncul
let showTimer      = null;
 
function destroyAllFlyouts() {
    document.querySelectorAll('.flyout-menu').forEach(f => f.remove());
    activeFlyout = null;
}
 
function buildFlyout(trigger) {
    const targetId = trigger.getAttribute('data-menu-target');
    const sourceMenu = document.getElementById(targetId);
    if (!sourceMenu) return null;
 
    // ambil judul dari teks trigger
    const titleEl   = trigger.querySelector('.sidebar-text');
    const titleText  = titleEl ? titleEl.textContent.trim() : '';
 
    // ambil semua submenu-item
    const items = sourceMenu.querySelectorAll('.submenu-item');
    if (!items.length) return null;
 
    const flyout = document.createElement('div');
    flyout.className = 'flyout-menu';
    flyout.setAttribute('data-flyout-for', targetId);
 
    // judul
    if (titleText) {
        const title = document.createElement('div');
        title.className = 'flyout-title';
        title.textContent = titleText;
        flyout.appendChild(title);
    }
 
    // clone item
    items.forEach(item => {
        const clone = document.createElement('a');
        clone.className = 'flyout-item' + (item.classList.contains('active') ? ' active' : '');
        clone.href = item.href || '#';
        clone.innerHTML = item.innerHTML;
        flyout.appendChild(clone);
    });
 
    document.body.appendChild(flyout);
    return flyout;
}
 
function positionFlyout(flyout, trigger) {
    const rect = trigger.getBoundingClientRect();
    flyout.style.top = rect.top + 'px';
}
 
function showFlyout(trigger) {
    if (!sidebar.classList.contains('sidebar-collapsed')) return;
    if (window.innerWidth < 768) return;
 
    clearTimeout(hideTimer);
    clearTimeout(showTimer);
 
    const targetId = trigger.getAttribute('data-menu-target');
 
    // sudah tampil flyout yang sama → biarkan saja
    if (activeFlyout && activeFlyout.getAttribute('data-flyout-for') === targetId) return;
 
    // sembunyikan flyout lama dulu
    if (activeFlyout) hideFlyoutNow(activeFlyout);
 
    showTimer = setTimeout(() => {
        const flyout = buildFlyout(trigger);
        if (!flyout) return;
 
        positionFlyout(flyout, trigger);
        activeFlyout = flyout;
 
        // hover pada flyout sendiri → batalkan hide
        flyout.addEventListener('mouseenter', () => clearTimeout(hideTimer));
        flyout.addEventListener('mouseleave', () => schedulHideFlyout(flyout));
 
        // paksa reflow lalu tambah class visible
        flyout.getBoundingClientRect();
        flyout.classList.add('flyout-visible');
    }, SHOW_DELAY);
}
 
function schedulHideFlyout(flyout) {
    clearTimeout(hideTimer);
    hideTimer = setTimeout(() => hideFlyoutNow(flyout), HIDE_DELAY);
}
 
function hideFlyoutNow(flyout) {
    if (!flyout) return;
    flyout.classList.remove('flyout-visible');
    flyout.classList.add('flyout-hiding');
    setTimeout(() => {
        flyout.remove();
        if (activeFlyout === flyout) activeFlyout = null;
    }, 200);
}
 
// pasang listener ke setiap menu-group trigger
menuTriggers.forEach(trigger => {
    trigger.addEventListener('mouseenter', () => showFlyout(trigger));
    trigger.addEventListener('mouseleave', () => {
        if (!activeFlyout) return;
        schedulHideFlyout(activeFlyout);
    });
});
 
// ─── TOAST ──────────────────────────────────────────────────────────────────
document.querySelectorAll('[data-toast]').forEach((toast, index) => {
    const closeBtn = toast.querySelector('[data-toast-close]');
    const hide = () => {
        if (toast.classList.contains('is-hiding')) return;
        toast.classList.add('is-hiding');
        setTimeout(() => toast.remove(), 260);
    };
    setTimeout(() => toast.classList.add('is-visible'), 80 + index * 90);
    setTimeout(() => hide(), 4200 + index * 300);
    closeBtn?.addEventListener('click', hide);
});

document.querySelectorAll('[data-admin-clock]').forEach((clock) => {
    const locale = clock.dataset.locale || 'id-ID';
    const formatter = new Intl.DateTimeFormat(locale, {
        weekday: 'long',
        day: '2-digit',
        month: 'long',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit',
        hour12: false,
    });

    const updateClock = () => {
        clock.textContent = formatter.format(new Date()).replace(' pukul ', ' ');
    };

    updateClock();
    setInterval(updateClock, 1000);
});

document.querySelectorAll('[data-auto-filter]').forEach((form) => {
    let timer;
    let isSubmitting = false;

    const submitForm = (delay = 0) => {
        clearTimeout(timer);
        timer = setTimeout(() => {
            if (isSubmitting) return;
            isSubmitting = true;
            form.requestSubmit();
        }, delay);
    };

    form.querySelectorAll('input, select').forEach((field) => {
        if (field.type === 'hidden') return;

        if (field.tagName === 'SELECT' || ['date', 'month', 'checkbox', 'radio'].includes(field.type)) {
            field.addEventListener('change', () => submitForm());
            return;
        }

        field.addEventListener('input', () => submitForm(450));
    });
});
</script>
<script>
if ("serviceWorker" in navigator) {
    window.addEventListener("load", function () {
        navigator.serviceWorker.register("/sw.js")
            .then(function () {
                console.log("Service Worker registered successfully");
            })
            .catch(function (error) {
                console.log("Service Worker registration failed:", error);
            });
    });
}
</script>

</body>
</html>
