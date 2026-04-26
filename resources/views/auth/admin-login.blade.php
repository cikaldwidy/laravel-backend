@extends('layouts.app')

@section('title', 'Login Admin')

@section('content')
<div class="min-h-screen bg-gray-100 flex items-center justify-center px-4 py-10">
    <div class="w-full max-w-5xl bg-white rounded-lg shadow-xl overflow-hidden grid grid-cols-1 lg:grid-cols-2">
        <div class="bg-gray-900 text-white p-8 lg:p-10 flex flex-col justify-between min-h-[420px]">
            <div>
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-lg bg-blue-600 mb-6">
                    <i class="fa-solid fa-shield-halved text-xl"></i>
                </div>

                <h1 class="text-3xl font-bold leading-tight">
                    Admin Presensi
                </h1>
                <p class="text-gray-300 mt-3 text-sm leading-6 max-w-md">
                    Masuk untuk memantau data presensi, mengelola user, dan mengatur jam kerja pegawai.
                </p>
            </div>

            <div class="grid grid-cols-3 gap-3 mt-8">
                <div class="bg-white/10 border border-white/10 rounded-lg p-4">
                    <p class="text-2xl font-bold">01</p>
                    <p class="text-xs text-gray-300 mt-1">Dashboard</p>
                </div>
                <div class="bg-white/10 border border-white/10 rounded-lg p-4">
                    <p class="text-2xl font-bold">02</p>
                    <p class="text-xs text-gray-300 mt-1">Presensi</p>
                </div>
                <div class="bg-white/10 border border-white/10 rounded-lg p-4">
                    <p class="text-2xl font-bold">03</p>
                    <p class="text-xs text-gray-300 mt-1">Jam Kerja</p>
                </div>
            </div>
        </div>

        <div class="p-8 lg:p-10 flex items-center">
            <div class="w-full">
                <div class="mb-8">
                    <p class="text-sm font-semibold text-blue-600">PORTAL ADMIN</p>
                    <h2 class="text-2xl font-bold text-gray-900 mt-2">Login Admin</h2>
                    <p class="text-sm text-gray-500 mt-2">
                        Gunakan akun dengan role admin untuk melanjutkan.
                    </p>
                </div>

                @if($errors->any())
                    <div class="mb-5 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form method="POST" action="/admin/login" class="space-y-5">
                    @csrf

                    <div>
                        <label for="email" class="block text-sm font-semibold text-gray-700 mb-2">
                            Email
                        </label>
                        <div class="relative">
                            <i class="fa-solid fa-envelope absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="admin@email.com"
                                class="w-full border border-gray-200 rounded-lg pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required
                                autofocus
                            >
                        </div>
                        @error('email')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="password" class="block text-sm font-semibold text-gray-700 mb-2">
                            Password
                        </label>
                        <div class="relative">
                            <i class="fa-solid fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"></i>
                            <input
                                id="password"
                                type="password"
                                name="password"
                                placeholder="Masukkan password"
                                class="w-full border border-gray-200 rounded-lg pl-10 pr-4 py-3 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                required
                            >
                        </div>
                        @error('password')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <button
                        type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition shadow-sm">
                        Login Admin
                    </button>
                </form>

                <div class="mt-6 flex items-center justify-between text-sm">
                    <a href="{{ route('landing') }}" class="text-gray-500 hover:text-gray-700">
                        Kembali
                    </a>
                    <a href="{{ route('login') }}" class="text-blue-600 font-semibold hover:underline">
                        Login User
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
