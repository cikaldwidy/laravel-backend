@extends('layouts.app')

@section('title', 'Pendaftaran Berhasil')

@section('content')
<style>
.success-panel {
    border: 1px solid #dbeafe;
    background: #ffffff;
    box-shadow: 0 18px 42px rgba(37, 99, 235, 0.08);
}

.success-loader {
    position: relative;
    width: 6.5rem;
    height: 6.5rem;
    border-radius: 9999px;
    border: 3px solid #22c55e;
    background: #dcfce7;
}

.success-check {
    position: absolute;
    inset: 0;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #15803d;
    font-size: 2.1rem;
}
</style>

<div class="px-6 md:px-10 mt-5">
    <div class="relative p-5">
        <div class="absolute top-10 left-0 w-full h-[2px] bg-blue-600"></div>

        <div class="flex justify-between relative z-10 text-[8px] md:text-sm text-blue-600 tracking-[1px]">
            @foreach(['LOGIN', 'PENDAFTARAN WAJAH', 'VERIFIKASI', 'BERHASIL'] as $label)
                <div class="flex flex-col items-center">
                    <div class="w-10 h-10 flex items-center justify-center rounded-full bg-blue-600 text-white font-bold">
                        <i class="fa-solid fa-check"></i>
                    </div>
                    <span class="mt-1 font-semibold">{{ $label }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="px-6 md:px-10 pb-7">
    <div class="success-panel rounded-xl p-6 md:p-8">
        <div class="mx-auto flex max-w-3xl flex-col items-center justify-center py-8 text-center md:py-12">
            <div class="success-loader">
                <div class="success-check">
                    <i class="fa-solid fa-check"></i>
                </div>
            </div>

            <h2 class="mt-7 text-2xl font-bold tracking-[.3px] text-slate-800 md:text-3xl">
                Verifikasi Berhasil
            </h2>
            <p class="mt-3 max-w-xl text-sm leading-relaxed text-slate-500">
                Data wajah sudah tersimpan dan akun Anda siap digunakan untuk proses presensi.
            </p>

            <a href="{{ route('dashboard') }}" class="mt-8 inline-flex items-center justify-center text-sm font-semibold uppercase tracking-[1.5px] text-blue-600 transition hover:text-blue-700">
                Next
                <i class="fa-solid fa-arrow-right ml-2 text-xs"></i>
            </a>
        </div>
    </div>
</div>
@endsection
