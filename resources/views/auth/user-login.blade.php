@extends('layouts.app')

@section('title', 'Login')

@section('content')

<div class="flex flex-1 items-center justify-center px-8 md:px-14 lg:px-20 py-10 md:py-14">

  <div class="w-full bg-white rounded-md shadow-lg p-2 md:p-5 flex flex-col md:flex-row gap-8">

    <!-- LEFT -->
    <div class="md:w-1/2 flex flex-col justify-center items-center text-center border-b md:border-b-0 md:border-r border-gray-100 pb-6 md:pb-0 md:pr-8">
      <img src="{{ asset('img/img-login.png') }}" class="w-[250px] h-auto mb-4">
      <h2 class="text-3xl font-bold text-gray-700 tracking-[.5px]">
        Selamat Datang
      </h2>
      <p class="text-gray-400 text-sm mt-2 max-w-xs">
        Masuk ke akun Anda untuk mengakses sistem absensi pegawai.
      </p>
    </div>

    <!-- RIGHT (FORM) -->
    <div class="md:w-1/2">

      <h3 class="text-xl font-bold text-gray-700 mb-3 tracking-[.5px]">
        Login Akun
      </h3>

      <p class="text-sm text-gray-400 mb-5">
        Login untuk melanjutkan absensi atau pendaftaran wajah tanpa perlu masuk ulang setiap saat.
      </p>

      <form method="POST" action="{{ route('login') }}" class="space-y-4">
        @csrf
        <input type="hidden" name="redirect_to" value="{{ request('redirect_to') }}">

        <!-- NIP / USERNAME -->
        <div>
          <label class="text-sm text-gray-700">NIP / Username <span class="text-red-500">*</span></label>
          <input
            type="text"
            name="login"
            value="{{ old('login') }}"
            placeholder="Masukkan NIP atau username"
            class="w-full mt-1 px-4 py-3 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
          >
          @error('login')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
          @enderror
        </div>

        <!-- PASSWORD -->
        <div>
          <label class="text-sm text-gray-700">Password <span class="text-red-500">*</span></label>
          <div class="relative mt-1">
            <input
              type="password"
              name="password"
              placeholder="Masukkan password"
              class="w-full px-4 py-3 rounded-lg border border-gray-200 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
            <i class="fa-solid fa-eye absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 cursor-pointer"></i>
          </div>
          @error('password')
            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
          @enderror
        </div>

        <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
          <label class="inline-flex items-center gap-2 text-sm text-gray-500">
            <input
              type="checkbox"
              name="remember"
              value="1"
              @checked(old('remember'))
              class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
            >
            <span>Ingat saya di perangkat ini</span>
          </label>

          <a href="{{ route('login', ['redirect_to' => 'face.enroll']) }}" class="text-sm text-blue-600 font-semibold hover:underline">
            Pendaftaran wajah
          </a>
        </div>

        <!-- BUTTON -->
        <button
          type="submit"
          class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-md hover:shadow-lg transition text-sm tracking-[.5px]">
          LOGIN
        </button>

      </form>

      <!-- FOOT -->
      <p class="text-center text-sm text-gray-400 mt-6">
        Belum punya akun?
        <a href="#" class="text-blue-600 font-semibold">Hubungi Admin</a>
      </p>

    </div>

  </div>

</div>

@endsection
