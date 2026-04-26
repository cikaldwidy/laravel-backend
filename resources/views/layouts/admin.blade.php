<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'Admin Panel')</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<div class="flex min-h-screen">
    <aside class="w-64 bg-white shadow-lg hidden md:block">
        <div class="p-6 font-bold text-xl border-b">
            Presensi Admin
        </div>

        <nav class="p-4 space-y-2">
            <a href="{{ route('admin.dashboard') }}"
               class="block p-3 rounded-lg {{ request()->routeIs('admin.dashboard') ? 'bg-blue-500 text-white' : 'hover:bg-gray-100' }}">
                Dashboard
            </a>

            <a href="{{ route('admin.users.index') }}"
               class="block p-3 rounded-lg {{ request()->routeIs('admin.users.*') ? 'bg-blue-500 text-white' : 'hover:bg-gray-100' }}">
                Kelola User
            </a>

            <a href="{{ route('admin.presensi.index') }}"
               class="block p-3 rounded-lg {{ request()->routeIs('admin.presensi.*') ? 'bg-blue-500 text-white' : 'hover:bg-gray-100' }}">
                Presensi
            </a>

            <a href="{{ route('admin.shifts.index') }}"
               class="block p-3 rounded-lg {{ request()->routeIs('admin.shifts.*') ? 'bg-blue-500 text-white' : 'hover:bg-gray-100' }}">
                Master Shift
            </a>

            <a href="{{ route('admin.user_shifts.index') }}"
               class="block p-3 rounded-lg {{ request()->routeIs('admin.user_shifts.*') ? 'bg-blue-500 text-white' : 'hover:bg-gray-100' }}">
                Jadwal Shift
            </a>

            <a href="{{ route('admin.biodata.index') }}"
               class="block p-3 rounded-lg {{ request()->routeIs('admin.biodata.*') ? 'bg-blue-500 text-white' : 'hover:bg-gray-100' }}">
                Biodata User
            </a>

            <a href="#"
               class="block p-3 rounded-lg hover:bg-gray-100">
                Data Wajah
            </a>

            <a href="{{ route('admin.settings.work.edit') }}"
               class="block p-3 rounded-lg {{ request()->routeIs('admin.settings.*') ? 'bg-blue-500 text-white' : 'hover:bg-gray-100' }}">
                Jam Kerja
            </a>
        </nav>
    </aside>

    <main class="flex-1">
        <div class="bg-white shadow px-6 py-4 flex justify-between items-center">
            <h1 class="text-lg font-bold">
                @yield('title')
            </h1>

            <div class="flex items-center gap-4">
                <span class="text-sm">
                    {{ auth()->user()->name }}
                </span>

                <form method="POST" action="/logout">
                    @csrf
                    <button class="bg-red-500 text-white px-3 py-1 rounded text-sm">
                        Logout
                    </button>
                </form>
            </div>
        </div>

        <div class="p-6">
            @yield('content')
        </div>
    </main>
</div>

</body>
</html>
