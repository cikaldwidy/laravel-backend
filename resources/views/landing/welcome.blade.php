@extends('layouts.app')

@section('title', 'Welcome')

@section('content')

  <div class="flex flex-col min-h-screen relative overflow-hidden">

    <!-- Header / Logo -->
    <div class="flex flex-col items-center text-center gap-3 px-8 md:px-14 lg:px-20 pt-20 md:pt-6 z-10 md:flex-row md:text-left">
      <img src="{{ asset('img/logo.jpeg') }}" class=" w-20 h-auto md:w-12 md:h-12 rounded-xl object-cover shadow">
      <div class="leading-tight">
        <p class="text-md md:text-[10px] font-semibold text-gray-400 tracking-widest uppercase">RUMAH SAKIT UMUM</p>
        <p class="text-xl md:text-sm font-extrabold text-blue-700 tracking-wide">SATITI PRIMA HUSADA</p>
        <p class="text-md md:text-[10px] font-bold text-red-500 tracking-widest uppercase">Tulungagung</p>
      </div>
    </div>

    <!-- Hero Section -->
    <div class="flex flex-1 flex-col md:flex-row items-stretch px-8 md:px-14 lg:px-20 pt-14 pb-20 md:pt-8 md:pb-10 relative z-10">

      <!-- Left Content -->
      <div class="md:w-[45%] flex flex-col justify-start md:justify-center text-center md:text-left items-center md:items-start mt-6 md:mt-0">
        <h1 class="text-3xl md:text-5xl font-black text-blue-700 leading-none tracking-[2px] mb-4">
          SISTEM ABSENSI
        </h1>
        <h2 class="text-3xl md:text-5xl font-black text-red-500 leading-none tracking-[2px] mb-8">
          RSU SATITI PRIMA HUSADA
        </h2>
        <p class="text-md text-gray-500 mb-10 max-w-xs mx-auto md:mx-0 text-justify">
          Sistem absensi pegawai yang dirancang untuk mendukung
          pencatatan kehadiran secara rapi, cepat, dan terpusat.
        </p>
        <div class="flex justify-center md:justify-start w-full">
          <a href="{{ route('login') }}"
            class="inline-flex items-center gap-3 bg-blue-600 hover:bg-blue-700 transition-colors
                    text-white text-md font-bold tracking-widest uppercase
                    px-10 py-4 rounded-full shadow-lg">
            GET STARTED
            <span class="w-6 h-6 rounded-full bg-white flex items-center justify-center text-sm text-blue-600">
              <i class="fa-solid fa-arrow-right"></i>
            </span>
          </a>
        </div>
      </div>

      <!-- Right Illustration -->
      <div class="hidden md:flex md:w-[55%] justify-center items-center relative">

        <img
          src="{{ asset('img/img-welcome.png') }}"
          alt="Ilustrasi Absensi"
          class="md:w-70 lg:w-[420px] object-contain relative z-10"
        >

        <!-- Fade atas -->
        <div class="absolute top-0 left-0 right-0 h-16 z-20 pointer-events-none"
          style="background: linear-gradient(to bottom, #ffff 0%, transparent 100%);">
        </div>

        <!-- Fade bawah -->
        <div class="absolute bottom-0 left-0 right-0 h-24 z-20 pointer-events-none"
          style="background: linear-gradient(to top, #ffff 0%, transparent 100%);">
        </div>

      </div>

    </div>

  </div>

@endsection
