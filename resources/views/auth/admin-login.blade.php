@extends('layouts.app')

@section('title', 'Login Admin')

@section('content')
<style>
    .admin-login-page {
        background: #ffffff;
    }

    .admin-login-shell {
        box-shadow: 0 28px 70px rgba(15, 23, 42, 0.22);
    }

    .admin-login-input:focus ~ .admin-login-icon,
    .admin-login-input:not(:placeholder-shown) ~ .admin-login-icon {
        color: #2563eb;
    }

    .admin-float-label {
        left: 1.55rem;
        top: 50%;
        max-width: calc(100% - 5.5rem);
        color: #64748b;
        line-height: 1;
        opacity: 0;
        pointer-events: none;
        transform: translateY(-50%);
        transition: 160ms ease;
    }

    .admin-login-input::placeholder {
        color: #64748b;
        opacity: 1;
        transition: 120ms ease;
    }

    .admin-login-input:focus::placeholder,
    .admin-login-input:not(:placeholder-shown)::placeholder {
        opacity: 0;
    }

    .admin-login-input:focus ~ .admin-float-label,
    .admin-login-input:not(:placeholder-shown) ~ .admin-float-label {
        top: -0.05rem;
        color: #2563eb;
        opacity: 1;
        transform: translateY(-50%);
    }

    .admin-hero-panel {
        background-image:
            linear-gradient(90deg, rgba(30, 64, 175, 0.70), rgba(14, 116, 144, 0.50)),
            url("{{ asset('img/login-admin.png') }}");
        background-size: cover;
        background-position: center;
    }
</style>

<div class="admin-login-page min-h-[100dvh] flex items-center justify-center px-4 py-8 sm:px-6 lg:px-10">
    <div class="admin-login-shell w-full overflow-hidden rounded-xl bg-white grid grid-cols-1 lg:grid-cols-[0.92fr_1.18fr]">
        <section class="flex min-h-[560px] items-center justify-center px-6 py-10 sm:px-10 lg:px-16">
            <div class="w-full max-w-md">
                <div class="mb-9 flex justify-center">
                    <div class="relative h-20 w-20">
                        <div class="absolute inset-0 rounded-3xl bg-gradient-to-br from-blue-700 to-cyan-400 opacity-20 blur-xl"></div>
                        <img src="{{ asset('img/logo.jpeg') }}" alt="Logo SPH" class="relative h-20 w-20 rounded-2xl border border-gray-100 object-contain shadow-sm">
                    </div>
                </div>

                <form method="POST" action="/admin/login" class="space-y-5">
                    @csrf

                    <div>
                        <div class="relative">
                            <input
                                id="email"
                                type="email"
                                name="email"
                                value="{{ old('email') }}"
                                placeholder="Email"
                                class="admin-login-input h-12 w-full rounded-full border border-gray-300 px-6 pr-12 text-sm text-gray-700 outline-none transition focus:border-2 focus:border-blue-500 focus:ring-4 focus:ring-blue-600/10"
                                required
                                autofocus
                            >
                            <label for="email" class="admin-float-label absolute z-10 bg-white px-2 text-xs font-semibold">
                                Email
                            </label>
                            <i class="admin-login-icon fa-solid fa-envelope absolute right-6 top-1/2 -translate-y-1/2 text-gray-500 transition"></i>
                        </div>
                        @error('email')
                            <div class="mt-3 px-2 text-xs text-red-700">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div>
                        <div class="relative">
                            <input
                                id="password"
                                type="password"
                                name="password"
                                placeholder="Kata Sandi"
                                class="admin-login-input h-12 w-full rounded-full border border-gray-300 px-6 pr-12 text-sm text-gray-700 outline-none transition focus:border-2 focus:border-blue-500 focus:ring-4 focus:ring-blue-600/10"
                                required
                            >
                            <label for="password" class="admin-float-label absolute z-10 bg-white px-2 text-xs font-semibold">
                                Kata Sandi
                            </label>
                            <button
                                type="button"
                                id="togglePassword"
                                class="admin-login-icon absolute right-5 top-1/2 -translate-y-1/2 text-gray-500 transition hover:text-blue-700"
                                aria-label="Tampilkan kata sandi"
                                aria-pressed="false"
                            >
                                <i id="togglePasswordIcon" class="fa-solid fa-eye-slash"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="mt-3 px-2 text-xs text-red-700">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    <div class="flex items-center justify-end">
                        <a href="{{ route('login') }}" class="hover:underline text-sm font-medium text-gray-500 transition hover:text-blue-700">
                            Login User
                        </a>
                    </div>

                    <button
                        type="submit"
                        class="h-14 w-full rounded-full bg-gradient-to-r from-blue-700 via-blue-600 to-cyan-500 text-sm font-bold tracking-wide text-white shadow-lg shadow-blue-700/25 transition hover:brightness-105 focus:outline-none focus:ring-4 focus:ring-blue-600/20">
                        MASUK
                    </button>

                </form>

                <div class="mt-8 text-center">
                    <a href="{{ route('landing') }}" class="text-sm text-gray-500 transition hover:text-blue-700 hover:underline">
                        Kembali ke halaman utama
                    </a>
                </div>
            </div>
        </section>

        <section class="admin-hero-panel relative hidden min-h-[560px] items-center justify-center overflow-hidden px-10 text-center text-white lg:flex">
            <div class="absolute inset-0 bg-gray-950/5"></div>
            <div class="relative max-w-md">
                <p class="text-sm font-semibold uppercase tracking-[0.18em]">Selamat Datang di</p>
                <h1 class="mt-5 text-5xl font-black tracking-wide">PRESENSI RS</h1>
                <p class="mt-2 text-sm font-semibold uppercase tracking-[0.3em] text-white/85">
                    Sistem Biometrik Kehadiran
                </p>
                <p class="mt-7 text-sm leading-6 text-white/90">
                    Portal admin untuk memantau presensi, mengelola akun pegawai, dan mengatur jadwal kerja rumah sakit.
                </p>
            </div>
        </section>
    </div>
</div>

<script>
    const passwordInput = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');
    const togglePasswordIcon = document.getElementById('togglePasswordIcon');

    if (passwordInput && togglePassword && togglePasswordIcon) {
        togglePassword.addEventListener('click', () => {
            const shouldShow = passwordInput.type === 'password';

            passwordInput.type = shouldShow ? 'text' : 'password';
            togglePassword.setAttribute('aria-pressed', shouldShow ? 'true' : 'false');
            togglePassword.setAttribute('aria-label', shouldShow ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
            togglePasswordIcon.classList.toggle('fa-eye', shouldShow);
            togglePasswordIcon.classList.toggle('fa-eye-slash', !shouldShow);
        });
    }
</script>
@endsection
