<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Admin Panel')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* Custom Scrollbar - Sangat Kecil seperti AdminLTE */
        .sidebar-scroll {
            scrollbar-width: thin;
            scrollbar-color: rgba(255, 255, 255, 0.1) transparent;
        }
        .sidebar-scroll::-webkit-scrollbar {
            width: 3px;
        }
        .sidebar-scroll::-webkit-scrollbar-track {
            background: transparent;
        }
        .sidebar-scroll::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.15);
            border-radius: 10px;
        }
        .sidebar-scroll::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.25);
        }

        .sidebar-item {
            transition: all 0.3s ease;
        }
        #sidebar.sidebar-collapsed .sidebar-item {
            justify-content: center;
            gap: 0;
            width: 3rem;
            height: 3rem;
            margin-left: auto;
            margin-right: auto;
            padding: 0;
        }
        #sidebar.sidebar-collapsed .sidebar-item i {
            width: auto;
            margin: 0;
        }

        :root {
            --sidebar-expanded-width: 16rem;
            --sidebar-collapsed-width: 4.5rem;
        }

        /* Sidebar Collapse Animation */
        #sidebar {
            transition: width 0.3s ease, transform 0.3s ease;
        }
        #sidebar.sidebar-collapsed {
            width: var(--sidebar-collapsed-width);
        }
        #sidebar.sidebar-collapsed .sidebar-text,
        #sidebar.sidebar-collapsed .brand-text {
            opacity: 0;
            width: 0;
            overflow: hidden;
        }
        #sidebar.sidebar-collapsed .brand-wrapper {
            justify-content: center;
            padding-left: 0;
            padding-right: 0;
        }
        .sidebar-text {
            transition: opacity 0.3s ease, width 0.3s ease;
            white-space: nowrap;
            font-size: 15px;
            font-weight: 500;
        }
        .brand-text {
            transition: opacity 0.3s ease, width 0.3s ease;
        }

        /* Tooltip untuk collapsed state */
        .tooltip {
            position: absolute;
            left: 100%;
            top: 50%;
            transform: translateY(-50%);
            margin-left: 0.5rem;
            padding: 0.5rem 0.75rem;
            color: white;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            white-space: nowrap;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s;
            z-index: 50;
        }
        .sidebar-collapsed .sidebar-item:hover .tooltip {
            opacity: 1;
        }
        .menu-trigger {
            width: 100%;
            border: 0;
            background: transparent;
        }
        .menu-caret {
            transition: transform 0.2s ease;
        }
        .menu-trigger[aria-expanded="true"] .menu-caret {
            transform: rotate(180deg);
        }
        .submenu {
            display: none;
            margin-top: 0.25rem;
            margin-left: 0.9rem;
            padding-left: 0.9rem;
            border-left: 1px solid rgba(148, 163, 184, 0.35);
        }
        .submenu.is-open {
            display: block;
        }
        .submenu-item {
            display: flex;
            align-items: center;
            gap: 0.65rem;
            padding: 0.55rem 0.65rem;
            border-radius: 0.5rem;
            color: #cbd5e1;
            font-size: 14px;
            font-weight: 500;
            transition: background-color 0.2s ease, color 0.2s ease;
        }
        .submenu-item:hover {
            background: rgba(71, 85, 105, 0.45);
            color: #fff;
        }
        .submenu-item.active {
            background: linear-gradient(90deg, #0284c7, #1d4ed8);
            color: #fff;
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.12);
        }
        #sidebar.sidebar-collapsed .submenu,
        #sidebar.sidebar-collapsed .menu-caret {
            display: none !important;
        }

        /* Adjust main content margin based on sidebar state */
        #main-content {
            transition: margin-left 0.3s ease;
        }
        #sidebar.sidebar-collapsed ~ #main-content {
            margin-left: var(--sidebar-collapsed-width);
        }
        #collapse-btn {
            transition: left 0.3s ease, transform 0.3s ease, background-color 0.2s ease;
        }
        #collapse-btn.is-collapsed {
            transform: translateY(-50%) rotate(180deg);
        }
        .admin-toast-stack {
            position: fixed;
            top: 1.25rem;
            right: 1.25rem;
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
            width: min(22rem, calc(100vw - 2rem));
            z-index: 120;
            pointer-events: none;
        }
        .admin-toast {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 0.85rem;
            padding: 0.95rem 1rem;
            border-radius: 0.375rem;
            color: #fff;
            box-shadow: 0 18px 38px rgba(15, 23, 42, 0.18);
            transform: translate3d(0, -14px, 0);
            opacity: 0;
            transition: opacity 0.28s ease, transform 0.28s ease;
            pointer-events: auto;
        }
        .admin-toast.is-visible {
            opacity: 1;
            transform: translate3d(0, 0, 0);
        }
        .admin-toast.is-hiding {
            opacity: 0;
            transform: translate3d(0, -10px, 0);
        }
        .admin-toast--success {
            background: linear-gradient(135deg, #65c26b, #4fae59);
        }
        .admin-toast--error {
            background: linear-gradient(135deg, #ef4444, #dc2626);
        }
        .admin-toast--warning {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }
        .admin-toast--info {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
        }
        .admin-toast__body {
            width: 100%;
            display: flex;
            align-items: flex-start;
            gap: 0.85rem;
        }
        .admin-toast__icon {
            width: 2rem;
            height: 2rem;
            border-radius: 9999px;
            background: rgba(255, 255, 255, 0.16);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            margin-top: 0.1rem;
        }
        .admin-toast__title {
            font-size: 0.92rem;
            font-weight: 700;
            line-height: 1.2;
        }
        .admin-toast__message {
            margin-top: 0.18rem;
            font-size: 0.78rem;
            line-height: 1.35;
            color: rgba(255, 255, 255, 0.9);
        }
        .admin-toast__close {
            margin-left: auto;
            border: 0;
            background: transparent;
            color: rgba(255, 255, 255, 0.85);
            padding: 0;
            line-height: 1;
            cursor: pointer;
        }
        .admin-toast__progress {
            width: 100%;
            height: 0.26rem;
            border-radius: 0.375rem;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.2);
        }
        .admin-toast__progress-bar {
            width: 100%;
            height: 100%;
            border-radius: inherit;
            background: rgba(255, 255, 255, 0.9);
            transform-origin: left center;
        }
        .admin-toast.is-visible .admin-toast__progress-bar {
            animation: toast-progress var(--toast-duration, 4200ms) linear forwards;
        }
        @keyframes toast-progress {
            from {
                transform: scaleX(1);
            }
            to {
                transform: scaleX(0);
            }
        }

        @media (min-width: 768px) {
            #sidebar {
                width: var(--sidebar-expanded-width);
            }
        }

        @media (max-width: 767px) {
            #sidebar,
            #sidebar.sidebar-collapsed {
                width: min(18rem, calc(100vw - 2rem));
            }

            #main-content,
            #sidebar.sidebar-collapsed ~ #main-content {
                margin-left: 0 !important;
            }
        }
    </style>
</head>
<body class="font-jakarta">
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
            $employeeOpen = request()->routeIs('admin.users.*') || request()->routeIs('admin.biodata.*') || request()->routeIs('admin.face_data.*') || request()->routeIs('admin.units.*');
            $scheduleOpen = request()->routeIs('admin.shifts.*') || request()->routeIs('admin.shift_management.schedules*') || request()->routeIs('admin.shift_management.swaps*');
            $attendanceOpen = request()->routeIs('admin.histories.*') || request()->routeIs('admin.leave_requests.*') || request()->routeIs('admin.features.show');
            $infoOpen = request()->routeIs('admin.announcements.*') || request()->routeIs('admin.reports.*');
            $settingsOpen = request()->routeIs('admin.settings.*');
            $adminFeatureSettings = \App\Models\FeatureSetting::matrix();
        @endphp
        <nav id="sidebar-nav" class="md:px-2 sidebar-scroll overflow-y-auto px-4 py-3 space-y-2" style="height: calc(100vh - 5rem);">
            <a href="{{ route('admin.dashboard') }}"
               class="sidebar-item relative flex items-center gap-3 p-3 rounded-md text-gray-300 {{ request()->routeIs('admin.dashboard') ? 'active text-white bg-gray-700' : 'hover:bg-gray-700' }}">
                <i class="fas fa-chart-line w-5 flex-shrink-0 text-center"></i>
                <span class="sidebar-text">Dashboard</span>
                <span class="tooltip">Dashboard</span>
            </a>

            <div class="menu-group">
                <button type="button"
                        data-menu-target="menu-employee"
                        aria-expanded="{{ $employeeOpen ? 'true' : 'false' }}"
                        class="sidebar-item menu-trigger relative flex items-center gap-3 p-3 rounded-md text-gray-300 {{ $employeeOpen ? 'text-white bg-gray-700' : 'hover:bg-gray-700' }}">
                    <i class="fas fa-users w-5 flex-shrink-0 text-center"></i>
                    <span class="sidebar-text flex-1 text-left">Pegawai</span>
                    <i class="fas fa-chevron-down menu-caret text-xs"></i>
                </button>
                <div id="menu-employee" class="submenu {{ $employeeOpen ? 'is-open' : '' }}">
                    <a href="{{ route('admin.users.index') }}" class="submenu-item {{ request()->routeIs('admin.users.*') || request()->routeIs('admin.biodata.*') ? 'active' : '' }}"><i class="fas fa-circle text-[7px]"></i> Akun Pegawai</a>
                    <a href="{{ route('admin.face_data.index') }}" class="submenu-item {{ request()->routeIs('admin.face_data.*') ? 'active' : '' }}"><i class="fas fa-circle text-[7px]"></i> Data Wajah</a>
                    <a href="{{ route('admin.units.index') }}" class="submenu-item {{ request()->routeIs('admin.units.*') ? 'active' : '' }}"><i class="fas fa-circle text-[7px]"></i> Unit Kerja</a>
                </div>
            </div>

            <div class="menu-group">
                <button type="button"
                        data-menu-target="menu-schedule"
                        aria-expanded="{{ $scheduleOpen ? 'true' : 'false' }}"
                        class="sidebar-item menu-trigger relative flex items-center gap-3 p-3 rounded-md text-gray-300 {{ $scheduleOpen ? 'text-white bg-gray-700' : 'hover:bg-gray-700' }}">
                    <i class="fas fa-calendar-days w-5 flex-shrink-0 text-center"></i>
                    <span class="sidebar-text flex-1 text-left">Jadwal & Shift</span>
                    <i class="fas fa-chevron-down menu-caret text-xs"></i>
                </button>
                <div id="menu-schedule" class="submenu {{ $scheduleOpen ? 'is-open' : '' }}">
                    <a href="{{ route('admin.shifts.index') }}" class="submenu-item {{ request()->routeIs('admin.shifts.*') ? 'active' : '' }}"><i class="fas fa-circle text-[7px]"></i> Master Shift</a>
                    <a href="{{ route('admin.shift_management.schedules') }}" class="submenu-item {{ request()->routeIs('admin.shift_management.schedules*') ? 'active' : '' }}"><i class="fas fa-circle text-[7px]"></i> Jadwal Pegawai</a>
                    <a href="{{ route('admin.shift_management.swaps') }}" class="submenu-item {{ request()->routeIs('admin.shift_management.swaps*') ? 'active' : '' }}"><i class="fas fa-circle text-[7px]"></i> Tukar Shift</a>
                </div>
            </div>

            <div class="menu-group">
                <button type="button"
                        data-menu-target="menu-attendance"
                        aria-expanded="{{ $attendanceOpen ? 'true' : 'false' }}"
                        class="sidebar-item menu-trigger relative flex items-center gap-3 p-3 rounded-md text-gray-300 {{ $attendanceOpen ? 'text-white bg-gray-700' : 'hover:bg-gray-700' }}">
                    <i class="fas fa-clipboard-check w-5 flex-shrink-0 text-center"></i>
                    <span class="sidebar-text flex-1 text-left">Absensi & Izin</span>
                    <i class="fas fa-chevron-down menu-caret text-xs"></i>
                </button>
                <div id="menu-attendance" class="submenu {{ $attendanceOpen ? 'is-open' : '' }}">
                    <a href="{{ route('admin.histories.index') }}" class="submenu-item {{ request()->routeIs('admin.histories.*') ? 'active' : '' }}"><i class="fas fa-circle text-[7px]"></i> Riwayat Absensi</a>
                    <a href="{{ route('admin.leave_requests.index') }}" class="submenu-item {{ request()->routeIs('admin.leave_requests.*') ? 'active' : '' }}"><i class="fas fa-circle text-[7px]"></i> Perizinan</a>
                    @foreach(\App\Models\FeatureSetting::FEATURES as $featureKey => $feature)
                        @if($adminFeatureSettings[$featureKey]['admin'] ?? false)
                            <a href="{{ route('admin.features.show', $featureKey) }}" class="submenu-item {{ request()->routeIs('admin.features.show') && request()->route('featureKey') === $featureKey ? 'active' : '' }}"><i class="fas fa-circle text-[7px]"></i> {{ $feature['label'] }}</a>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="menu-group">
                <button type="button"
                        data-menu-target="menu-info"
                        aria-expanded="{{ $infoOpen ? 'true' : 'false' }}"
                        class="sidebar-item menu-trigger relative flex items-center gap-3 p-3 rounded-md text-gray-300 {{ $infoOpen ? 'text-white bg-gray-700' : 'hover:bg-gray-700' }}">
                    <i class="fas fa-bullhorn w-5 flex-shrink-0 text-center"></i>
                    <span class="sidebar-text flex-1 text-left">Info & Laporan</span>
                    <i class="fas fa-chevron-down menu-caret text-xs"></i>
                </button>
                <div id="menu-info" class="submenu {{ $infoOpen ? 'is-open' : '' }}">
                    <a href="{{ route('admin.announcements.index') }}" class="submenu-item {{ request()->routeIs('admin.announcements.*') ? 'active' : '' }}"><i class="fas fa-circle text-[7px]"></i> Pengumuman</a>
                    <a href="{{ route('admin.reports.index') }}" class="submenu-item {{ request()->routeIs('admin.reports.*') ? 'active' : '' }}"><i class="fas fa-circle text-[7px]"></i> Laporan</a>
                </div>
            </div>

            <div class="menu-group">
                <button type="button"
                        data-menu-target="menu-settings"
                        aria-expanded="{{ $settingsOpen ? 'true' : 'false' }}"
                        class="sidebar-item menu-trigger relative flex items-center gap-3 p-3 rounded-md text-gray-300 {{ $settingsOpen ? 'text-white bg-gray-700' : 'hover:bg-gray-700' }}">
                    <i class="fas fa-gear w-5 flex-shrink-0 text-center"></i>
                    <span class="sidebar-text flex-1 text-left">Pengaturan</span>
                    <i class="fas fa-chevron-down menu-caret text-xs"></i>
                </button>
                <div id="menu-settings" class="submenu {{ $settingsOpen ? 'is-open' : '' }}">
                    <a href="{{ route('admin.settings.work.edit') }}" class="submenu-item {{ request()->routeIs('admin.settings.work.*') ? 'active' : '' }}"><i class="fas fa-circle text-[7px]"></i> Jam & Lokasi Kerja</a>
                    <a href="{{ route('admin.settings.features.index') }}" class="submenu-item {{ request()->routeIs('admin.settings.features.*') ? 'active' : '' }}"><i class="fas fa-circle text-[7px]"></i> Pengaturan Fitur</a>
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
            <div class="px-4 md:px-6 py-3 flex justify-between items-center">
                <div class="flex items-center gap-4">
                    <!-- Mobile Menu Button -->
                    <button id="mobile-menu-btn" class="md:hidden text-gray-600 hover:text-gray-900 focus:outline-none">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                      <div class="hidden md:flex px-6 text-md text-gray-600 tracking-[.3px]">
                        <a href="#" class="hover:text-blue-700">Home</a>
                        <span class="mx-2">/</span>
                        <span class="text-gray-900 font-medium">@yield('title', 'Dashboard')</span>
                    </div>
                </div>

                <div class="flex items-center gap-3 md:gap-6">
                    <!-- Search Button -->
                    <button class="hidden md:block text-gray-600 hover:text-gray-900">
                        <i class="fas fa-search text-lg"></i>
                    </button>

                    <!-- Notifications -->
                    <button class="relative text-gray-600 hover:text-gray-900">
                        <i class="fas fa-bell text-lg"></i>
                        <span class="p-2.5 absolute -top-1 -right-2 bg-red-500 text-white text-[10px] rounded-full w-3 h-3 flex items-center justify-center">
                            3
                        </span>
                    </button>

                    <!-- Messages -->
                    <button class="hidden md:block relative text-gray-600 hover:text-gray-900">
                        <i class="fas fa-envelope text-lg"></i>
                        <span class="p-2.5 absolute -top-1 -right-2 bg-yellow-500 text-white text-[10px] rounded-full w-3 h-3 flex items-center justify-center">
                            15
                        </span>
                    </button>

                    <!-- User Profile Dropdown -->
                    <div class="relative group">
                        <button class="flex items-center gap-2 md:gap-3 hover:bg-gray-50 rounded-md p-2 transition">
                            <div class="w-8 h-8 md:w-10 md:h-10 rounded-full bg-blue-800 flex items-center justify-center text-white font-bold shadow-lg text-xl">
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
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2">
                                <i class="fas fa-user-circle w-4"></i>
                                Profile
                            </a>
                            <a href="#" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-50 flex items-center gap-2">
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
    // Mobile menu toggle
    const mobileMenuBtn = document.getElementById('mobile-menu-btn');
    const sidebar = document.getElementById('sidebar');
    const sidebarOverlay = document.getElementById('sidebar-overlay');
    const mainContent = document.getElementById('main-content');

    function toggleMobileMenu() {
        sidebar.classList.toggle('-translate-x-full');
        sidebarOverlay.classList.toggle('hidden');
    }

    mobileMenuBtn?.addEventListener('click', toggleMobileMenu);
    sidebarOverlay?.addEventListener('click', toggleMobileMenu);

    // Sidebar collapse toggle (Desktop only)
    const collapseBtn = document.getElementById('collapse-btn');
    const collapseIcon = document.getElementById('collapse-icon');
    const menuTriggers = document.querySelectorAll('[data-menu-target]');

    function toggleSidebarMenu(trigger) {
        if (!trigger) {
            return;
        }

        if (window.innerWidth >= 768 && sidebar.classList.contains('sidebar-collapsed')) {
            return;
        }

        const targetId = trigger.getAttribute('data-menu-target');
        const targetMenu = document.getElementById(targetId);
        if (!targetMenu) {
            return;
        }

        const shouldOpen = trigger.getAttribute('aria-expanded') !== 'true';

        menuTriggers.forEach((item) => {
            const itemTargetId = item.getAttribute('data-menu-target');
            const itemMenu = document.getElementById(itemTargetId);
            item.setAttribute('aria-expanded', 'false');
            itemMenu?.classList.remove('is-open');
        });

        trigger.setAttribute('aria-expanded', shouldOpen ? 'true' : 'false');
        targetMenu.classList.toggle('is-open', shouldOpen);
    }

    menuTriggers.forEach((trigger) => {
        trigger.addEventListener('click', function () {
            toggleSidebarMenu(trigger);
        });
    });

    function syncDesktopSidebarState() {
        if (window.innerWidth < 768) {
            mainContent.style.marginLeft = '0';
            sidebar.classList.remove('sidebar-collapsed');
            collapseIcon?.classList.remove('rotate-180');
            return;
        }

        const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
        sidebar.classList.toggle('sidebar-collapsed', isCollapsed);
        collapseBtn?.classList.toggle('is-collapsed', isCollapsed);
        mainContent.style.marginLeft = isCollapsed ? '4.5rem' : '16rem';
        if (collapseBtn) {
            collapseBtn.style.left = isCollapsed ? '3.5rem' : '15rem';
        }
    }

    collapseBtn?.addEventListener('click', function() {
        if (window.innerWidth < 768) {
            return;
        }

        const nextCollapsedState = !sidebar.classList.contains('sidebar-collapsed');
        localStorage.setItem('sidebarCollapsed', nextCollapsedState);
        syncDesktopSidebarState();
    });

    // Restore collapse state on page load
    window.addEventListener('DOMContentLoaded', function() {
        syncDesktopSidebarState();
    });

    // Close mobile menu on window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth >= 768) {
            sidebar.classList.remove('-translate-x-full');
            sidebarOverlay.classList.add('hidden');
            syncDesktopSidebarState();
            return;
        }

        mainContent.style.marginLeft = '0';
    });

    const toastElements = document.querySelectorAll('[data-toast]');

    function hideToast(toast) {
        if (!toast || toast.classList.contains('is-hiding')) {
            return;
        }

        toast.classList.add('is-hiding');
        window.setTimeout(() => {
            toast.remove();
        }, 260);
    }

    toastElements.forEach((toast, index) => {
        const closeButton = toast.querySelector('[data-toast-close]');

        window.setTimeout(() => {
            toast.classList.add('is-visible');
        }, 80 + (index * 90));

        window.setTimeout(() => {
            hideToast(toast);
        }, 4200 + (index * 300));

        closeButton?.addEventListener('click', () => hideToast(toast));
    });
</script>

</body>
</html>
