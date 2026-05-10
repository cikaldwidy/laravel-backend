@extends('layouts.app')

@section('title', 'Pendaftaran Wajah')

@section('content')
<style>
@keyframes face-scan-line {
    0% { transform: translateX(-50%) translateY(0); opacity: 0.55; }
    50% { transform: translateX(-50%) translateY(132px); opacity: 1; }
    100% { transform: translateX(-50%) translateY(0); opacity: 0.55; }
}
</style>

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

<div class="flex flex-1 items-center justify-center px-8 md:px-14 lg:px-20 py-7">
    <div class="w-full bg-white rounded-md shadow-lg p-2 md:p-5 flex flex-col md:flex-row gap-8">
        <div class="md:w-1/2 flex flex-col justify-center items-center text-center border-b md:border-b-0 md:border-r border-gray-100 pb-6 md:pb-0 md:pr-8">
            <img src="{{ asset('img/img-login.jpg') }}" class="w-[200px] h-auto mb-4">
            <h2 class="text-3xl font-bold text-gray-700 tracking-[.5px]">
                Pendaftaran Wajah
            </h2>
            <p class="text-gray-400 text-sm mt-2 max-w-xs">
                Pastikan wajah berada di dalam frame, pencahayaan cukup, dan ikuti instruksi kedipan untuk menyimpan sampel.
            </p>

            <div class="mt-8 w-full max-w-sm bg-gray-50 border border-gray-100 rounded-lg p-4 text-left">
                <h3 class="text-base font-bold text-gray-700">Petunjuk</h3>
                <div class="mt-4 space-y-3 text-sm text-gray-500">
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
        </div>

        <div class="md:w-1/2">
            <h3 class="text-xl font-bold text-gray-700 mb-3 tracking-[.5px]">
                Scan Wajah
            </h3>

            <div class="relative rounded-md overflow-hidden bg-slate-100 border border-gray-100 aspect-[4/3]">
                <video id="video" autoplay muted playsinline class="w-full h-full object-cover"></video>
                <div class="absolute inset-5 border-4 border-white rounded-2xl pointer-events-none"></div>
                <div class="absolute inset-0 pointer-events-none flex items-center justify-center">
                    <div id="headGuide" class="relative w-64 h-64 md:w-80 md:h-80 transition-transform duration-500 ease-out">
                        <div id="headFrameGlow" class="absolute inset-2 rounded-full bg-red-500/10 blur-md transition-all duration-300"></div>
                        <div id="headFrame" class="absolute inset-4 rounded-full border-[6px] border-red-400/90 shadow-[0_0_0_10px_rgba(248,113,113,0.12)] transition-all duration-300"></div>
                        <div id="scanLine" class="absolute top-[15%] bottom-[15%] left-1/2 w-[2px] -translate-x-1/2 bg-gradient-to-b from-transparent via-cyan-200 to-transparent opacity-90" style="animation: face-scan-line 2.4s ease-in-out infinite;"></div>
                        <div class="absolute left-1/2 top-[15%] bottom-[15%] w-px -translate-x-1/2 bg-cyan-100/60"></div>
                        <div class="absolute top-1/2 left-[15%] right-[15%] h-px -translate-y-1/2 bg-white/20"></div>
                    </div>
                </div>
                <div class="absolute top-4 left-4 bg-blue-600/90 text-white text-xs px-3 py-2 rounded-full shadow">
                    Kamera aktif
                </div>
            </div>

            <p id="status" class="mt-4 text-sm text-gray-500">
                Siapkan kamera untuk mulai enrollment.
            </p>
            <p id="guideInstruction" class="mt-2 text-sm font-semibold text-blue-600">
                Arahkan wajah ke kamera, lalu kedipkan mata.
            </p>

            <div class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-3">
                <button id="startCamera" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-md hover:shadow-lg transition text-sm tracking-[.5px]">
                    <i class="fa-solid fa-camera mr-2"></i>AKTIFKAN KAMERA
                </button>
                <button id="resetSamples" class="w-full border border-red-200 text-red-500 hover:bg-red-50 font-semibold py-3 rounded-md transition text-sm tracking-[.5px]">
                    RESET
                </button>
            </div>

            <div class="mt-6 grid gap-4 sm:grid-cols-2">
                <div class="rounded-lg border border-gray-200 p-4">
                    <div class="flex items-center justify-between text-sm font-semibold">
                        <p class="text-gray-700">Verifikasi Kedipan</p>
                        <p id="blinkStatus" class="text-gray-500">Belum terverifikasi</p>
                    </div>
                </div>

                <div class="rounded-lg border border-gray-200 p-4">
                    <div class="flex items-center justify-between text-sm font-semibold text-gray-700">
                        <p>Progres Sampel</p>
                        <p id="sampleCount">0 / 3</p>
                    </div>

                    <div class="mt-4 flex items-center justify-center gap-3">
                        <span id="sampleDot1" class="w-3 h-3 rounded-full bg-gray-200"></span>
                        <span id="sampleDot2" class="w-3 h-3 rounded-full bg-gray-200"></span>
                        <span id="sampleDot3" class="w-3 h-3 rounded-full bg-gray-200"></span>
                    </div>
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
const statusText = document.getElementById('status');
const sampleCount = document.getElementById('sampleCount');
const blinkStatus = document.getElementById('blinkStatus');
const canvas = document.getElementById('captureCanvas');
const sampleDots = [
    document.getElementById('sampleDot1'),
    document.getElementById('sampleDot2'),
    document.getElementById('sampleDot3'),
];
const headFrame = document.getElementById('headFrame');
const headFrameGlow = document.getElementById('headFrameGlow');
const guideInstruction = document.getElementById('guideInstruction');

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
const BLINK_OPEN_EAR = 0.22;
const BLINK_CLOSED_EAR = 0.19;
const BLINK_DROP_RATIO = 0.72;

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
let eyesWereOpen = false;
let lastEar = null;
let maxOpenEar = 0;

function updateStatus(message, isError = false) {
    statusText.textContent = message;
    statusText.className = isError
        ? 'mt-4 text-sm text-red-500 text-center md:text-left'
        : 'mt-4 text-sm text-gray-500 text-center md:text-left';
}

function setBlinkVerified(value) {
    blinkVerified = value;
    blinkStatus.textContent = value ? 'Terverifikasi' : 'Belum terverifikasi';
    blinkStatus.className = value ? 'text-blue-600' : 'text-gray-500';
}

function updateFrameIndicator(isValid) {
    if (isValid) {
        headFrame.className = 'absolute inset-4 rounded-full border-[6px] border-blue-400/95 shadow-[0_0_0_10px_rgba(96,165,250,0.14)] transition-all duration-300';
        headFrameGlow.className = 'absolute inset-2 rounded-full bg-blue-400/15 blur-md transition-all duration-300';
        return;
    }

    headFrame.className = 'absolute inset-4 rounded-full border-[6px] border-red-400/90 shadow-[0_0_0_10px_rgba(248,113,113,0.12)] transition-all duration-300';
    headFrameGlow.className = 'absolute inset-2 rounded-full bg-red-500/10 blur-md transition-all duration-300';
}

function updateSampleCount() {
    sampleCount.textContent = `${descriptors.length} / ${REQUIRED_SAMPLES}`;
    sampleDots.forEach((dot, index) => {
        dot.className = index < descriptors.length
            ? 'w-3 h-3 rounded-full bg-blue-600'
            : 'w-3 h-3 rounded-full bg-gray-200';
    });
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
    const ear = (calculateEyeAspectRatio(landmarks.getLeftEye()) + calculateEyeAspectRatio(landmarks.getRightEye())) / 2;
    lastEar = ear;

    maxOpenEar = Math.max(maxOpenEar, ear);

    if (ear > BLINK_OPEN_EAR) {
        eyesWereOpen = true;
        return false;
    }

    return eyesWereOpen && (ear < BLINK_CLOSED_EAR || (maxOpenEar > 0 && ear <= maxOpenEar * BLINK_DROP_RATIO));
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

    updateStatus('Memuat model deteksi wajah...');
    modelsPromise = Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri(modelBaseUrl),
        faceapi.nets.faceLandmark68TinyNet.loadFromUri(modelBaseUrl),
        faceapi.nets.faceRecognitionNet.loadFromUri(modelBaseUrl),
    ]);

    try {
        await modelsPromise;
    } catch (error) {
        modelsPromise = null;
        throw new Error('Model face-api belum tersedia di ' + modelBaseUrl + '.');
    }

    modelsLoaded = true;
    updateStatus('Model siap. Aktifkan kamera untuk mulai scan otomatis.');
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
        if (now - lastDetectionAt < 180) return;

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
            updateFrameIndicator(isClear);

            if (detectBlink(detection.landmarks) && !blinkVerified) {
                setBlinkVerified(true);
                updateStatus('Kedipan berhasil. Menunggu wajah cukup jelas untuk menyimpan sampel.');
            } else if (!blinkVerified && lastEar !== null) {
                blinkStatus.textContent = `Kedipkan mata (${lastEar.toFixed(2)})`;
            }

            if (!isInsideGuide) {
                guideInstruction.textContent = 'Posisikan wajah di tengah bingkai.';
                return;
            }

            if (!isFrameQualityGood(quality)) {
                guideInstruction.textContent = 'Tahan wajah tetap stabil di pencahayaan yang cukup.';
                updateStatus(`Wajah belum cukup jelas. Cahaya ${Math.round(quality.brightness)}, ketajaman ${Math.round(quality.sharpness)}.`, true);
                return;
            }

            if (!blinkVerified) {
                guideInstruction.textContent = 'Kedipkan mata untuk verifikasi.';
                updateStatus('Wajah sudah jelas. Kedipkan mata satu kali.');
                return;
            }

            if (Date.now() - lastCaptureAt < 700 || descriptors.length >= REQUIRED_SAMPLES) return;

            lastCaptureAt = Date.now();
            descriptors.push(Array.from(detection.descriptor));
            sampleQualities.push(quality);
            updateSampleCount();
            guideInstruction.textContent = 'Sampel otomatis tersimpan. Tetap hadapkan wajah ke kamera.';
            updateStatus(`Sampel otomatis tersimpan (${descriptors.length}/${REQUIRED_SAMPLES}).`);

            if (descriptors.length === REQUIRED_SAMPLES) {
                await saveEmbedding();
            }
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
                blink_verified: blinkVerified ? 'true' : 'false',
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
