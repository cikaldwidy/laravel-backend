@extends('layouts.app')

@section('title', 'Verifikasi')

@section('content')
<div class="min-h-screen bg-[#f8fbff]">
    <div class="px-8 md:px-14 lg:px-20 pt-6">
        <div class="flex items-center gap-3">
            <img src="{{ asset('img/logo.jpeg') }}" class="w-12 h-12 rounded-xl object-cover shadow">
            <div class="leading-tight">
                <p class="text-[10px] font-semibold text-gray-400 tracking-widest uppercase">Rumah Sakit Umum</p>
                <p class="text-sm font-extrabold text-blue-700 tracking-wide">SATITI PRIMA HUSADA</p>
                <p class="text-[10px] font-bold text-red-500 tracking-widest uppercase">Tulungagung</p>
            </div>
        </div>
    </div>

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
                    <div class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-300">4</div>
                    <span class="mt-1">BERHASIL</span>
                </div>
            </div>
        </div>
    </div>

    <div class="px-4 md:px-14 lg:px-20 pb-10">
        <div class="max-w-5xl mx-auto bg-white rounded-md shadow-lg p-6 md:p-8">
            <div class="text-center">
                <h1 class="text-2xl font-bold text-gray-700 tracking-[0.4px]">Verifikasi Wajah</h1>
                <p class="text-sm text-gray-400 mt-3">
                    Sistem sedang memverifikasi sampel wajah Anda.
                </p>
            </div>

            <div class="mt-10 flex flex-col items-center">
                <div class="relative w-40 h-40">
                    <svg class="w-40 h-40 -rotate-90" viewBox="0 0 160 160">
                        <circle cx="80" cy="80" r="60" fill="none" stroke="#e5e7eb" stroke-width="10"></circle>
                        <circle id="progressRing" cx="80" cy="80" r="60" fill="none" stroke="#1d4ed8" stroke-width="10" stroke-linecap="round" stroke-dasharray="376.99" stroke-dashoffset="376.99"></circle>
                    </svg>
                    <div id="progressText" class="absolute inset-0 flex items-center justify-center text-3xl font-bold text-gray-700">0%</div>
                </div>

                <p id="progressLabel" class="mt-6 text-sm text-gray-400">Mohon tunggu sebentar...</p>

                <div class="mt-8 w-full max-w-md h-2 rounded-full bg-gray-200 overflow-hidden">
                    <div id="progressBar" class="h-full bg-blue-700 rounded-full transition-all duration-300 ease-out" style="width: 0%"></div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const progressRing = document.getElementById('progressRing');
const progressText = document.getElementById('progressText');
const progressBar = document.getElementById('progressBar');
const progressLabel = document.getElementById('progressLabel');

const radius = 60;
const circumference = 2 * Math.PI * radius;
let progress = 0;

function updateProgress(value) {
    const offset = circumference - (value / 100) * circumference;
    progressRing.style.strokeDashoffset = offset;
    progressText.textContent = `${value}%`;
    progressBar.style.width = `${value}%`;
}

const interval = window.setInterval(() => {
    progress += 5;
    updateProgress(progress);

    if (progress >= 100) {
        window.clearInterval(interval);
        progressLabel.textContent = 'Verifikasi selesai. Mengalihkan ke halaman berhasil...';
        window.setTimeout(() => {
           window.location.href = "{{ route('face.success') }}";
        }, 500);
    }
}, 120);
</script>
@endsection
