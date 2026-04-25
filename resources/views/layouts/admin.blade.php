<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<div class="flex min-h-screen">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-white shadow-lg hidden md:block">

        <div class="p-6 font-bold text-xl border-b">
            Presensi Admin
        </div>

        <nav class="p-4 space-y-2">

            <a href="/admin/dashboard"
               class="block p-3 rounded-lg bg-blue-500 text-white">
                Dashboard
            </a>

            <a href="/admin/users"
               class="block p-3 rounded-lg hover:bg-gray-100">
                👤 Kelola User
            </a>

            <a href="#"
               class="block p-3 rounded-lg hover:bg-gray-100">
                📊 Presensi
            </a>

            <a href="#"
               class="block p-3 rounded-lg hover:bg-gray-100">
                📷 Data Wajah
            </a>

            <a href="#"
               class="block p-3 rounded-lg hover:bg-gray-100">
                ⚙️ Pengaturan
            </a>

        </nav>

    </aside>

    <!-- MAIN -->
    <main class="flex-1">

        <!-- TOPBAR -->
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

        <!-- CONTENT -->
        <div class="p-6">
            @yield('content')
        </div>

    </main>

</div>

</body>
</html>