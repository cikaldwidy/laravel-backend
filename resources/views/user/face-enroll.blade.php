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
<div class="min-h-screen bg-white">

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

    <div class="px-4 md:px-14 lg:px-20 pb-10">
        <div class="max-w-6xl mx-auto bg-white rounded-md shadow-lg p-4 md:p-6">
            <div class="text-center mb-6">
                <h1 class="text-xl md:text-2xl font-bold text-gray-700 tracking-[0.4px]">Pendaftaran Wajah</h1>
                <p class="text-sm text-gray-400 mt-2">
                    Pastikan wajah Anda berada di dalam frame dan pencahayaan cukup.
                </p>
            </div>

            <div class="grid gap-6 lg:grid-cols-[1.1fr_0.9fr] items-start">
                <div>
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
                            <div id="guideArrow" class="absolute left-1/2 -bottom-8 -translate-x-1/2 text-white/95 text-4xl transition-all duration-500 ease-out">
                                <i class="fa-solid fa-arrow-up"></i>
                            </div>
                            </div>
                        </div>
                        <div class="absolute top-4 left-4 bg-blue-600/90 text-white text-xs px-3 py-2 rounded-full shadow">
                            Kamera aktif
                        </div>
                    </div>

                    <p id="status" class="mt-4 text-sm text-gray-500 text-center md:text-left">
                        Siapkan kamera untuk mulai enrollment.
                    </p>
                    <p id="guideInstruction" class="mt-2 text-sm font-semibold text-blue-600 text-center md:text-left">
                        Arahkan wajah lurus ke dalam bingkai.
                    </p>

                    <div class="mt-5 grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <button id="startCamera" class="sm:col-span-3 bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-md font-semibold text-sm shadow transition">
                            <i class="fa-solid fa-camera mr-2"></i>AKTIFKAN KAMERA
                        </button>
                        <button id="resetSamples" class="border border-red-200 text-red-500 hover:bg-red-50 px-4 py-3 rounded-md font-semibold text-sm transition">
                            RESET
                        </button>
                        <button id="captureSample" class="sm:col-span-2 bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-md font-semibold text-sm shadow disabled:opacity-50 disabled:cursor-not-allowed transition" disabled>
                            <i class="fa-solid fa-camera mr-2"></i>AMBIL SAMPEL
                        </button>
                    </div>
                </div>

                <div class="bg-[#fbfdff] border border-gray-100 rounded-md p-5">
                    <h2 class="text-base font-bold text-gray-800">Petunjuk</h2>
                    <div class="mt-4 space-y-4 text-sm text-gray-500">
                        <div class="flex gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                <i class="fa-regular fa-face-smile"></i>
                            </div>
                            <p>Hadapkan wajah ke kamera dan pastikan wajah cukup jelas.</p>
                        </div>
                        <div class="flex gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                <i class="fa-regular fa-lightbulb"></i>
                            </div>
                            <p>Gunakan pencahayaan yang cukup agar deteksi lebih stabil.</p>
                        </div>
                        <div class="flex gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-glasses"></i>
                            </div>
                            <p>Hindari aksesori yang menutupi wajah secara berlebihan.</p>
                        </div>
                        <div class="flex gap-3">
                            <div class="w-8 h-8 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-arrows-rotate"></i>
                            </div>
                            <p>Ambil beberapa sampel dengan posisi wajah sedikit berbeda.</p>
                        </div>
                    </div>

                    <div class="mt-8">
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
const captureSampleButton = document.getElementById('captureSample');
const statusText = document.getElementById('status');
const sampleCount = document.getElementById('sampleCount');
const canvas = document.getElementById('captureCanvas');
const sampleDots = [
    document.getElementById('sampleDot1'),
    document.getElementById('sampleDot2'),
    document.getElementById('sampleDot3'),
];
const headGuide = document.getElementById('headGuide');
const headFrame = document.getElementById('headFrame');
const headFrameGlow = document.getElementById('headFrameGlow');
const guideArrow = document.getElementById('guideArrow');
const guideInstruction = document.getElementById('guideInstruction');

const REQUIRED_SAMPLES = 3;
const descriptors = [];
let modelsLoaded = false;
let stream;
const modelBaseUrl = '/face-api/models';
const guideSteps = ['center', 'left', 'right'];
let enrollmentInterval = null;
let isSaving = false;
let lastCaptureAt = 0;
let modelsPromise = null;
let trackingActive = false;
let processingDetection = false;
let lastDetectionAt = 0;
const sampleQualities = [];
const detectorOptions = new faceapi.TinyFaceDetectorOptions({
    inputSize: 224,
    scoreThreshold: 0.5,
});
const MIN_BRIGHTNESS = 38;
const MAX_BRIGHTNESS = 210;
const MIN_SHARPNESS = 10;

function updateStatus(message, isError = false) {
    statusText.textContent = message;
    statusText.className = isError
        ? 'mt-4 text-sm text-red-500 text-center md:text-left'
        : 'mt-4 text-sm text-gray-500 text-center md:text-left';
}

function updateFrameIndicator(isValid) {
    if (isValid) {
        headFrame.className = 'absolute inset-4 rounded-full border-[6px] border-emerald-400/95 shadow-[0_0_0_10px_rgba(74,222,128,0.14)] transition-all duration-300';
        headFrameGlow.className = 'absolute inset-2 rounded-full bg-emerald-400/15 blur-md transition-all duration-300';
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
    updateGuideState(guideSteps[Math.min(descriptors.length, guideSteps.length - 1)]);
}

function updateGuideState(step) {
    if (step === 'left') {
        headGuide.style.transform = 'translateX(-14px) rotate(-10deg)';
        guideArrow.className = 'absolute -left-10 top-1/2 -translate-y-1/2 text-white/90 text-3xl transition-all duration-500 ease-out';
        guideArrow.innerHTML = '<i class="fa-solid fa-arrow-left"></i>';
        guideInstruction.textContent = 'Putar kepala sedikit ke kiri.';
        return;
    }

    if (step === 'right') {
        headGuide.style.transform = 'translateX(14px) rotate(10deg)';
        guideArrow.className = 'absolute -right-10 top-1/2 -translate-y-1/2 text-white/90 text-3xl transition-all duration-500 ease-out';
        guideArrow.innerHTML = '<i class="fa-solid fa-arrow-right"></i>';
        guideInstruction.textContent = 'Putar kepala sedikit ke kanan.';
        return;
    }

    headGuide.style.transform = 'translateX(0) rotate(0deg)';
    guideArrow.className = 'absolute left-1/2 -bottom-10 -translate-x-1/2 text-white/90 text-3xl transition-all duration-500 ease-out';
    guideArrow.innerHTML = '<i class="fa-solid fa-arrow-up"></i>';
    guideInstruction.textContent = 'Arahkan wajah lurus ke dalam bingkai.';
}

function getHeadPoseStep(landmarks) {
    const jaw = landmarks.getJawOutline();
    const nose = landmarks.getNose();
    const noseTip = nose[3];
    const leftJaw = jaw[0];
    const rightJaw = jaw[16];
    const ratio = (noseTip.x - leftJaw.x) / (rightJaw.x - leftJaw.x);

    if (ratio < 0.42) {
        return 'left';
    }

    if (ratio > 0.58) {
        return 'right';
    }

    return 'center';
}

function isFaceInsideGuide(box) {
    if (!video.videoWidth || !video.videoHeight) {
        return false;
    }

    const faceCenterX = box.x + (box.width / 2);
    const faceCenterY = box.y + (box.height / 2);
    const horizontalOffset = Math.abs((faceCenterX / video.videoWidth) - 0.5);
    const verticalOffset = Math.abs((faceCenterY / video.videoHeight) - 0.5);
    const faceWidthRatio = box.width / video.videoWidth;
    const faceHeightRatio = box.height / video.videoHeight;

    return horizontalOffset <= 0.12 &&
        verticalOffset <= 0.15 &&
        faceWidthRatio >= 0.22 &&
        faceWidthRatio <= 0.52 &&
        faceHeightRatio >= 0.32 &&
        faceHeightRatio <= 0.72;
}

function getFrameQuality(faceBox = null) {
    if (!video.videoWidth || !video.videoHeight) {
        return { brightness: 0, sharpness: 0 };
    }

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
    return quality.brightness >= MIN_BRIGHTNESS && quality.brightness <= MAX_BRIGHTNESS && quality.sharpness >= MIN_SHARPNESS;
}

function stopEnrollmentTracking() {
    trackingActive = false;
    updateFrameIndicator(false);
    if (enrollmentInterval) {
        window.cancelAnimationFrame(enrollmentInterval);
        enrollmentInterval = null;
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
    updateSampleCount();
    captureSampleButton.disabled = !video.srcObject;
    updateFrameIndicator(false);
    updateStatus('Sampel direset. Ikuti panduan lalu tekan Ambil Sampel.');
}

function startEnrollmentTracking() {
    stopEnrollmentTracking();
    trackingActive = true;
    lastDetectionAt = 0;

    const runTracking = async () => {
        if (!trackingActive) {
            return;
        }

        enrollmentInterval = window.requestAnimationFrame(runTracking);

        if (!video.srcObject || processingDetection) {
            return;
        }

        const now = performance.now();
        if (now - lastDetectionAt < 220) {
            return;
        }

        lastDetectionAt = now;

        try {
            const detection = await faceapi
                .detectSingleFace(video, detectorOptions)
                .withFaceLandmarks(true);

            if (!detection) {
                updateFrameIndicator(false);
                return;
            }

            const expectedStep = guideSteps[Math.min(descriptors.length, guideSteps.length - 1)];
            const currentStep = getHeadPoseStep(detection.landmarks);
            const isInsideGuide = isFaceInsideGuide(detection.detection.box);
            updateFrameIndicator(isInsideGuide && currentStep === expectedStep);
        } catch (error) {
            updateFrameIndicator(false);
        }
    };

    runTracking();
}

async function loadModels() {
    if (modelsLoaded) {
        return;
    }

    if (modelsPromise) {
        await modelsPromise;
        return;
    }

    updateStatus('Memuat model deteksi wajah...');

    modelsPromise = (async () => {
        await Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri(modelBaseUrl),
            faceapi.nets.faceLandmark68TinyNet.loadFromUri(modelBaseUrl),
            faceapi.nets.faceRecognitionNet.loadFromUri(modelBaseUrl),
        ]);
    })();

    try {
        await modelsPromise;
    } catch (error) {
        modelsPromise = null;
        throw new Error(
            'Model face-api belum tersedia di ' + modelBaseUrl +
            '. Tambahkan file model ke folder public/face-api/models terlebih dulu.'
        );
    }

    modelsLoaded = true;
    updateStatus('Model siap. Aktifkan kamera untuk mengambil sampel.');
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
        captureSampleButton.disabled = false;
        updateStatus('Kamera aktif. Menyiapkan deteksi wajah...');
        await loadModels();
        startEnrollmentTracking();
        updateStatus('Kamera aktif. Ikuti arah panduan dan tekan Ambil Sampel.');
    } catch (error) {
        updateStatus(error.message || 'Kamera atau model wajah gagal diinisialisasi.', true);
    }
}

async function captureSample() {
    if (!video.srcObject) {
        updateStatus('Aktifkan kamera terlebih dulu.', true);
        return;
    }

    if (descriptors.length >= REQUIRED_SAMPLES) {
        updateStatus('Sampel sudah lengkap. Tekan reset jika ingin mengulang.', true);
        return;
    }

    if (processingDetection) {
        return;
    }

    processingDetection = true;

    try {
        const detection = await faceapi
            .detectSingleFace(video, detectorOptions)
            .withFaceLandmarks(true)
            .withFaceDescriptor();

        if (!detection) {
            updateStatus('Wajah belum terdeteksi. Pastikan wajah berada di dalam bingkai.', true);
            return;
        }

        const expectedStep = guideSteps[descriptors.length];
        const currentStep = getHeadPoseStep(detection.landmarks);
        const quality = getFrameQuality(detection.detection.box);
        const isInsideGuide = isFaceInsideGuide(detection.detection.box);

        updateFrameIndicator(isInsideGuide && currentStep === expectedStep);

        if (!isInsideGuide) {
            updateStatus('Posisikan wajah lebih pas di dalam lingkaran sampai indikator berubah hijau.', true);
            return;
        }

        if (currentStep !== expectedStep) {
            updateStatus(`Ikuti instruksi: ${guideInstruction.textContent}`, true);
            return;
        }

        if (!isFrameQualityGood(quality)) {
            updateStatus(`Wajah belum cukup jelas. Cahaya ${Math.round(quality.brightness)}, ketajaman ${Math.round(quality.sharpness)}.`, true);
            return;
        }

        const now = Date.now();
        if (now - lastCaptureAt < 700) {
            return;
        }

        lastCaptureAt = now;
        descriptors.push(Array.from(detection.descriptor));
        sampleQualities.push(quality);
        updateSampleCount();

        if (descriptors.length === REQUIRED_SAMPLES) {
            captureSampleButton.disabled = true;
            updateStatus('Sampel lengkap. Menyimpan data wajah...');
            guideInstruction.textContent = 'Sampel lengkap. Menyimpan data wajah.';
            await saveEmbedding();
        } else {
            updateStatus('Sampel berhasil diambil. Lanjutkan ke posisi berikutnya.');
        }
    } finally {
        processingDetection = false;
    }
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

async function saveEmbedding() {
    if (descriptors.length !== REQUIRED_SAMPLES || isSaving) {
        updateStatus('Lengkapi 3 sampel sebelum menyimpan.', true);
        return;
    }

    isSaving = true;
    updateStatus('Menyimpan data wajah ke server...');

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
        captureSampleButton.disabled = false;
        updateStatus(error.message || 'Terjadi kesalahan saat menyimpan data wajah.', true);
    }
}

startCameraButton.addEventListener('click', startCamera);
resetSamplesButton.addEventListener('click', resetSamples);
captureSampleButton.addEventListener('click', captureSample);
updateSampleCount();
window.addEventListener('load', () => {
    loadModels().catch(() => {
        // error detail tetap ditampilkan saat user menekan tombol mulai
    });
});
window.addEventListener('beforeunload', stopCameraStream);
</script>
@endsection
