@extends('layouts.app')

@section('title', 'Pendaftaran Berhasil')

@section('content')
<div class="px-8 md:px-14 lg:px-20 mt-5">
    <div class="relative p-5">
        <div class="absolute top-10 left-0 w-full h-[2px] bg-gray-300"></div>

        <div class="flex justify-between relative z-10 text-[8px] md:text-sm text-gray-500 tracking-[1px]">
            <div class="flex flex-col items-center">
                <div class="w-10 h-10 flex items-center justify-center rounded-full bg-blue-600 text-white font-bold">1</div>
                <span class="mt-1 text-blue-600 font-semibold">LOGIN</span>
            </div>

            <div class="flex flex-col items-center">
                <div class="w-10 h-10 flex items-center justify-center rounded-full bg-blue-600 text-white font-bold">2</div>
                <span class="mt-1 text-blue-600 font-semibold">PENDAFTARAN WAJAH</span>
            </div>

            <div class="flex flex-col items-center">
                <div class="w-10 h-10 flex items-center justify-center rounded-full bg-blue-600 text-white font-bold">3</div>
                <span class="mt-1 text-blue-600 font-semibold">VERIFIKASI</span>
            </div>

            <div class="flex flex-col items-center">
                <div class="w-10 h-10 flex items-center justify-center rounded-full bg-blue-600 text-white font-bold">4</div>
                <span class="mt-1 text-blue-600 font-semibold">BERHASIL</span>
            </div>
        </div>
    </div>
</div>

<div class="flex flex-1 items-center justify-center px-8 md:px-14 lg:px-20 py-7">
    <div class="w-full bg-white rounded-md shadow-lg p-2 md:p-5 flex flex-col md:flex-row gap-8">
        <div class="md:w-1/2 flex flex-col justify-center items-center text-center border-b md:border-b-0 md:border-r border-gray-100 pb-6 md:pb-0 md:pr-8">
            <img src="{{ asset('img/img-login.jpg') }}" class="w-[200px] h-auto mb-4">
            <h2 class="text-3xl font-bold text-gray-700 tracking-[.5px]">
                Verifikasi Tuntas
            </h2>
            <p class="text-gray-400 text-sm mt-2 max-w-xs">
                Data wajah sudah tersimpan dan akun Anda siap digunakan untuk proses presensi.
            </p>
        </div>

        <div class="md:w-1/2 flex items-center justify-center">
            <div class="flex flex-col items-center text-center py-6">
                <div class="relative w-28 h-28 rounded-full border-4 border-blue-600 flex items-center justify-center">
                    <i class="fa-solid fa-check text-5xl text-blue-600"></i>
                    <span class="absolute -top-3 left-5 w-2 h-2 rounded-full bg-yellow-400"></span>
                    <span class="absolute -top-1 right-2 w-2 h-2 rounded-full bg-blue-300"></span>
                    <span class="absolute top-8 -left-4 w-2 h-2 rounded-full bg-blue-500"></span>
                    <span class="absolute bottom-4 -right-5 w-2 h-2 rounded-full bg-orange-400"></span>
                </div>

                <h3 class="mt-8 text-xl font-bold text-gray-700 tracking-[.5px]">
                    Pendaftaran Berhasil!
                </h3>
                <p class="mt-4 text-sm text-gray-500 max-w-sm">
                    Wajah Anda berhasil didaftarkan. Anda dapat melanjutkan ke dashboard.
                </p>

                <a href="{{ route('dashboard') }}" class="mt-8 w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-md hover:shadow-lg transition text-sm tracking-[.5px] text-center">
                    LANJUTKAN KE DASHBOARD
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
