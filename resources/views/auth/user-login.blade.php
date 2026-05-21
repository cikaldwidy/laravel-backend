@extends('layouts.app')

@section('title', 'Login')

@section('content')
@php
  $turnstileEnabled = (bool) config('services.turnstile.enabled')
    && filled(config('services.turnstile.site_key'))
    && filled(config('services.turnstile.secret_key'));
  $turnstileSiteKey = config('services.turnstile.site_key');
@endphp
<style>
  .sph-auth-bg {
    background:
      radial-gradient(circle at top left, rgba(220, 38, 38, 0.12), transparent 28rem),
      radial-gradient(circle at bottom right, rgba(37, 99, 235, 0.16), transparent 30rem),
      #f8fafc;
  }

  .sph-auth-card {
    border-top: 4px solid #2563eb;
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.12);
  }
</style>

@if($turnstileEnabled && $turnstileSiteKey)
  <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
@endif

<div class="sph-auth-bg flex min-h-[100dvh] flex-1 items-center justify-center px-5 py-8 md:px-14 lg:px-20">

  <div class="sph-auth-card w-full max-w-5xl bg-white rounded-xl p-4 md:p-6 flex flex-col md:flex-row gap-8">

    <!-- LEFT -->
    <div class="md:w-1/2 flex flex-col justify-center items-center text-center pb-6 md:pb-0 md:pr-8">
      <img src="{{ asset('img/logo.jpeg') }}" alt="Logo SPH" class="w-24 h-24 object-contain mb-4 rounded-xl border border-slate-100">
      <img src="{{ asset('img/img-login.png') }}" class="w-[340px] max-w-full h-auto">
    </div>

    <!-- RIGHT (FORM) -->
    <div class="md:w-1/2">

      <h3 class="text-xl font-bold text-slate-800 mb-3 tracking-[.5px]">
        Login Akun
      </h3>

      <p class="text-sm text-slate-500 mb-5">
        Login untuk melanjutkan absensi atau pendaftaran wajah tanpa perlu masuk ulang setiap saat.
      </p>

      <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="redirect_to" value="{{ request('redirect_to') }}">

        <!-- NIP / USERNAME -->
        <div>
          <label class="text-sm text-slate-700">NIP / Username <span class="text-red-600">*</span></label>
          <input
            type="text"
            name="login"
            value="{{ old('login') }}"
            placeholder="Masukkan NIP atau username"
            class="w-full mt-1 px-4 py-3 rounded-lg border border-slate-200 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20"
          >
          @error('login')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
          @enderror
        </div>

        <!-- PASSWORD -->
        <div>
          <label class="text-sm text-slate-700">Password <span class="text-red-600">*</span></label>
          <div class="relative mt-1">
            <input
              id="password"
              type="password"
              name="password"
              placeholder="Masukkan password"
              class="w-full px-4 py-3 pr-12 rounded-lg border border-slate-200 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20"
            >
            <button
              type="button"
              id="togglePassword"
              class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-blue-700"
              aria-label="Tampilkan kata sandi"
              aria-pressed="false"
            >
              <i id="togglePasswordIcon" class="fa-solid fa-eye"></i>
            </button>
          </div>
          @error('password')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
          @enderror
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <label class="inline-flex items-center gap-2 text-sm text-slate-500">
            <input
              type="checkbox"
              name="remember"
              value="1"
              @checked(old('remember'))
              class="h-4 w-4 rounded border-slate-300 text-blue-700 focus:ring-blue-600"
            >
            <span>Ingat saya di perangkat ini</span>
          </label>

          <a href="{{ route('login', ['redirect_to' => 'face.enroll']) }}" class="underline text-sm text-blue-600 font-semibold hover:text-blue-700 hover:underline">
            Pendaftaran wajah
          </a>
        </div>

        @if($turnstileEnabled && $turnstileSiteKey)
          <div>
            <div
              class="cf-turnstile"
              data-sitekey="{{ $turnstileSiteKey }}"
              data-theme="light"
              data-action="user-login"
            ></div>
            @error('cf-turnstile-response')
              <p class="text-red-500 text-xs mt-2">{{ $message }}</p>
            @enderror
          </div>
        @endif

        <!-- BUTTON -->
        <button
          type="submit"
          class="w-full bg-blue-700 hover:bg-blue-800 text-white font-semibold py-3 rounded-md shadow-lg shadow-blue-700/20 transition text-sm tracking-[.5px]">
          LOGIN
        </button>

      </form>

      <!-- FOOT -->
      <p class="text-center text-sm text-slate-400 mt-6">
        Belum punya akun?
        <a href="{{ route('register') }}" class="text-blue-600 font-semibold hover:text-blue-700 underline">Hubungi admin</a>
      </p>

    </div>

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
      togglePasswordIcon.classList.toggle('fa-eye', !shouldShow);
      togglePasswordIcon.classList.toggle('fa-eye-slash', shouldShow);
    });
  }
</script>

@endsection
