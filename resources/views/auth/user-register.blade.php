@extends('layouts.app')

@section('title', 'Akun Pegawai')

@section('content')
@php
  $supportWhatsapp = preg_replace('/\D+/', '', (string) config('services.support.whatsapp'));
  $supportWhatsapp = str_starts_with($supportWhatsapp, '0') ? '62' . substr($supportWhatsapp, 1) : $supportWhatsapp;
  $supportWhatsappUrl = 'https://wa.me/' . $supportWhatsapp . '?text=' . rawurlencode('Halo customer service, saya ingin dibantu untuk pembuatan akun pegawai.');
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

<div class="sph-auth-bg flex min-h-[100dvh] items-center justify-center px-5 py-8 md:px-12">
  <div class="sph-auth-card w-full max-w-xl rounded-xl bg-white p-5 md:p-8">

    <div class="p-3">
      <h2 class="inline-flex items-center gap-2 text-xl font-semibold text-slate-800">
        <span>Informasi Pendaftaran</span>
        <i class="fa-solid fa-bullhorn text-base text-blue-600"></i>
      </h2>
      <p class="mt-3 text-sm leading-7 text-slate-600">
        Akun login pegawai dibuat langsung oleh admin rumah sakit. Hal ini untuk memastikan bahwa data kepegawaian, NIP, unit kerja, dan hak akses tetap terverifikasi.
      </p>
      <p class="mt-3 text-sm leading-7 text-slate-600">
        Jika Anda belum memiliki akun, silakan hubungi admin atau bagian yang mengelola sistem kepegawaian untuk dibuatkan akun terlebih dahulu.
      </p>
      <div class="mt-6 grid grid-cols-1 gap-3 sm:grid-cols-[auto_auto_1fr] sm:items-center">  
      <a href="{{ route('login') }}" class="inline-flex items-center justify-center text-sm font-semibold text-blue-600 underline underline-offset-4 hover:text-blue-800">
        Kembali ke Login
      </a>
      <a href="{{ route('landing') }}" class="inline-flex items-center justify-center text-sm font-semibold text-blue-600 underline underline-offset-4 hover:text-blue-800">
        Ke Halaman Utama
      </a>
      <a
        href="{{ $supportWhatsappUrl }}"
        target="_blank"
        rel="noopener noreferrer"
        class="inline-flex min-h-[3rem] items-center justify-center gap-2 text-sm font-semibold text-emerald-600 shadow-sm sm:justify-self-end hover:text-emerald-800"
      >
        <i class="fa-brands fa-whatsapp text-base"></i>
        <span>Hubungi Via WA</span>
      </a>
    </div> 
    </div>

    
  </div>
</div>
@endsection
