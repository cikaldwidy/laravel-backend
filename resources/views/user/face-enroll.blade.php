@extends('layouts.app')

@section('title', 'Pendaftaran Wajah')

@section('content')
<style>
@keyframes face-scan-line {
    0%   { top: 0%;   opacity: 0; }
    8%   { opacity: 1; }
    50%  { top: calc(100% - 3px); opacity: 1; }
    92%  { opacity: 1; }
    100% { top: 0%;   opacity: 0; }
}

@keyframes face-scan-glow {
    0%   { top: 0%;   opacity: 0; }
    8%   { opacity: 0.6; }
    50%  { top: calc(82% - 3px); opacity: 0.85; }
    92%  { opacity: 0.5; }
    100% { top: 0%;   opacity: 0; }
}

@keyframes frame-pulse {
    0%, 100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(59,130,246,0.14), 0 0 0 14px rgba(59,130,246,0.06); }
    50% { transform: scale(1.03); box-shadow: 0 0 0 10px rgba(59,130,246,0.08), 0 0 0 22px rgba(59,130,246,0.03); }
}

.face-enroll-shell {
    width: 100%;
}

.face-enroll-stage {
    aspect-ratio: 18 / 9;
    background: #ffffff;
    border: 1px solid rgba(191, 219, 254, 0.9);
}

.face-guide {
    width: min(82vw, 29rem);
    height: min(82vw, 29rem);
}

.camera-panel {
    border: 1px solid #e2e8f0;
    background: #ffffff;
}

/* ── blur mask luar lingkaran ── */
/* ── blur mask luar lingkaran ── */
/* ── blur luar lingkaran: pakai box-shadow inset trick ── */
.face-blur-outer {
    position: absolute;
    inset: 8%;                        /* sesuaikan dengan ukuran lingkaran */
    border-radius: 50%;
    pointer-events: none;
    /* shadow sangat besar ke luar = blur/gelap di luar lingkaran */
    box-shadow:
        0 0 0 9999px rgba(8, 15, 40, 0.55),   /* overlay gelap luar */
        0 0 40px 8px rgba(0, 0, 0, 0.5) inset; /* soft shadow dalam tepi */
    opacity: 0;
    transition: opacity 0.35s ease;
}

.face-inner-mask {
    position: absolute;
    inset: 10%;
    border-radius: 50%;
    background: radial-gradient(circle at center, rgba(255, 255, 255, 0.98) 0%, rgba(248, 250, 252, 0.95) 60%, rgba(241, 245, 249, 0.92) 100%);
    box-shadow: inset 0 0 28px rgba(148, 163, 184, 0.12);
    transition: opacity 0.35s ease, background 0.35s ease;
}

.face-guide.is-camera-ready .face-inner-mask {
    opacity: 0.08;
}

.face-guide.is-camera-ready .face-blur-outer {
    opacity: 1;
}

.camera-guide-text {
    opacity: 0;
    transition: opacity 0.25s ease;
    text-shadow: 0 2px 10px rgba(15, 23, 42, 0.45);
}

.camera-guide-text.is-visible {
    opacity: 1;
}

.camera-guide-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-height: 2.75rem;
    padding: 0.75rem 1rem;
    border-radius: 9999px;
    background: rgba(15, 23, 42, 0.72);
    border: 1px solid rgba(255, 255, 255, 0.18);
    box-shadow: 0 10px 25px rgba(15, 23, 42, 0.22);
    backdrop-filter: blur(10px);
}

/* ── scan track ── */
/* ── scan track: HAPUS overflow hidden ── */
.face-scan-track {
    position: absolute;
    top: 10%; bottom: 10%;   /* perlebar area agar tidak terpotong */
    left: 10%; right: 10%;
    border-radius: 50%;
    /* JANGAN pakai overflow: hidden — ini yang bikin terpotong & delay visual */
}

.face-scan-line {
    position: absolute;
    left: -5%; right: -5%;  /* lebih lebar dari track agar terlihat penuh */
    top: 0;
    height: 2px;
    border-radius: 9999px;
    background: linear-gradient(
        90deg,
        transparent 0%,
        rgba(147, 197, 253, 0.5) 10%,
        rgba(59, 130, 246, 1) 35%,
        rgba(220, 240, 255, 1) 50%,
        rgba(59, 130, 246, 1) 65%,
        rgba(147, 197, 253, 0.5) 90%,
        transparent 100%
    );
    box-shadow:
        0 0 4px 1px rgba(59, 130, 246, 0.7),
        0 0 14px 4px rgba(96, 165, 250, 0.4),
        0 0 28px 8px rgba(59, 130, 246, 0.18);
    opacity: 0;
    z-index: 10;
    will-change: top, opacity;          /* GPU hint agar tidak delay */
}

/* animasi lebih cepat: 1.6s (dari 2.4s) */
@keyframes face-scan-line {
    0%   { top: 0%;                   opacity: 0; }
    6%   { opacity: 1; }
    50%  { top: calc(100% - 2px);    opacity: 1; }
    94%  { opacity: 1; }
    100% { top: 0%;                   opacity: 0; }
}

@keyframes face-scan-glow {
    0%   { top: 0%;                   opacity: 0; }
    6%   { opacity: 0.7; }
    50%  { top: calc(80% - 2px);     opacity: 0.9; }
    94%  { opacity: 0.5; }
    100% { top: 0%;                   opacity: 0; }
}

.face-scan-track.is-active .face-scan-line {
    animation: face-scan-line 1.6s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    opacity: 1;
}

.face-scan-track.is-active .face-scan-glow {
    animation: face-scan-glow 1.6s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    opacity: 1;
}
/* ── orbit rings ── */
.face-orbit-ring {
    position: absolute;
    border-radius: 50%;
    border: 1px dashed rgba(96, 165, 250, 0.35);
    pointer-events: none;
}
.face-orbit-ring.outer {
    inset: -4px;
    animation: ring-rotate 12s linear infinite;
}
.face-orbit-ring.inner {
    inset: 8px;
    border-color: rgba(186, 230, 253, 0.22);
    border-style: dotted;
    animation: ring-rotate-rev 18s linear infinite;
}

/* orbit dot markers */
.orbit-dot {
    position: absolute;
    width: 5px; height: 5px;
    border-radius: 50%;
    background: rgba(96, 165, 250, 0.85);
    box-shadow: 0 0 6px 2px rgba(59, 130, 246, 0.55);
    animation: dot-blink 2.4s ease-in-out infinite;
}

/* ── corner brackets ── */
.face-corner {
    position: absolute;
    width: 20px; height: 20px;
    border-color: rgba(96, 165, 250, 0.85);
    border-style: solid;
    pointer-events: none;
}
.face-corner.tl { top: 12px; left: 12px; border-width: 3px 0 0 3px; border-radius: 4px 0 0 0; }
.face-corner.tr { top: 12px; right: 12px; border-width: 3px 3px 0 0; border-radius: 0 4px 0 0; }
.face-corner.bl { bottom: 12px; left: 12px; border-width: 0 0 3px 3px; border-radius: 0 0 0 4px; }
.face-corner.br { bottom: 12px; right: 12px; border-width: 0 3px 3px 0; border-radius: 0 0 4px 0; }

/* ── main circle frame ── */
.face-circle-frame {
    position: absolute;
    inset: 10%;
    border-radius: 50%;
    border: 3px solid rgba(59, 130, 246, 0.75);
    transition: all 0.35s ease;
}
.face-circle-frame.valid {
    border-color: rgba(59, 130, 246, 0.95);
    box-shadow:
        inset 0 0 30px 6px rgba(59, 130, 246, 0.1),
        0 0 0 12px rgba(59, 130, 246, 0.08);
}
.face-circle-frame.invalid {
    border-color: rgba(248, 113, 113, 0.9);
    box-shadow:
        inset 0 0 20px 4px rgba(248, 113, 113, 0.08),
        0 0 0 12px rgba(255, 255, 255, 0.08);
}

@media (max-width: 640px) {
    .face-enroll-stage { aspect-ratio: 5 / 6; }
    .face-guide {
        width: min(86vw, 21rem);
        height: min(86vw, 21rem);
    }
}
</style>

<div class="px-6 md:px-10 mt-5">
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
                <div class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-300">3</div>
                <span class="mt-1">VERIFIKASI</span>
            </div>

            <div class="flex flex-col items-center">
                <div class="w-10 h-10 flex items-center justify-center rounded-full bg-gray-300">4</div>
                <span class="mt-1">BERHASIL</span>
            </div>
        </div>
    </div>
</div>

<div class="px-6 md:px-10 pb-7">
    <div class="face-enroll-shell">
        <div class="mx-auto">
            <div class="mb-8 w-full rounded-md border border-blue-100 bg-white p-6 md:p-8 text-left shadow-[0_18px_40px_rgba(37,99,235,0.08)]">
                <div class="flex flex-col gap-7 md:flex-row md:items-center">
                    <div class="md:w-72 md:shrink-0 flex justify-center">
                        <img src="{{ asset('img/img-pendaftaran.png') }}" alt="Pendaftaran Wajah" class="w-52 md:w-64 h-auto">
                    </div>
                    <div class="flex-1">
                        <span class="inline-flex items-center px-2 py-1.5 text-xs font-semibold uppercase tracking-[1.5px] text-blue-600">
                            Panduan Pendaftaran Wajah 
                        </span>
                        <div class="mt-3 space-y-4 text-sm md:text-[15px] text-slate-600 tracking-[.2px]">
                            <p class="flex items-center gap-4 rounded-md bg-white/75 p-2 ring-1 ring-blue-50">
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center self-center rounded-full bg-blue-50 text-blue-600">
                                    <i class="fa-regular fa-face-smile"></i>
                                </span>
                                <span class="self-center leading-relaxed">Hadapkan wajah ke kamera dan pastikan posisi berada di tengah bingkai.</span>
                            </p>
                            <p class="flex items-center gap-4 rounded-md bg-white/75 p-2 ring-1 ring-blue-50">
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center self-center rounded-full bg-blue-50 text-blue-600">
                                    <i class="fa-regular fa-lightbulb"></i>
                                </span>
                                <span class="self-center leading-relaxed">Gunakan pencahayaan cukup agar sistem dapat membaca wajah dengan stabil.</span>
                            </p>
                            <p class="flex items-center gap-4 rounded-2xl bg-white/75 p-2 ring-1 ring-blue-50">
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center self-center rounded-full bg-blue-50 text-blue-600">
                                    <i class="fa-solid fa-arrows-rotate"></i>
                                </span>
                                <span class="self-center leading-relaxed">Sampel akan tersimpan otomatis setelah wajah jelas dan kedipan terverifikasi.</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="camera-panel rounded-md p-4 md:p-5">
                <div id="iosSafariHandoff" class="hidden mb-4 rounded-md border border-blue-200 bg-blue-50 p-4">
                    <p class="text-sm font-bold text-blue-800">Kamera iPhone PWA kurang stabil</p>
                    <p class="mt-1 text-xs leading-relaxed text-blue-700">
                        Lanjutkan pendaftaran wajah di Safari. Setelah berhasil, buka kembali aplikasi Presensi; status akan dicek otomatis.
                    </p>
                    <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <a id="openSafariEnroll" href="{{ route('face.enroll', ['handoff' => 'safari'], false) }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center rounded-md bg-blue-600 px-4 py-2 text-xs font-semibold text-white">
                            <i class="fa-brands fa-safari mr-2"></i>BUKA DI SAFARI
                        </a>
                        <button type="button" id="copySafariEnroll" class="rounded-md border border-blue-200 bg-white px-4 py-2 text-xs font-semibold text-blue-700">
                            SALIN LINK
                        </button>
                    </div>
                </div>

                <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-sm font-semibold uppercase tracking-[1.4px] text-blue-600">Area Kamera</p>
                        <p class="mt-1 text-sm text-slate-500">Aktifkan kamera, lalu ikuti instruksi sampai 3 sampel wajah tersimpan.</p>
                    </div>
                    <div class="flex flex-col gap-3 w-full md:w-auto md:items-end">
                        <div id="cameraStatusBadge" class="inline-flex items-center gap-2 self-start border border-gray-200 bg-gray-100 px-2 py-1.5 text-xs font-medium tracking-[.3px] text-slate-500 md:self-end">
                            <i id="cameraStatusIcon" class="fa-solid fa-camera-slash"></i>
                            <span id="cameraStatusText">Kamera mati</span>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 w-full md:min-w-[360px]">
                        <button type="button" id="startCamera" class="w-full rounded-md bg-blue-600 px-4 py-2 text-xs font-semibold tracking-[.5px] text-white transition hover:bg-blue-700 hover:shadow-lg">
                            <i class="fa-solid fa-camera mr-2"></i>AKTIFKAN KAMERA
                        </button>
                        <button type="button" id="resetSamples" class="w-full rounded-md border border-red-200 bg-white px-4 py-2 text-xs font-semibold tracking-[.5px] text-red-500 transition hover:bg-red-50">
                            RESET
                        </button>
                        </div>
                    </div>
                </div>

                <div id="enrollStage" class="face-enroll-stage relative overflow-hidden">
                    <video id="video" autoplay muted playsinline class="w-full h-full object-cover opacity-0 transition-opacity duration-300"></video>
                    <div class="absolute inset-0 pointer-events-none flex flex-col items-center justify-center">
                      <div id="headGuide" class="face-guide relative transition-transform duration-500 ease-out">

    <!-- BLUR LUAR LINGKARAN (box-shadow trick, bukan backdrop-filter) -->
    <div class="face-blur-outer"></div>

    <div class="face-inner-mask"></div>

    <!-- main circle frame -->
    <div id="headFrame" class="face-circle-frame"></div>

    <!-- scan track — no overflow hidden! -->
    <div class="face-scan-track">
        <div class="face-scan-glow"></div>
        <div id="scanLine" class="face-scan-line"></div>
    </div>
</div>
                        <div id="guideInstruction" class="camera-guide-text camera-guide-badge mt-4 mx-4 text-center text-sm font-medium text-white tracking-[.3px]">
                            Arahkan wajah ke kamera, lalu kedipkan mata.
                        </div>
                    </div>
                </div>
            </div>

            <p id="status" class="mt-5 text-sm text-gray-500 text-center tracking-[.3px]">
                Siapkan kamera untuk mulai proses pendaftaran.
            </p>

            <div class="mt-6">
                <div class="rounded-md border bg-white p-4 sm:p-5">
                    <div class="flex items-center justify-between text-sm font-semibold text-gray-700">
                        <p class="tracking-[.3px]">Wajah Disimpan</p>
                        <p id="sampleCount">0 / 3</p>
                    </div>

                    <div class="mt-4 h-3 w-full overflow-hidden rounded-full bg-blue-100 ring-1 ring-blue-200/70">
                        <div id="sampleProgressBar" class="h-full w-0 rounded-full bg-gradient-to-r from-sky-400 via-blue-500 to-blue-600 transition-all duration-300 ease-out"></div>
                    </div>

                    <p class="mt-3 text-xs text-gray-500 tracking-[.3px]">
                        Progress akan bertambah otomatis setiap sampel wajah berhasil disimpan.
                    </p>
                    <p id="sampleStepNote" class="mt-2 text-sm font-medium tracking-[.5px] text-blue-600">
                        Langkah 1: Kedipkan mata.
                    </p>
                </div>
            </div>

            <canvas id="captureCanvas" class="hidden"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
const video = document.getElementById('video');
const startCameraButton = document.getElementById('startCamera');
const resetSamplesButton = document.getElementById('resetSamples');
const iosSafariHandoff = document.getElementById('iosSafariHandoff');
const openSafariEnrollLink = document.getElementById('openSafariEnroll');
const copySafariEnrollButton = document.getElementById('copySafariEnroll');
const cameraStatusBadge = document.getElementById('cameraStatusBadge');
const cameraStatusIcon = document.getElementById('cameraStatusIcon');
const cameraStatusText = document.getElementById('cameraStatusText');
const statusText = document.getElementById('status');
const sampleCount = document.getElementById('sampleCount');
const sampleProgressBar = document.getElementById('sampleProgressBar');
const sampleStepNote = document.getElementById('sampleStepNote');
const canvas = document.getElementById('captureCanvas');
const headGuide = document.getElementById('headGuide');
const headFrame = document.getElementById('headFrame');
const guideInstruction = document.getElementById('guideInstruction');
const scanTrack = document.querySelector('.face-scan-track');

const faceStatusUrl = "{{ route('face.status', [], false) }}";
const safariEnrollUrl = "{{ route('face.enroll', ['handoff' => 'safari'], false) }}";
const REQUIRED_SAMPLES = 3;
const descriptors = [];
const sampleQualities = [];
let enrollmentImage = null;
const modelBaseUrl = '/face-api/models';
const detectorOptions = new faceapi.TinyFaceDetectorOptions({
    inputSize: 160,
    scoreThreshold: 0.4,
});
const MIN_BRIGHTNESS = 38;
const MAX_BRIGHTNESS = 210;
const MIN_SHARPNESS = 10;
const BLINK_OPEN_EAR = 0.17;
const BLINK_CLOSED_EAR = 0.145;
const BLINK_DROP_RATIO = 0.9;
const TURN_THRESHOLD = 0.035;
const BLINK_CAPTURE_WINDOW_MS = 2200;
const BLINK_COOLDOWN_MS = 900;
const CAMERA_RESPONSE_TIMEOUT_MS = 7000;
const CAMERA_VIDEO_READY_TIMEOUT_MS = 7000;
const CAMERA_TIMEOUT_MESSAGE = 'Kamera tidak merespons di PWA. Gunakan Buka di Safari untuk pendaftaran wajah.';
const ENROLLMENT_STEPS = [
    'Langkah 1: Kedipkan mata.',
    'Langkah 2: Hadapkan wajah ke kanan.',
    'Langkah 3: Hadapkan wajah ke kiri.',
];

let modelsLoaded = false;
let stream;
let enrollmentFrame = null;
let modelsPromise = null;
let trackingActive = false;
let processingDetection = false;
let lastDetectionAt = 0;
let lastCaptureAt = 0;
let isSaving = false;
let blinkVerified = false;
let blinkVerifiedAt = 0;
let eyesWereOpen = false;
let lastEar = null;
let maxOpenEar = 0;
let blinkCloseFrames = 0;
let blinkCooldownUntil = 0;
let lastNoFaceFeedbackAt = 0;
let lastVideoWaitingFeedbackAt = 0;
let detectionErrorCount = 0;
let cameraStartToken = 0;
let handoffStatusChecking = false;

function isIosDevice() {
    return /iPad|iPhone|iPod/.test(navigator.userAgent)
        || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
}

function isStandalonePwa() {
    return window.matchMedia('(display-mode: standalone)').matches
        || window.navigator.standalone === true;
}

function setScanAnimationActive(isActive) {
    if (!scanTrack) return;
    scanTrack.classList.toggle('is-active', isActive);
    if (headGuide) {
        headGuide.classList.toggle('is-camera-ready', isActive);
    }
    if (video) {
        video.classList.toggle('opacity-0', !isActive);
        video.classList.toggle('opacity-100', isActive);
    }
    if (guideInstruction) {
        guideInstruction.classList.toggle('is-visible', isActive);
    }
    if (cameraStatusBadge && cameraStatusIcon && cameraStatusText) {
        cameraStatusBadge.className = isActive
            ? 'inline-flex items-center gap-2 self-start rounded-full border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold tracking-[.4px] text-blue-600 md:self-end'
            : 'inline-flex items-center gap-2 self-start rounded-full border border-slate-200 bg-slate-100 px-3 py-2 text-xs font-semibold tracking-[.4px] text-slate-500 md:self-end';
        cameraStatusIcon.className = isActive ? 'fa-solid fa-camera' : 'fa-solid fa-camera-slash';
        cameraStatusText.textContent = isActive ? 'Kamera aktif' : 'Kamera mati';
    }
}

function updateStatus(message, isError = false) {
    statusText.textContent = message;
    statusText.className = isError
        ? 'mt-4 text-sm text-red-500 text-center md:text-left'
        : 'mt-4 text-sm text-gray-500 text-center md:text-left';
}

function setupSafariHandoff() {
    if (!iosSafariHandoff || !isIosDevice() || !isStandalonePwa()) return;

    iosSafariHandoff.classList.remove('hidden');

    if (openSafariEnrollLink) {
        openSafariEnrollLink.href = safariEnrollUrl;
    }

    copySafariEnrollButton?.addEventListener('click', async () => {
        const absoluteUrl = new URL(safariEnrollUrl, window.location.origin).href;

        try {
            await navigator.clipboard.writeText(absoluteUrl);
            updateStatus('Link Safari disalin. Tempel di Safari jika tombol buka tidak berpindah.', false);
        } catch (error) {
            updateStatus('Buka link ini di Safari: ' + absoluteUrl, false);
        }
    });
}

async function checkEnrollmentHandoffStatus() {
    if (!isIosDevice() || !isStandalonePwa() || handoffStatusChecking) return;

    handoffStatusChecking = true;

    try {
        const response = await fetch(faceStatusUrl, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            cache: 'no-store',
        });

        if (!response.ok) return;

        const data = await response.json();

        if (data.has_enrollment && data.redirect) {
            window.location.href = data.redirect;
        }
    } catch (error) {
        // Dicek lagi saat app kembali aktif.
    } finally {
        handoffStatusChecking = false;
    }
}

function setBlinkVerified(value) {
    blinkVerified = value;
    blinkVerifiedAt = value ? Date.now() : 0;
}

function updateStepNote() {
    if (!sampleStepNote) return;

    if (descriptors.length >= REQUIRED_SAMPLES) {
        sampleStepNote.textContent = 'Semua langkah selesai. Menyimpan data wajah...';
        return;
    }

    sampleStepNote.textContent = ENROLLMENT_STEPS[descriptors.length];
}

function updateFrameIndicator(isValid) {
    headFrame.classList.toggle('valid', isValid);
    headFrame.classList.toggle('invalid', !isValid);
}

function updateSampleCount() {
    sampleCount.textContent = `${descriptors.length} / ${REQUIRED_SAMPLES}`;
    if (sampleProgressBar) {
        sampleProgressBar.style.width = `${(descriptors.length / REQUIRED_SAMPLES) * 100}%`;
    }
    updateStepNote();
}

function isFaceInsideGuide(box) {
    if (!video.videoWidth || !video.videoHeight) return false;

    const faceCenterX = box.x + (box.width / 2);
    const faceCenterY = box.y + (box.height / 2);
    const horizontalOffset = Math.abs((faceCenterX / video.videoWidth) - 0.5);
    const verticalOffset = Math.abs((faceCenterY / video.videoHeight) - 0.5);
    const faceWidthRatio = box.width / video.videoWidth;
    const faceHeightRatio = box.height / video.videoHeight;

    return horizontalOffset <= 0.16 &&
        verticalOffset <= 0.18 &&
        faceWidthRatio >= 0.20 &&
        faceWidthRatio <= 0.58 &&
        faceHeightRatio >= 0.28 &&
        faceHeightRatio <= 0.82;
}

function distanceBetween(pointA, pointB) {
    return Math.hypot(pointA.x - pointB.x, pointA.y - pointB.y);
}

function calculateEyeAspectRatio(eye) {
    const verticalA = distanceBetween(eye[1], eye[5]);
    const verticalB = distanceBetween(eye[2], eye[4]);
    const horizontal = distanceBetween(eye[0], eye[3]);
    return horizontal === 0 ? 0 : (verticalA + verticalB) / (2 * horizontal);
}

function detectBlink(landmarks) {
    const now = Date.now();
    const ear = (calculateEyeAspectRatio(landmarks.getLeftEye()) + calculateEyeAspectRatio(landmarks.getRightEye())) / 2;
    lastEar = ear;

    maxOpenEar = Math.max(maxOpenEar, ear);
    const adaptiveOpenThreshold = Math.max(BLINK_OPEN_EAR, maxOpenEar * 0.88);
    const adaptiveClosedThreshold = Math.max(BLINK_CLOSED_EAR, maxOpenEar * BLINK_DROP_RATIO);

    if (ear >= adaptiveOpenThreshold) {
        if (eyesWereOpen && blinkCloseFrames >= 1 && now >= blinkCooldownUntil) {
            blinkCloseFrames = 0;
            blinkCooldownUntil = now + BLINK_COOLDOWN_MS;
            return true;
        }

        eyesWereOpen = true;
        blinkCloseFrames = 0;
        return false;
    }

    if (eyesWereOpen && ear <= adaptiveClosedThreshold && now >= blinkCooldownUntil) {
        blinkCloseFrames += 1;
        if (blinkCloseFrames >= 2 && maxOpenEar > 0 && ear <= maxOpenEar * BLINK_DROP_RATIO) {
            blinkCooldownUntil = now + BLINK_COOLDOWN_MS;
            blinkCloseFrames = 0;
            return true;
        }
    }

    return false;
}

function getHeadTurnDirection(landmarks, faceBox) {
    const nose = landmarks.getNose();
    if (!nose || nose.length === 0 || !faceBox.width) return 'center';

    const noseTip = nose[Math.floor(nose.length / 2)];
    const faceCenterX = faceBox.x + (faceBox.width / 2);
    const horizontalOffset = (noseTip.x - faceCenterX) / faceBox.width;

    if (horizontalOffset >= TURN_THRESHOLD) return 'right';
    if (horizontalOffset <= -TURN_THRESHOLD) return 'left';
    return 'center';
}

function getHeadTurnFeedback(direction, step) {
    if (step === 1) {
        if (direction === 'left') {
            return {
                guide: 'Wajah masih mengarah ke kiri. Putar perlahan ke kanan.',
                status: 'Progres belum lanjut. Untuk langkah kedua, hadapkan wajah sedikit ke kanan.',
            };
        }

        return {
            guide: 'Putar wajah sedikit ke kanan, jangan terlalu berlebihan.',
            status: 'Progres belum lanjut. Hadapkan wajah ke kanan sampai posisi berubah dari tengah.',
        };
    }

    if (direction === 'right') {
        return {
            guide: 'Wajah masih mengarah ke kanan. Putar perlahan ke kiri.',
            status: 'Progres belum lanjut. Untuk langkah terakhir, hadapkan wajah sedikit ke kiri.',
        };
    }

    return {
        guide: 'Putar wajah sedikit ke kiri, jangan terlalu berlebihan.',
        status: 'Progres belum lanjut. Hadapkan wajah ke kiri sampai posisi berubah dari tengah.',
    };
}

function getFrameQuality(faceBox = null) {
    if (!video.videoWidth || !video.videoHeight) return { brightness: 0, sharpness: 0 };

    canvas.width = 240;
    canvas.height = 180;
    const context = canvas.getContext('2d', { willReadFrequently: true });
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    const width = canvas.width;
    const height = canvas.height;

    let sampleX = Math.floor(width * 0.2);
    let sampleY = Math.floor(height * 0.15);
    let sampleWidth = Math.floor(width * 0.6);
    let sampleHeight = Math.floor(height * 0.7);

    if (faceBox) {
        const scaleX = width / video.videoWidth;
        const scaleY = height / video.videoHeight;
        sampleX = Math.max(0, Math.floor(faceBox.x * scaleX));
        sampleY = Math.max(0, Math.floor(faceBox.y * scaleY));
        sampleWidth = Math.min(width - sampleX, Math.floor(faceBox.width * scaleX));
        sampleHeight = Math.min(height - sampleY, Math.floor(faceBox.height * scaleY));
    }

    const { data } = context.getImageData(sampleX, sampleY, sampleWidth, sampleHeight);
    let brightnessTotal = 0;
    const grayscale = new Float32Array(sampleWidth * sampleHeight);

    for (let i = 0, pixelIndex = 0; i < data.length; i += 4, pixelIndex++) {
        const gray = 0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2];
        grayscale[pixelIndex] = gray;
        brightnessTotal += gray;
    }

    let sharpnessTotal = 0;
    for (let y = 1; y < sampleHeight - 1; y++) {
        for (let x = 1; x < sampleWidth - 1; x++) {
            const index = y * sampleWidth + x;
            const laplacian =
                4 * grayscale[index] -
                grayscale[index - 1] -
                grayscale[index + 1] -
                grayscale[index - sampleWidth] -
                grayscale[index + sampleWidth];
            sharpnessTotal += Math.abs(laplacian);
        }
    }

    return {
        brightness: brightnessTotal / grayscale.length,
        sharpness: sharpnessTotal / ((sampleWidth - 2) * (sampleHeight - 2)),
    };
}

function isFrameQualityGood(quality) {
    return quality.brightness >= MIN_BRIGHTNESS && quality.brightness <= MAX_BRIGHTNESS && quality.sharpness >= 8;
}

function averageDescriptors(samples) {
    const averaged = new Array(samples[0].length).fill(0);
    samples.forEach((sample) => {
        sample.forEach((value, index) => {
            averaged[index] += value;
        });
    });
    return averaged.map((value) => value / samples.length);
}

function captureEnrollmentImage() {
    if (!video.videoWidth || !video.videoHeight) return null;

    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    const context = canvas.getContext('2d');
    context.drawImage(video, 0, 0, canvas.width, canvas.height);

    return canvas.toDataURL('image/jpeg', 0.85);
}

async function captureSample(descriptor, quality) {
    if (Date.now() - lastCaptureAt < 350 || descriptors.length >= REQUIRED_SAMPLES) return;

    lastCaptureAt = Date.now();
    if (!enrollmentImage) {
        enrollmentImage = captureEnrollmentImage();
    }
    descriptors.push(Array.from(descriptor));
    sampleQualities.push(quality);
    updateSampleCount();

    if (descriptors.length === 1) {
        guideInstruction.textContent = 'Sampel pertama tersimpan. Sekarang hadapkan wajah ke kanan.';
        updateStatus('Sampel pertama berhasil. Lanjutkan dengan menghadapkan wajah ke kanan.');
        return;
    }

    if (descriptors.length === 2) {
        guideInstruction.textContent = 'Sampel kedua tersimpan. Sekarang hadapkan wajah ke kiri.';
        updateStatus('Sampel kedua berhasil. Lanjutkan dengan menghadapkan wajah ke kiri.');
        return;
    }

    guideInstruction.textContent = 'Semua sampel lengkap. Menyimpan data wajah.';
    updateStatus(`Sampel otomatis tersimpan (${descriptors.length}/${REQUIRED_SAMPLES}).`);
    await saveEmbedding();
}

function stopEnrollmentTracking() {
    trackingActive = false;
    updateFrameIndicator(false);
    if (enrollmentFrame) {
        window.cancelAnimationFrame(enrollmentFrame);
        enrollmentFrame = null;
    }
}

function stopCameraStream() {
    if (stream) {
        stream.getTracks().forEach((track) => track.stop());
        stream = null;
    }
    if (video) {
        video.pause();
        video.srcObject = null;
    }
    setScanAnimationActive(false);
}

function cameraErrorMessage(error) {
    if (/tidak merespons di PWA|belum bisa diputar|belum siap/i.test(error?.message || '')) {
        return error.message;
    }

    if (error?.name === 'NotAllowedError' || error?.name === 'SecurityError') {
        return 'Izin kamera ditolak. Buka pengaturan browser, izinkan kamera untuk situs ini, lalu coba lagi.';
    }

    if (error?.name === 'NotFoundError' || error?.name === 'DevicesNotFoundError') {
        return 'Kamera depan tidak ditemukan di perangkat ini.';
    }

    if (error?.name === 'NotReadableError' || error?.name === 'TrackStartError') {
        return 'Kamera sedang dipakai aplikasi lain. Tutup aplikasi kamera/meeting lain, lalu coba lagi.';
    }

    if (error?.name === 'AbortError' || /aborted/i.test(error?.message || '')) {
        return 'Kamera batal dinyalakan oleh browser. Tutup tab/aplikasi lain yang memakai kamera, lalu tekan Aktifkan Kamera lagi.';
    }

    if (error?.name === 'OverconstrainedError') {
        return 'Pengaturan kamera tidak cocok dengan perangkat. Coba aktifkan kamera lagi.';
    }

    return error?.message || 'Kamera atau model wajah gagal diinisialisasi.';
}

function waitForVideoReady(timeoutMs = CAMERA_VIDEO_READY_TIMEOUT_MS) {
    if (video.readyState >= 2 && video.videoWidth > 0 && video.videoHeight > 0) {
        return Promise.resolve();
    }

    return new Promise((resolve, reject) => {
        const timeout = window.setTimeout(() => {
            cleanup();
            reject(new Error('Gambar kamera belum siap. Coba tekan Aktifkan Kamera lagi.'));
        }, timeoutMs);

        const check = () => {
            if (video.readyState >= 2 && video.videoWidth > 0 && video.videoHeight > 0) {
                cleanup();
                resolve();
            }
        };

        const cleanup = () => {
            window.clearTimeout(timeout);
            video.removeEventListener('loadedmetadata', check);
            video.removeEventListener('loadeddata', check);
            video.removeEventListener('canplay', check);
            video.removeEventListener('playing', check);
        };

        video.addEventListener('loadedmetadata', check);
        video.addEventListener('loadeddata', check);
        video.addEventListener('canplay', check);
        video.addEventListener('playing', check);
        check();
    });
}

function delay(ms) {
    return new Promise((resolve) => {
        window.setTimeout(resolve, ms);
    });
}

function prepareCameraVideo() {
    video.muted = true;
    video.defaultMuted = true;
    video.autoplay = true;
    video.playsInline = true;
    video.setAttribute('muted', '');
    video.setAttribute('autoplay', '');
    video.setAttribute('playsinline', '');
    video.setAttribute('webkit-playsinline', '');
}

async function startVideoPreview() {
    prepareCameraVideo();

    let playError = null;
    const playPromise = video.play();

    if (playPromise?.catch) {
        playPromise.catch((error) => {
            playError = error;
        });
    }

    try {
        await waitForVideoReady();
    } catch (error) {
        if (playError) {
            throw playError;
        }

        throw error;
    }

    if (playPromise?.then) {
        await Promise.race([
            playPromise.catch(() => null),
            delay(1200),
        ]);
    }
}

function withTimeout(promise, timeoutMs, message) {
    return new Promise((resolve, reject) => {
        let settled = false;
        const timeout = window.setTimeout(() => {
            settled = true;
            const error = new Error(message);
            error.name = 'TimeoutError';
            reject(error);
        }, timeoutMs);

        promise
            .then((value) => {
                if (settled) {
                    if (value?.getTracks) {
                        value.getTracks().forEach((track) => track.stop());
                    }
                    return;
                }

                settled = true;
                window.clearTimeout(timeout);
                resolve(value);
            })
            .catch((error) => {
                if (settled) return;

                settled = true;
                window.clearTimeout(timeout);
                reject(error);
            });
    });
}

async function openCameraStream() {
    if (!navigator.mediaDevices?.getUserMedia) {
        throw new Error('Browser tidak mendukung akses kamera. Gunakan Safari/Chrome terbaru lewat HTTPS.');
    }

    const constraints = [
        {
            video: {
                facingMode: { ideal: 'user' },
                width: { ideal: 480 },
                height: { ideal: 360 },
            },
            audio: false,
        },
        {
            video: {
                facingMode: { ideal: 'user' },
                width: { ideal: 320 },
                height: { ideal: 240 },
            },
            audio: false,
        },
        {
            video: true,
            audio: false,
        },
    ];

    let lastError = null;

    for (const constraint of constraints) {
        try {
            return await withTimeout(
                navigator.mediaDevices.getUserMedia(constraint),
                CAMERA_RESPONSE_TIMEOUT_MS,
                CAMERA_TIMEOUT_MESSAGE
            );
        } catch (error) {
            lastError = error;

            if (error?.name === 'TimeoutError') {
                break;
            }
        }
    }

    throw lastError || new Error('Kamera tidak bisa dinyalakan.');
}

function resetSamples() {
    descriptors.length = 0;
    sampleQualities.length = 0;
    enrollmentImage = null;
    lastCaptureAt = 0;
    isSaving = false;
    processingDetection = false;
    detectionErrorCount = 0;
    lastNoFaceFeedbackAt = 0;
    lastVideoWaitingFeedbackAt = 0;
    eyesWereOpen = false;
    lastEar = null;
    maxOpenEar = 0;
    blinkCloseFrames = 0;
    blinkCooldownUntil = 0;
    setBlinkVerified(false);
    updateSampleCount();
    updateFrameIndicator(false);
    guideInstruction.textContent = 'Arahkan wajah ke kamera, lalu kedipkan mata.';
    updateStatus(video.srcObject ? 'Sampel direset. Sistem akan mengambil otomatis saat wajah jelas.' : 'Siapkan kamera untuk mulai enrollment.');
}

async function loadModels() {
    if (!window.faceapi) {
        throw new Error('Library face recognition belum termuat. Periksa koneksi internet lalu refresh halaman.');
    }

    if (modelsLoaded) return;
    if (modelsPromise) {
        await modelsPromise;
        return;
    }

    updateStatus('Memuat face recognition...');
    modelsPromise = Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri(modelBaseUrl),
        faceapi.nets.faceLandmark68TinyNet.loadFromUri(modelBaseUrl),
        faceapi.nets.faceRecognitionNet.loadFromUri(modelBaseUrl),
    ]);

    try {
        await modelsPromise;
    } catch (error) {
        modelsPromise = null;
        throw new Error('face recognition belum tersedia di ' + modelBaseUrl + '.');
    }

    modelsLoaded = true;
    updateStatus('Face recognition siap. Aktifkan kamera untuk mulai scan otomatis.');
}

function startEnrollmentTracking() {
    stopEnrollmentTracking();
    trackingActive = true;
    lastDetectionAt = 0;

    const runTracking = async () => {
        if (!trackingActive) return;
        enrollmentFrame = window.requestAnimationFrame(runTracking);

        if (!video.srcObject || processingDetection || isSaving) return;

        const now = performance.now();
        if (now - lastDetectionAt < 60) return;

        if (video.readyState < 2 || !video.videoWidth || !video.videoHeight) {
            if (now - lastVideoWaitingFeedbackAt > 1200) {
                lastVideoWaitingFeedbackAt = now;
                updateStatus('Menunggu gambar kamera siap...', true);
            }
            return;
        }

        processingDetection = true;
        lastDetectionAt = now;

        try {
            const detection = await faceapi
                .detectSingleFace(video, detectorOptions)
                .withFaceLandmarks(true)
                .withFaceDescriptor();

            if (!detection) {
                updateFrameIndicator(false);
                guideInstruction.textContent = 'Dekatkan wajah ke tengah bingkai.';
                if (now - lastNoFaceFeedbackAt > 1200) {
                    lastNoFaceFeedbackAt = now;
                    updateStatus('Wajah belum terdeteksi. Pastikan wajah masuk lingkaran dan cahaya cukup.', true);
                }
                return;
            }

            detectionErrorCount = 0;
            const isInsideGuide = isFaceInsideGuide(detection.detection.box);
            const quality = getFrameQuality(detection.detection.box);
            const isClear = isInsideGuide && isFrameQualityGood(quality);
            const headTurnDirection = getHeadTurnDirection(detection.landmarks, detection.detection.box);
            updateFrameIndicator(isClear);

            if (detectBlink(detection.landmarks) && !blinkVerified) {
                setBlinkVerified(true);
                updateStatus('Kedipan berhasil. Sampel pertama siap disimpan.');
                if (isClear && descriptors.length === 0) {
                    await captureSample(detection.descriptor, quality);
                    return;
                }
            }

            const blinkWindowActive = blinkVerified && (Date.now() - blinkVerifiedAt <= BLINK_CAPTURE_WINDOW_MS);

            if (!isInsideGuide) {
                guideInstruction.textContent = 'Posisikan wajah di tengah bingkai.';
                return;
            }

            if (!isFrameQualityGood(quality)) {
                guideInstruction.textContent = 'Tahan wajah tetap stabil di pencahayaan yang cukup.';
                updateStatus(`Wajah belum cukup jelas. Cahaya ${Math.round(quality.brightness)}, ketajaman ${Math.round(quality.sharpness)}.`, true);
                return;
            }

            if (descriptors.length === 0 && !blinkWindowActive) {
                guideInstruction.textContent = 'Kedipkan mata untuk verifikasi.';
                updateStatus('Wajah sudah jelas. Kedipkan mata satu kali untuk mengambil sampel pertama.');
                return;
            }

            if (descriptors.length === 0 && blinkWindowActive) {
                guideInstruction.textContent = 'Kedipan terdeteksi. Menyimpan sampel pertama...';
                await captureSample(detection.descriptor, quality);
                return;
            }

            if (descriptors.length === 1 && headTurnDirection !== 'right') {
                const feedback = getHeadTurnFeedback(headTurnDirection, 1);
                guideInstruction.textContent = feedback.guide;
                updateStatus(feedback.status, true);
                return;
            }

            if (descriptors.length === 2 && headTurnDirection !== 'left') {
                const feedback = getHeadTurnFeedback(headTurnDirection, 2);
                guideInstruction.textContent = feedback.guide;
                updateStatus(feedback.status, true);
                return;
            }

            await captureSample(detection.descriptor, quality);
        } catch (error) {
            detectionErrorCount += 1;
            console.error('Face enrollment detection error:', error);
            updateFrameIndicator(false);

            if (detectionErrorCount >= 3) {
                updateStatus('Deteksi wajah belum berjalan stabil. Tekan Reset, lalu aktifkan kamera lagi.', true);
                return;
            }

            updateStatus('Menyiapkan deteksi wajah, tahan posisi sebentar...', true);
        } finally {
            processingDetection = false;
        }
    };

    runTracking();
}

async function startCamera() {
    const token = ++cameraStartToken;
    let slowStartTimer = null;

    try {
        startCameraButton.disabled = true;
        stopEnrollmentTracking();
        stopCameraStream();
        resetSamples();
        updateStatus('Menyalakan kamera...');
        slowStartTimer = window.setTimeout(() => {
            if (token === cameraStartToken && !stream) {
                updateStatus('Kamera masih belum merespons. Jika tetap seperti ini, gunakan Buka di Safari.', true);
            }
        }, 3500);

        const nextStream = await openCameraStream();

        if (token !== cameraStartToken) {
            nextStream.getTracks().forEach((track) => track.stop());
            return;
        }

        stream = nextStream;

        video.srcObject = stream;
        await startVideoPreview();
        setScanAnimationActive(true);
        updateStatus('Kamera aktif. Menyiapkan deteksi wajah...');
        await loadModels();
        startEnrollmentTracking();
        updateStatus('Kamera aktif. Sistem akan mengambil sampel otomatis saat wajah jelas.');
    } catch (error) {
        stopEnrollmentTracking();
        stopCameraStream();
        updateStatus(cameraErrorMessage(error), true);
    } finally {
        if (slowStartTimer) {
            window.clearTimeout(slowStartTimer);
        }
        startCameraButton.disabled = false;
    }
}

async function saveEmbedding() {
    if (descriptors.length !== REQUIRED_SAMPLES || isSaving) return;

    isSaving = true;
    stopEnrollmentTracking();
    updateStatus('Sampel lengkap. Menyimpan data wajah...');
    guideInstruction.textContent = 'Sampel lengkap. Menyimpan data wajah.';

    try {
        const response = await fetch(window.location.origin + "/face/enroll", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({
                embedding: averageDescriptors(descriptors),
                descriptor_samples: descriptors,
                image: enrollmentImage,
                blink_verified: true,
                quality_metrics: {
                    sample_count: sampleQualities.length,
                    min_brightness: Math.min(...sampleQualities.map((sample) => sample.brightness)),
                    max_brightness: Math.max(...sampleQualities.map((sample) => sample.brightness)),
                    min_sharpness: Math.min(...sampleQualities.map((sample) => sample.sharpness)),
                },
            }),
        });

        const result = await response.json();

        if (!response.ok) {
            throw new Error(result.message || 'Gagal menyimpan data wajah.');
        }

        updateStatus(result.message || 'Data wajah berhasil disimpan.');
        stopCameraStream();
        window.location.href = result.redirect;
    } catch (error) {
        isSaving = false;
        startEnrollmentTracking();
        updateStatus(error.message || 'Terjadi kesalahan saat menyimpan data wajah.', true);
    }
}

startCameraButton.addEventListener('click', startCamera);
resetSamplesButton.addEventListener('click', resetSamples);
updateSampleCount();
setupSafariHandoff();
checkEnrollmentHandoffStatus();
loadModels().catch(() => {});
window.addEventListener('focus', checkEnrollmentHandoffStatus);
window.addEventListener('beforeunload', stopCameraStream);
document.addEventListener('visibilitychange', () => {
    if (document.hidden) {
        stopEnrollmentTracking();
        stopCameraStream();
        cameraStartToken += 1;
    } else {
        checkEnrollmentHandoffStatus();
    }
});
</script>
@endsection
