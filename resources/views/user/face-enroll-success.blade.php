@extends('layouts.app')

@section('title', 'Pendaftaran Berhasil')

@section('content')
<div class="user-page">
    <div class="user-phone">

    <div class="px-4 pt-4">
        <div class="relative p-5">
            <div class="absolute top-10 left-0 w-full h-[2px] bg-emerald-100"></div>

            <div class="flex justify-between relative z-10 text-[8px] md:text-sm text-gray-500 tracking-[1px]">
                <div class="flex flex-col items-center">
                    <div class="w-10 h-10 flex items-center justify-center rounded-full bg-emerald-700 text-white font-bold">1</div>
                    <span class="mt-1 text-emerald-700 font-semibold">LOGIN</span>
                </div>

                <div class="flex flex-col items-center">
                    <div class="w-10 h-10 flex items-center justify-center rounded-full bg-emerald-700 text-white font-bold">2</div>
                    <span class="mt-1 text-emerald-700 font-semibold">PENDAFTARAN WAJAH</span>
                </div>

                <div class="flex flex-col items-center">
                    <div class="w-10 h-10 flex items-center justify-center rounded-full bg-emerald-700 text-white font-bold">3</div>
                    <span class="mt-1 text-emerald-700 font-semibold">VERIFIKASI</span>
                </div>

                <div class="flex flex-col items-center">
                    <div class="w-10 h-10 flex items-center justify-center rounded-full bg-emerald-700 text-white font-bold">4</div>
                    <span class="mt-1 text-emerald-700 font-semibold">BERHASIL</span>
                </div>
            </div>
        </div>
    </div>

    <div class="px-4 pb-10">
        <div class="user-card p-6">
            <div class="flex flex-col items-center text-center py-6">
                <div class="relative w-28 h-28 rounded-full border-4 border-green-500 flex items-center justify-center">
                    <i class="fa-solid fa-check text-5xl text-green-500"></i>
                    <span class="absolute -top-3 left-5 w-2 h-2 rounded-full bg-yellow-400"></span>
                    <span class="absolute -top-1 right-2 w-2 h-2 rounded-full bg-emerald-400"></span>
                    <span class="absolute top-8 -left-4 w-2 h-2 rounded-full bg-green-400"></span>
                    <span class="absolute bottom-4 -right-5 w-2 h-2 rounded-full bg-orange-400"></span>
                </div>

                <h1 class="mt-8 text-3xl font-bold text-gray-700 tracking-[0.4px]">Pendaftaran Berhasil!</h1>
                <p class="mt-4 text-sm text-gray-500 max-w-sm">
                    Wajah Anda berhasil didaftarkan. Anda dapat melanjutkan ke dashboard.
                </p>

                <a href="{{ route('dashboard') }}" class="mt-8 inline-flex items-center gap-3 bg-emerald-700 hover:bg-emerald-800 text-white text-sm font-semibold px-8 py-3 rounded-xl shadow transition">
                    LANJUTKAN KE DASHBOARD
                    <span class="w-6 h-6 rounded-full bg-white text-emerald-700 flex items-center justify-center">
                        <i class="fa-solid fa-arrow-right text-xs"></i>
                    </span>
                </a>
            </div>
        </div>
    </div>
    </div>
</div>
@endsection
