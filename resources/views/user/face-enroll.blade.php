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
    max-width: 65rem;
}

.face-enroll-stage {
    aspect-ratio: 18 / 9;
    background: linear-gradient(180deg, #f8fafc 0%, #eef2ff 100%);
}

.face-guide {
    width: min(80vw, 26rem);
    height: min(80vw, 26rem);
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
        width: min(84vw, 19rem);
        height: min(84vw, 19rem);
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

<div class="flex flex-1 items-center justify-center py-7">
    <div class="face-enroll-shell p-4 md:p-6">
        <div class="mx-auto">
            <div class="mb-6 w-full bg-gray-50 border border-gray-100 rounded-lg p-4 text-left">
                <h3 class="text-lg  font-semibold text-gray-700 tracking-[.5px]">Petunjuk</h3>
                <div class="mt-4 space-y-3 text-sm text-gray-500 tracking-[.3px]">
                    <p class="flex gap-3">
                        <span class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                            <i class="fa-regular fa-face-smile"></i>
                        </span>
                        <span>Hadapkan wajah ke kamera dan pastikan posisi berada di tengah bingkai.</span>
                    </p>
                    <p class="flex gap-3">
                        <span class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                            <i class="fa-regular fa-lightbulb"></i>
                        </span>
                        <span>Gunakan pencahayaan cukup agar sistem dapat membaca wajah dengan stabil.</span>
                    </p>
                    <p class="flex gap-3">
                        <span class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-arrows-rotate"></i>
                        </span>
                        <span>Sampel akan tersimpan otomatis setelah wajah jelas dan kedipan terverifikasi.</span>
                    </p>
                </div>
            </div>

            <div id="enrollStage" class="face-enroll-stage relative overflow-hidden">
                <video id="video" autoplay muted playsinline class="w-full h-full object-cover opacity-0 transition-opacity duration-300"></video>
                <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
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
                </div>
                <div id="cameraBadge" class="absolute left-1/2 bottom-4 -translate-x-1/2 bg-red-500 text-white text-xs md:text-sm px-3 py-2 rounded-full shadow-sm tracking-[.3px]">
                    Kamera belum aktif
                </div>
            </div>

            <p id="status" class="mt-4 text-sm text-gray-500 text-center tracking-[.3px]">
                Siapkan kamera untuk mulai enrollment.
            </p>
            <p id="guideInstruction" class="mt-3 text-sm font-medium text-orange-500 text-center tracking-[.3px]">
                Arahkan wajah ke kamera, lalu kedipkan mata.
            </p>

            <div class="mt-6">
                <div class="rounded-md border border-blue-100 bg-blue-50/50 p-4 sm:p-5 shadow-sm">
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

            <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-3 max-w-xl mx-auto">
                <button id="startCamera" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-md hover:shadow-lg transition text-sm tracking-[.5px]">
                    <i class="fa-solid fa-camera mr-2"></i>AKTIFKAN KAMERA
                </button>
                <button id="resetSamples" class="w-full border border-red-200 text-red-500 hover:bg-red-50 font-semibold py-3 rounded-md transition text-sm tracking-[.5px]">
                    RESET
                </button>
            </div>

            <canvas id="captureCanvas" class="hidden"></canvas>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
const video = document.getElementById('video');
const cameraBadge = document.getElementById('cameraBadge');
const startCameraButton = document.getElementById('startCamera');
const resetSamplesButton = document.getElementById('resetSamples');
const statusText = document.getElementById('status');
const sampleCount = document.getElementById('sampleCount');
const sampleProgressBar = document.getElementById('sampleProgressBar');
const sampleStepNote = document.getElementById('sampleStepNote');
const canvas = document.getElementById('captureCanvas');
const headGuide = document.getElementById('headGuide');
const headFrame = document.getElementById('headFrame');
const guideInstruction = document.getElementById('guideInstruction');
const scanTrack = document.querySelector('.face-scan-track');

const REQUIRED_SAMPLES = 3;
const descriptors = [];
const sampleQualities = [];
const modelBaseUrl = '/face-api/models';
const detectorOptions = new faceapi.TinyFaceDetectorOptions({
    inputSize: 224,
    scoreThreshold: 0.5,
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
    if (cameraBadge) {
        cameraBadge.textContent = isActive ? 'Kamera aktif' : 'Kamera belum aktif';
        cameraBadge.className = isActive
            ? 'absolute left-1/2 bottom-4 -translate-x-1/2 bg-blue-600/90 text-white text-[11px] sm:text-xs px-3 py-2 rounded-full shadow'
            : 'absolute left-1/2 bottom-4 -translate-x-1/2 bg-gray-200 text-gray-600 text-[11px] sm:text-xs px-3 py-2 rounded-full shadow-sm';
    }
}

function updateStatus(message, isError = false) {
    statusText.textContent = message;
    statusText.className = isError
        ? 'mt-4 text-sm text-red-500 text-center md:text-left'
        : 'mt-4 text-sm text-gray-500 text-center md:text-left';
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

async function captureSample(descriptor, quality) {
    if (Date.now() - lastCaptureAt < 350 || descriptors.length >= REQUIRED_SAMPLES) return;

    lastCaptureAt = Date.now();
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
    setScanAnimationActive(false);
}

function resetSamples() {
    descriptors.length = 0;
    sampleQualities.length = 0;
    lastCaptureAt = 0;
    isSaving = false;
    processingDetection = false;
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

        processingDetection = true;
        lastDetectionAt = now;

        try {
            const detection = await faceapi
                .detectSingleFace(video, detectorOptions)
                .withFaceLandmarks(true)
                .withFaceDescriptor();

            if (!detection) {
                updateFrameIndicator(false);
                updateStatus('Wajah belum terdeteksi. Posisikan wajah di dalam bingkai.', true);
                return;
            }

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
        } finally {
            processingDetection = false;
        }
    };

    runTracking();
}

async function startCamera() {
    try {
        stopEnrollmentTracking();
        stopCameraStream();
        resetSamples();
        updateStatus('Menyalakan kamera...');

        stream = await navigator.mediaDevices.getUserMedia({
            video: {
                facingMode: 'user',
                width: { ideal: 480 },
                height: { ideal: 360 },
                frameRate: { ideal: 24, max: 24 }
            },
            audio: false
        });

        video.srcObject = stream;
        await video.play();
        setScanAnimationActive(true);
        updateStatus('Kamera aktif. Menyiapkan deteksi wajah...');
        await loadModels();
        startEnrollmentTracking();
        updateStatus('Kamera aktif. Sistem akan mengambil sampel otomatis saat wajah jelas.');
    } catch (error) {
        updateStatus(error.message || 'Kamera atau model wajah gagal diinisialisasi.', true);
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
window.addEventListener('load', () => {
    loadModels().catch(() => {});
});
window.addEventListener('beforeunload', stopCameraStream);
</script>
@endsection
