@extends('layouts.app')

@section('title', 'Daftar Akun')

@section('content')
<style>
  .sph-auth-bg {
    background:
      radial-gradient(circle at top left, rgba(220, 38, 38, 0.12), transparent 28rem),
      radial-gradient(circle at bottom right, rgba(37, 99, 235, 0.16), transparent 30rem),
      #f8fafc;
  }

  .sph-auth-card {
    border-top: 4px solid #dc2626;
    box-shadow: 0 18px 45px rgba(15, 23, 42, 0.12);
  }

  .sph-accent-line {
    width: 4.5rem;
    height: 0.25rem;
    border-radius: 999px;
    background: linear-gradient(90deg, #dc2626 0%, #2563eb 100%);
  }
</style>

<div class="sph-auth-bg flex min-h-[100dvh] items-center justify-center px-5 py-8 md:px-12">
  <div class="sph-auth-card w-full max-w-md bg-white rounded-xl p-6">
    <div class="flex items-center gap-3 mb-4">
      <img src="{{ asset('img/logo.jpeg') }}" alt="Logo SPH" class="w-16 h-16 object-contain rounded-xl border border-slate-100">
      <div>
        <h3 class="text-xl font-bold text-slate-800 tracking-[.5px]">
          Buat Akun
        </h3>
        <div class="sph-accent-line mt-2"></div>
      </div>
    </div>

    <p class="text-sm text-slate-500 mb-5">
      Daftarkan akun Anda untuk melanjutkan absensi dan pendaftaran wajah.
    </p>

    <form method="POST" action="{{ route('register.store') }}" class="space-y-4">
      @csrf

      <div>
        <label class="text-sm text-slate-700">Nama Lengkap <span class="text-red-600">*</span></label>
        <input
          type="text"
          name="name"
          value="{{ old('name') }}"
          placeholder="Masukkan nama lengkap"
          class="w-full mt-1 px-4 py-3 rounded-lg border border-slate-200 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20"
        >
        @error('name')
          <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label class="text-sm text-slate-700">Username <span class="text-red-600">*</span></label>
        <input
          type="text"
          name="username"
          value="{{ old('username') }}"
          placeholder="Contoh: ardanahalim"
          class="w-full mt-1 px-4 py-3 rounded-lg border border-slate-200 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20"
        >
        @error('username')
          <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label class="text-sm text-slate-700">NIP <span class="text-red-600">*</span></label>
        <input
          type="text"
          name="nip"
          value="{{ old('nip') }}"
          placeholder="Masukkan NIP"
          class="w-full mt-1 px-4 py-3 rounded-lg border border-slate-200 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20"
        >
        @error('nip')
          <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label class="text-sm text-slate-700">Email <span class="text-red-600">*</span></label>
        <input
          type="email"
          name="email"
          value="{{ old('email') }}"
          placeholder="Masukkan email"
          class="w-full mt-1 px-4 py-3 rounded-lg border border-slate-200 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20"
        >
        @error('email')
          <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label class="text-sm text-slate-700">Password <span class="text-red-600">*</span></label>
        <div class="relative mt-1">
          <input
            id="registerPassword"
            type="password"
            name="password"
            placeholder="Minimal 6 karakter"
            class="w-full px-4 py-3 pr-12 rounded-lg border border-slate-200 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20"
          >
          <button
            type="button"
            data-toggle-password="registerPassword"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-blue-700"
            aria-label="Tampilkan kata sandi"
            aria-pressed="false"
          >
            <i class="fa-solid fa-eye"></i>
          </button>
        </div>
        @error('password')
          <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
        @enderror
      </div>

      <div>
        <label class="text-sm text-slate-700">Konfirmasi Password <span class="text-red-600">*</span></label>
        <div class="relative mt-1">
          <input
            id="registerPasswordConfirmation"
            type="password"
            name="password_confirmation"
            placeholder="Ulangi password"
            class="w-full px-4 py-3 pr-12 rounded-lg border border-slate-200 focus:outline-none focus:border-blue-600 focus:ring-2 focus:ring-blue-600/20"
          >
          <button
            type="button"
            data-toggle-password="registerPasswordConfirmation"
            class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-blue-700"
            aria-label="Tampilkan kata sandi"
            aria-pressed="false"
          >
            <i class="fa-solid fa-eye"></i>
          </button>
        </div>
      </div>

      <button
        type="submit"
        class="w-full bg-blue-700 hover:bg-blue-800 text-white font-semibold py-3 rounded-md shadow-lg shadow-blue-700/20 transition text-sm tracking-[.5px]">
        DAFTAR
      </button>
    </form>

    <p class="text-center text-sm text-slate-400 mt-6">
      Sudah punya akun?
      <a href="{{ route('login') }}" class="text-red-600 font-semibold hover:text-red-700">Login</a>
    </p>
  </div>
</div>

<script>
  document.querySelectorAll('[data-toggle-password]').forEach((button) => {
    const input = document.getElementById(button.dataset.togglePassword);
    const icon = button.querySelector('i');

    if (!input || !icon) return;

    button.addEventListener('click', () => {
      const shouldShow = input.type === 'password';

      input.type = shouldShow ? 'text' : 'password';
      button.setAttribute('aria-pressed', shouldShow ? 'true' : 'false');
      button.setAttribute('aria-label', shouldShow ? 'Sembunyikan kata sandi' : 'Tampilkan kata sandi');
      icon.classList.toggle('fa-eye', !shouldShow);
      icon.classList.toggle('fa-eye-slash', shouldShow);
    });
  });
</script>

@endsection
