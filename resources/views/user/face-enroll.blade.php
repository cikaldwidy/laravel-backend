@extends('layouts.app')

@section('title', 'Pendaftaran Wajah')

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
                <h1 class="text-xl md:text-2xl font-bold text-gray-800 tracking-[0.4px]">Pendaftaran Wajah</h1>
                <p class="text-sm text-blue-600 font-semibold mt-1">Langkah 2 dari 4</p>
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
                            <div id="headGuide" class="relative w-40 h-40 md:w-48 md:h-48 transition-transform duration-500 ease-out">
                                <div class="absolute inset-0 rounded-full border-2 border-white/70 shadow-[0_0_0_10px_rgba(255,255,255,0.08)]"></div>
                                <div class="absolute left-1/2 top-[24%] w-10 h-10 -translate-x-1/2 rounded-full border-2 border-white/80"></div>
                                <div class="absolute left-1/2 top-[42%] w-[68px] h-[52px] -translate-x-1/2 rounded-[45%] border-2 border-white/80"></div>
                                <div class="absolute left-1/2 bottom-[12%] w-[92px] h-[48px] -translate-x-1/2 rounded-b-[60px] border-2 border-t-0 border-white/80"></div>
                                <div id="guideArrow" class="absolute -right-10 top-1/2 -translate-y-1/2 text-white/90 text-3xl transition-all duration-500 ease-out">
                                    <i class="fa-solid fa-arrow-right"></i>
                                </div>
                                <div id="guidePulse" class="absolute inset-0 rounded-full border-2 border-blue-300/60 animate-ping"></div>
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
                            <i class="fa-solid fa-camera mr-2"></i>MULAI PENDAFTARAN WAJAH
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

                    <div class="mt-8 rounded-md border border-blue-100 bg-blue-50/60 p-4 text-sm text-gray-500">
                        Data user: <span class="font-semibold text-gray-700">{{ auth()->user()->name }}</span><br>
                        Email: <span class="font-semibold text-gray-700">{{ auth()->user()->email }}</span>
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
const statusText = document.getElementById('status');
const sampleCount = document.getElementById('sampleCount');
const canvas = document.getElementById('captureCanvas');
const sampleDots = [
    document.getElementById('sampleDot1'),
    document.getElementById('sampleDot2'),
    document.getElementById('sampleDot3'),
];
const headGuide = document.getElementById('headGuide');
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

function updateStatus(message, isError = false) {
    statusText.textContent = message;
    statusText.className = isError
        ? 'mt-4 text-sm text-red-500 text-center md:text-left'
        : 'mt-4 text-sm text-gray-500 text-center md:text-left';
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

function getFrameQuality() {
    if (!video.videoWidth || !video.videoHeight) {
        return { brightness: 0, sharpness: 0 };
    }

    canvas.width = 160;
    canvas.height = 120;
    const context = canvas.getContext('2d', { willReadFrequently: true });
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    const { data, width, height } = context.getImageData(0, 0, canvas.width, canvas.height);

    let brightnessTotal = 0;
    const grayscale = new Float32Array(width * height);

    for (let i = 0, pixelIndex = 0; i < data.length; i += 4, pixelIndex++) {
        const gray = 0.299 * data[i] + 0.587 * data[i + 1] + 0.114 * data[i + 2];
        grayscale[pixelIndex] = gray;
        brightnessTotal += gray;
    }

    let sharpnessTotal = 0;
    for (let y = 1; y < height - 1; y++) {
        for (let x = 1; x < width - 1; x++) {
            const index = y * width + x;
            const laplacian =
                4 * grayscale[index] -
                grayscale[index - 1] -
                grayscale[index + 1] -
                grayscale[index - width] -
                grayscale[index + width];
            sharpnessTotal += Math.abs(laplacian);
        }
    }

    return {
        brightness: brightnessTotal / grayscale.length,
        sharpness: sharpnessTotal / ((width - 2) * (height - 2)),
    };
}

function isFrameQualityGood(quality) {
    return quality.brightness >= 55 && quality.brightness <= 210 && quality.sharpness >= 18;
}

function stopEnrollmentTracking() {
    trackingActive = false;
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
        descriptors.length = 0;
        sampleQualities.length = 0;
        lastCaptureAt = 0;
        isSaving = false;
        updateSampleCount();

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
        updateStatus('Kamera aktif. Ikuti arah gerakan kepala sampai proses selesai.');
        startEnrollmentTracking();
    } catch (error) {
        updateStatus(error.message || 'Kamera atau model wajah gagal diinisialisasi.', true);
    }
}

function startEnrollmentTracking() {
    stopEnrollmentTracking();
    trackingActive = true;

    const detectFrame = async () => {
        if (!trackingActive) {
            return;
        }

        enrollmentInterval = window.requestAnimationFrame(detectFrame);

        if (!video.srcObject || descriptors.length >= REQUIRED_SAMPLES || isSaving || processingDetection) {
            return;
        }

        const now = Date.now();
        if (now - lastDetectionAt < 180) {
            return;
        }

        lastDetectionAt = now;
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
            const quality = getFrameQuality();

            if (currentStep !== expectedStep) {
                updateStatus(`Ikuti instruksi: ${guideInstruction.textContent}`, false);
                return;
            }

            if (!isFrameQualityGood(quality)) {
                updateStatus('Wajah belum cukup jelas. Perbaiki cahaya atau posisikan wajah lebih stabil.', true);
                return;
            }

            if (now - lastCaptureAt < 900) {
                return;
            }

            lastCaptureAt = now;
            descriptors.push(Array.from(detection.descriptor));
            sampleQualities.push(quality);
            updateSampleCount();

            if (descriptors.length === REQUIRED_SAMPLES) {
                updateStatus('Sampel lengkap. Menyimpan data wajah...');
                guideInstruction.textContent = 'Sampel lengkap. Menyimpan data wajah.';
                stopEnrollmentTracking();
                await saveEmbedding();
            } else {
                updateStatus('Pose terdeteksi. Lanjutkan ke gerakan berikutnya.');
            }
        } finally {
            processingDetection = false;
        }
    };

    detectFrame();
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
        const response = await fetch('{{ route('face.enroll.store') }}', {
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
        updateStatus(error.message || 'Terjadi kesalahan saat menyimpan data wajah.', true);
    }
}

startCameraButton.addEventListener('click', startCamera);
updateSampleCount();
window.addEventListener('load', () => {
    loadModels().catch(() => {
        // error detail tetap ditampilkan saat user menekan tombol mulai
    });
});
window.addEventListener('beforeunload', stopCameraStream);
</script>
@endsection
