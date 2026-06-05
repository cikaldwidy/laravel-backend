@extends('layouts.app')

@section('title', 'Verifikasi')

@section('content')
<style>
@keyframes progress-glow {
    0%, 100% { opacity: 0.72; transform: scale(1); }
    50% { opacity: 1; transform: scale(1.04); }
}

.verification-panel {
    border: 1px solid #dbeafe;
    background: #ffffff;
    box-shadow: 0 18px 42px rgba(37, 99, 235, 0.08);
}

.progress-ring {
    filter: drop-shadow(0 12px 24px rgba(37, 99, 235, 0.16));
}

.progress-core {
    animation: progress-glow 1.6s ease-in-out infinite;
}

.verification-panel.is-failed {
    border-color: #fecaca;
}

.verification-panel.is-failed .progress-core {
    background: #fee2e2;
    animation: none;
}
</style>

<div class="px-6 md:px-10 mt-5">
    <div class="relative p-5">
        <div class="absolute top-10 left-0 w-full h-[2px] bg-gray-300"></div>
        <div class="absolute top-10 left-0 h-[2px] w-2/3 bg-blue-600"></div>

        <div class="flex justify-between relative z-10 text-[8px] md:text-sm tracking-[1px]">
            @foreach([
                ['label' => 'LOGIN', 'state' => 'done'],
                ['label' => 'PENDAFTARAN WAJAH', 'state' => 'done'],
                ['label' => 'VERIFIKASI', 'state' => 'active'],
                ['label' => 'BERHASIL', 'state' => 'pending'],
            ] as $step)
                @php
                    $isPending = $step['state'] === 'pending';
                    $isActive = $step['state'] === 'active';
                @endphp
                <div class="flex flex-col items-center">
                    <div class="w-10 h-10 flex items-center justify-center rounded-full font-bold {{ $isPending ? 'bg-gray-300 text-gray-600' : 'bg-blue-600 text-white' }}">
                        @if($step['state'] === 'done')
                            <i class="fa-solid fa-check"></i>
                        @elseif($isActive)
                            3
                        @else
                            4
                        @endif
                    </div>
                    <span class="mt-1 font-semibold {{ $isPending ? 'text-gray-500' : 'text-blue-600' }}">{{ $step['label'] }}</span>
                </div>
            @endforeach
        </div>
    </div>
</div>

<div class="px-6 md:px-10 pb-7">
    <div id="verificationPanel" class="verification-panel rounded-xl p-6 md:p-8">
        <div class="mx-auto flex max-w-3xl flex-col items-center justify-center py-8 text-center md:py-12">
            <div class="relative h-40 w-40">
                <svg class="progress-ring h-40 w-40 -rotate-90" viewBox="0 0 160 160" aria-hidden="true">
                    <circle cx="80" cy="80" r="58" fill="none" stroke="#dbeafe" stroke-width="10"></circle>
                    <circle id="progressRing" cx="80" cy="80" r="58" fill="none" stroke="#2563eb" stroke-width="10" stroke-linecap="round"></circle>
                </svg>
                <div class="absolute inset-5 rounded-full bg-blue-50 progress-core"></div>
                <div id="progressText" class="absolute inset-0 flex items-center justify-center text-3xl font-bold text-slate-800">0%</div>
            </div>

            <h2 id="progressTitle" class="mt-8 text-2xl font-bold tracking-[.3px] text-slate-800 md:text-3xl">
                Mengecek Wajah Anda
            </h2>
            <p id="progressLabel" class="mt-3 max-w-xl text-sm leading-relaxed text-slate-500">
                Kami sedang memastikan foto wajah yang diambil sudah cocok. Mohon tunggu sebentar.
            </p>

            <div class="mt-8 h-2 w-full max-w-md overflow-hidden rounded-full bg-blue-100">
                <div id="progressBar" class="h-full w-0 rounded-full bg-blue-600"></div>
            </div>

            <a id="retryEnroll" href="{{ route('face.enroll') }}" class="mt-8 hidden items-center justify-center text-sm font-semibold uppercase tracking-[1.5px] text-red-600 transition hover:text-red-700">
                Ulangi Pendaftaran
                <i class="fa-solid fa-arrow-rotate-right ml-2 text-xs"></i>
            </a>
        </div>
    </div>
</div>

<script>
const verificationFailed = @json($verificationFailed ?? false);
const verificationMessage = @json($verificationMessage ?? 'Wajah yang terlihat di setiap langkah berbeda. Ulangi pendaftaran dan pastikan orang yang sama mengikuti semua instruksi.');
const verificationPanel = document.getElementById('verificationPanel');
const progressRing = document.getElementById('progressRing');
const progressText = document.getElementById('progressText');
const progressBar = document.getElementById('progressBar');
const progressTitle = document.getElementById('progressTitle');
const progressLabel = document.getElementById('progressLabel');
const retryEnroll = document.getElementById('retryEnroll');

const radius = 58;
const circumference = 2 * Math.PI * radius;
let currentProgress = 0;
let targetProgress = 0;
let completed = false;

progressRing.style.strokeDasharray = circumference;
progressRing.style.strokeDashoffset = circumference;
progressBar.style.transition = 'width 260ms ease-out';

function renderProgress(value) {
    const rounded = Math.min(100, Math.round(value));
    const offset = circumference - (value / 100) * circumference;

    progressRing.style.strokeDashoffset = offset;
    progressText.textContent = `${rounded}%`;
    progressBar.style.width = `${value}%`;
}

function animateProgress() {
    currentProgress += (targetProgress - currentProgress) * 0.08;

    if (targetProgress >= 100 && Math.abs(targetProgress - currentProgress) < 0.15) {
        currentProgress = 100;
    }

    renderProgress(currentProgress);

    if (currentProgress >= 100 && !completed) {
        completed = true;

        if (verificationFailed) {
            verificationPanel.classList.add('is-failed');
            progressRing.setAttribute('stroke', '#dc2626');
            progressBar.className = 'h-full w-0 rounded-full bg-red-600';
            progressText.className = 'absolute inset-0 flex items-center justify-center text-3xl font-bold text-red-600';
            progressText.innerHTML = '<i class="fa-solid fa-xmark"></i>';
            progressTitle.textContent = 'Wajah Tidak Cocok';
            progressLabel.textContent = verificationMessage;
            retryEnroll.classList.remove('hidden');
            retryEnroll.classList.add('inline-flex');
            return;
        }

        progressTitle.textContent = 'Wajah Berhasil Dicek';
        progressLabel.textContent = 'Pendaftaran wajah berhasil. Anda akan diarahkan sebentar lagi.';
        window.setTimeout(() => {
            window.location.href = "{{ route('face.success') }}";
        }, 650);
        return;
    }

    window.requestAnimationFrame(animateProgress);
}

const interval = window.setInterval(() => {
    targetProgress = Math.min(100, targetProgress + 4);

    if (targetProgress >= 100) {
        window.clearInterval(interval);
    }
}, 110);

window.requestAnimationFrame(animateProgress);
</script>
@endsection
