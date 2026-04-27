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
        <nav id="sidebar-nav" class="md:px-2 sidebar-scroll overflow-y-auto px-4 py-3 space-y-1" style="height: calc(100vh - 5rem);">
            <a href="{{ route('admin.dashboard') }}"
               class="sidebar-item relative flex items-center gap-3 p-3 rounded-md text-gray-300 {{ request()->routeIs('admin.dashboard') ? 'active text-white bg-gray-700' : 'hover:bg-gray-700' }}">
                <i class="fas fa-chart-line w-5 flex-shrink-0 text-center"></i>
                <span class="sidebar-text">Dashboard</span>
                <span class="tooltip">Dashboard</span>
            </a>

            <a href="{{ route('admin.users.index') }}"
               class="sidebar-item relative flex items-center gap-3 p-3 rounded-md text-gray-300 {{ request()->routeIs('admin.users.*') ? 'active text-white bg-gray-700' : 'hover:bg-gray-700' }}">
                <i class="fas fa-users w-5 flex-shrink-0 text-center"></i>
                <span class="sidebar-text">Kelola User</span>
                <span class="tooltip">Kelola User</span>
            </a>

            <a href="{{ route('admin.units.index') }}"
               class="sidebar-item relative flex items-center gap-3 p-3 rounded-md text-gray-300 {{ request()->routeIs('admin.units.*') ? 'active text-white bg-gray-700' : 'hover:bg-gray-700' }}">
                <i class="fas fa-building w-5 flex-shrink-0 text-center"></i>
                <span class="sidebar-text">Master Unit</span>
                <span class="tooltip">Master Unit</span>
            </a>

            <a href="{{ route('admin.presensi.index') }}"
               class="sidebar-item relative flex items-center gap-3 p-3 rounded-md text-gray-300 {{ request()->routeIs('admin.presensi.*') ? 'active text-white bg-gray-700' : 'hover:bg-gray-700' }}">
                <i class="fas fa-clipboard-check w-5 flex-shrink-0 text-center"></i>
                <span class="sidebar-text">Presensi</span>
                <span class="tooltip">Presensi</span>
            </a>

            <a href="{{ route('admin.shifts.index') }}"
               class="sidebar-item relative flex items-center gap-3 p-3 rounded-md text-gray-300 {{ request()->routeIs('admin.shifts.*') ? 'active text-white bg-gray-700' : 'hover:bg-gray-700' }}">
                <i class="fas fa-clock w-5 flex-shrink-0 text-center"></i>
                <span class="sidebar-text">Master Shift</span>
                <span class="tooltip">Master Shift</span>
            </a>

            <a href="{{ route('admin.user_shifts.index') }}"
               class="sidebar-item relative flex items-center gap-3 p-3 rounded-md text-gray-300 {{ request()->routeIs('admin.user_shifts.*') ? 'active text-white bg-gray-700' : 'hover:bg-gray-700' }}">
                <i class="fas fa-calendar-alt w-5 flex-shrink-0 text-center"></i>
                <span class="sidebar-text">Jadwal Shift</span>
                <span class="tooltip">Jadwal Shift</span>
            </a>

            <a href="{{ route('admin.biodata.index') }}"
               class="sidebar-item relative flex items-center gap-3 p-3 rounded-md text-gray-300 {{ request()->routeIs('admin.biodata.*') ? 'active text-white bg-gray-700' : 'hover:bg-gray-700' }}">
                <i class="fas fa-id-card w-5 flex-shrink-0 text-center"></i>
                <span class="sidebar-text">Biodata User</span>
                <span class="tooltip">Biodata User</span>
            </a>

            <a href="{{ route('admin.leave_requests.index') }}"
               class="sidebar-item relative flex items-center gap-3 p-3 rounded-md text-gray-300 {{ request()->routeIs('admin.leave_requests.*') ? 'active text-white bg-gray-700' : 'hover:bg-gray-700' }}">
                <i class="fas fa-file-alt w-5 flex-shrink-0 text-center"></i>
                <span class="sidebar-text">Perizinan</span>
                <span class="tooltip">Perizinan</span>
            </a>

            <a href="{{ route('admin.histories.index') }}"
               class="sidebar-item relative flex items-center gap-3 p-3 rounded-md text-gray-300 {{ request()->routeIs('admin.histories.*') ? 'active text-white bg-gray-700' : 'hover:bg-gray-700' }}">
                <i class="fas fa-history w-5 flex-shrink-0 text-center"></i>
                <span class="sidebar-text">Riwayat</span>
                <span class="tooltip">Riwayat</span>
            </a>

            <a href="{{ route('admin.announcements.index') }}"
               class="sidebar-item relative flex items-center gap-3 p-3 rounded-md text-gray-300 {{ request()->routeIs('admin.announcements.*') ? 'active text-white bg-gray-700' : 'hover:bg-gray-700' }}">
                <i class="fas fa-bullhorn w-5 flex-shrink-0 text-center"></i>
                <span class="sidebar-text">Pengumuman</span>
                <span class="tooltip">Pengumuman</span>
            </a>

            <a href="{{ route('admin.reports.index') }}"
               class="sidebar-item relative flex items-center gap-3 p-3 rounded-md text-gray-300 {{ request()->routeIs('admin.reports.*') ? 'active text-white bg-gray-700' : 'hover:bg-gray-700' }}">
                <i class="fas fa-chart-bar w-5 flex-shrink-0 text-center"></i>
                <span class="sidebar-text">Laporan</span>
                <span class="tooltip">Laporan</span>
            </a>

            <a href="{{ route('admin.settings.work.edit') }}"
               class="sidebar-item relative flex items-center gap-3 p-3 rounded-md text-gray-300 {{ request()->routeIs('admin.settings.*') ? 'active text-white bg-gray-700' : 'hover:bg-gray-700' }}">
                <i class="fas fa-cog w-5 flex-shrink-0 text-center"></i>
                <span class="sidebar-text">Jam Kerja</span>
                <span class="tooltip">Jam Kerja</span>
            </a>
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
</script>

</body>
</html>
