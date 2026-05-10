@extends('layouts.app')

@section('title', 'Verifikasi')

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
                <div class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-300">4</div>
                <span class="mt-1">BERHASIL</span>
            </div>
        </div>
    </div>
</div>

<div class="flex flex-1 items-center justify-center px-8 md:px-14 lg:px-20 py-7">
    <div class="w-full bg-white rounded-md shadow-lg p-2 md:p-5 flex flex-col md:flex-row gap-8">
        <div class="md:w-1/2 flex flex-col justify-center items-center text-center border-b md:border-b-0 md:border-r border-gray-100 pb-6 md:pb-0 md:pr-8">
            <img src="{{ asset('img/img-login.jpg') }}" class="w-[200px] h-auto mb-4">
            <h2 class="text-3xl font-bold text-gray-700 tracking-[.5px]">
                Verifikasi Wajah
            </h2>
            <p class="text-gray-400 text-sm mt-2 max-w-xs">
                Sistem sedang memproses dan memverifikasi sampel wajah Anda sebelum aktivasi selesai.
            </p>
        </div>

        <div class="md:w-1/2 flex flex-col justify-center">
            <h3 class="text-xl font-bold text-gray-700 mb-3 tracking-[.5px]">
                Proses Verifikasi
            </h3>

            <div class="flex flex-col items-center rounded-lg border border-gray-100 p-6">
                <div class="relative w-40 h-40">
                    <svg class="w-40 h-40 -rotate-90" viewBox="0 0 160 160">
                        <circle cx="80" cy="80" r="60" fill="none" stroke="#e5e7eb" stroke-width="10"></circle>
                        <circle id="progressRing" cx="80" cy="80" r="60" fill="none" stroke="#2563eb" stroke-width="10" stroke-linecap="round" stroke-dasharray="376.99" stroke-dashoffset="376.99"></circle>
                    </svg>
                    <div id="progressText" class="absolute inset-0 flex items-center justify-center text-3xl font-bold text-gray-700">0%</div>
                </div>

                <p id="progressLabel" class="mt-6 text-sm text-gray-400">Mohon tunggu sebentar...</p>

                <div class="mt-8 w-full max-w-md h-2 rounded-full bg-gray-200 overflow-hidden">
                    <div id="progressBar" class="h-full bg-blue-600 rounded-full transition-all duration-300 ease-out" style="width: 0%"></div>
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
