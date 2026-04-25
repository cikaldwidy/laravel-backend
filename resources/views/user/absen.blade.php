@extends('layouts.app')

@section('content')
<div class="w-full min-h-screen bg-slate-100 py-8 px-4">
    <div class="max-w-6xl mx-auto grid gap-6 lg:grid-cols-[1.15fr_0.85fr] items-start">
        <div class="bg-white rounded-3xl shadow-xl overflow-hidden">
            <div class="bg-slate-900 px-6 py-5 text-white">
                <p class="text-sm uppercase tracking-[0.3em] text-emerald-300">Verifikasi Absensi</p>
                <h1 class="text-2xl font-bold mt-2">Scan wajah dan validasi lokasi sebelum absen</h1>
                <p class="text-sm text-slate-300 mt-2">
                    Sistem akan memeriksa challenge gerakan wajah, kecocokan template, dan posisi GPS Anda.
                </p>
            </div>

            <div class="p-6">
                <div class="relative rounded-3xl overflow-hidden bg-slate-950 aspect-video">
                    <video id="video" autoplay muted playsinline class="w-full h-full object-cover"></video>
                    <div class="absolute inset-0 border-[3px] border-dashed border-emerald-400/70 m-6 rounded-[2rem] pointer-events-none"></div>
                    <div class="absolute top-4 left-4 bg-black/60 text-white text-xs px-3 py-2 rounded-full">
                        Kamera aktif
                    </div>
                </div>

                <div class="mt-6 flex flex-wrap gap-3">
                    <button id="startVerification" class="bg-slate-900 text-white px-5 py-3 rounded-2xl font-semibold">
                        Mulai Verifikasi
                    </button>
                    <button id="submitAttendance" class="bg-emerald-500 text-white px-5 py-3 rounded-2xl font-semibold" disabled>
                        Kirim Absensi
                    </button>
                </div>

                <p id="status" class="mt-4 text-sm text-slate-600">Klik mulai verifikasi untuk menyalakan kamera dan GPS.</p>
            </div>
        </div>

        <div class="space-y-6">
            <div class="bg-white rounded-3xl shadow-xl p-6">
                <h2 class="text-lg font-bold text-slate-800">Status Hari Ini</h2>
                <div class="mt-4 space-y-3 text-sm text-slate-600">
                    <p>Nama: <span class="font-semibold text-slate-800">{{ auth()->user()->name }}</span></p>
                    <p>Jenis absensi berikutnya:
                        <span class="font-semibold text-slate-800">
                            @if(!$presensi)
                                Check-in
                            @elseif(!$presensi->jam_keluar)
                                Check-out
                            @else
                                Selesai
                            @endif
                        </span>
                    </p>
                    <p>Threshold wajah: <span class="font-semibold text-slate-800">{{ $faceThreshold }}</span></p>
                    <p>Radius kantor: <span class="font-semibold text-slate-800">{{ $officeRadius }} meter</span></p>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-xl p-6">
                <h2 class="text-lg font-bold text-slate-800">Challenge Liveness</h2>
                <ol id="challengeList" class="mt-4 space-y-3 text-sm text-slate-600 list-decimal pl-5"></ol>
            </div>

            <div class="bg-white rounded-3xl shadow-xl p-6">
                <h2 class="text-lg font-bold text-slate-800">Hasil Validasi</h2>
                <div class="mt-4 space-y-3 text-sm text-slate-600">
                    <p>GPS: <span id="gpsStatus" class="font-semibold text-slate-800">Belum diambil</span></p>
                    <p>Challenge: <span id="challengeStatus" class="font-semibold text-slate-800">Belum dimulai</span></p>
                    <p>Wajah: <span id="faceStatus" class="font-semibold text-slate-800">Menunggu scan</span></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
const modelBaseUrl = '/face-api/models';
const challengeUrl = '{{ route('absen.challenge', [], false) }}';
const submitUrl = '{{ route('absen.store', [], false) }}';
const dashboardUrl = '{{ route('dashboard', [], false) }}';
const video = document.getElementById('video');
const startVerificationButton = document.getElementById('startVerification');
const submitAttendanceButton = document.getElementById('submitAttendance');
const statusText = document.getElementById('status');
const challengeList = document.getElementById('challengeList');
const gpsStatus = document.getElementById('gpsStatus');
const challengeStatus = document.getElementById('challengeStatus');
const faceStatus = document.getElementById('faceStatus');

let stream;
let modelsLoaded = false;
let geolocation = null;
let challenge = null;
let completedSteps = [];
let verificationReady = false;
let latestDescriptor = null;
let latestSnapshot = null;
let isSubmitting = false;
let latestQualityMetrics = null;
let descriptorSamples = [];
let qualitySamples = [];
let trackingFrame = null;
let processingDetection = false;
let lastDetectionAt = 0;
const MIN_BRIGHTNESS = 38;
const MAX_BRIGHTNESS = 210;
const MIN_SHARPNESS = 10;
const detectorOptions = new faceapi.TinyFaceDetectorOptions({
    inputSize: 224,
    scoreThreshold: 0.5,
});

function updateStatus(message, isError = false) {
    statusText.textContent = message;
    statusText.className = isError ? 'mt-4 text-sm text-red-600' : 'mt-4 text-sm text-slate-600';
}

function humanizeStep(step) {
    const labels = {
        center: 'Arahkan wajah lurus ke tengah',
        left: 'Putar wajah sedikit ke kiri',
        right: 'Putar wajah sedikit ke kanan',
    };

    return labels[step] || step;
}

function renderChallenge() {
    challengeList.innerHTML = '';

    if (!challenge) {
        return;
    }

    challenge.steps.forEach((step, index) => {
        const li = document.createElement('li');
        const isDone = completedSteps.includes(step);
        li.textContent = `${index + 1}. ${humanizeStep(step)}${isDone ? ' - selesai' : ''}`;
        li.className = isDone ? 'text-emerald-600 font-semibold' : 'text-slate-600';
        challengeList.appendChild(li);
    });
}

async function loadModels() {
    if (modelsLoaded) {
        return;
    }

    updateStatus('Memuat model deteksi wajah...');

    try {
        await Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri(modelBaseUrl),
            faceapi.nets.faceLandmark68TinyNet.loadFromUri(modelBaseUrl),
            faceapi.nets.faceRecognitionNet.loadFromUri(modelBaseUrl),
        ]);
    } catch (error) {
        throw new Error('Model face-api belum tersedia di /face-api/models.');
    }

    modelsLoaded = true;
}

async function requestChallenge() {
    const response = await fetch(challengeUrl, {
        method: 'POST',
        cache: 'no-store',
        credentials: 'same-origin',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
        }
    });

    if (!response.ok) {
        throw new Error('Challenge liveness gagal dibuat.');
    }

    challenge = await response.json();
    completedSteps = [];
    challengeStatus.textContent = 'Challenge aktif';
    renderChallenge();
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

function captureSnapshot() {
    const canvas = document.createElement('canvas');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
    return canvas.toDataURL('image/jpeg', 0.9);
}

function getFrameQuality(faceBox = null) {
    const canvas = document.createElement('canvas');
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

function averageDescriptors(samples) {
    const averaged = new Array(samples[0].length).fill(0);

    samples.forEach((sample) => {
        sample.forEach((value, index) => {
            averaged[index] += value;
        });
    });

    return averaged.map((value) => value / samples.length);
}

function summarizeQuality(samples) {
    return {
        brightness: samples.reduce((total, sample) => total + sample.brightness, 0) / samples.length,
        sharpness: samples.reduce((total, sample) => total + sample.sharpness, 0) / samples.length,
    };
}

function stopTracking() {
    if (trackingFrame) {
        window.cancelAnimationFrame(trackingFrame);
        trackingFrame = null;
    }
}

function stopStream() {
    if (stream) {
        stream.getTracks().forEach((track) => track.stop());
        stream = null;
    }
}

function trackChallenge() {
    stopTracking();

    const runDetection = async () => {
        trackingFrame = window.requestAnimationFrame(runDetection);

        if (!challenge || completedSteps.length === challenge.steps.length) {
            return;
        }

        const now = performance.now();
        if (processingDetection || now - lastDetectionAt < 220) {
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
            faceStatus.textContent = 'Wajah tidak terdeteksi';
            return;
        }

        const currentExpectedStep = challenge.steps[completedSteps.length];
        const currentPose = getHeadPoseStep(detection.landmarks);
        const quality = getFrameQuality(detection.detection.box);

        faceStatus.textContent = `Wajah terdeteksi (${currentPose})`;

        if (!isFrameQualityGood(quality)) {
            faceStatus.textContent = 'Wajah terdeteksi, tapi frame kurang jelas';
            updateStatus(`Perbaiki pencahayaan/stabilitas. Cahaya ${Math.round(quality.brightness)}, ketajaman ${Math.round(quality.sharpness)}.`, true);
            return;
        }

        if (currentPose === currentExpectedStep) {
            completedSteps.push(currentExpectedStep);
            descriptorSamples.push(Array.from(detection.descriptor));
            qualitySamples.push(quality);
            latestDescriptor = averageDescriptors(descriptorSamples);
            latestSnapshot = captureSnapshot();
            latestQualityMetrics = summarizeQuality(qualitySamples);
            renderChallenge();
            challengeStatus.textContent = `${completedSteps.length}/${challenge.steps.length} langkah selesai`;
            updateStatus(`Langkah "${humanizeStep(currentExpectedStep)}" berhasil.`);
        }

        if (completedSteps.length === challenge.steps.length) {
            verificationReady = true;
            submitAttendanceButton.disabled = false;
            challengeStatus.textContent = 'Challenge selesai';
            updateStatus('Challenge selesai. Absensi sedang dikirim...');
            stopTracking();
            submitAttendance();
        }
        } finally {
            processingDetection = false;
        }
    };

    runDetection();
}

async function requestGeolocation() {
    if (!navigator.geolocation) {
        throw new Error('Browser tidak mendukung geolokasi.');
    }

    geolocation = await new Promise((resolve, reject) => {
        navigator.geolocation.getCurrentPosition(
            (position) => resolve(position.coords),
            () => reject(new Error('Izin lokasi ditolak atau GPS tidak tersedia.')),
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
        );
    });

    gpsStatus.textContent = `${geolocation.latitude.toFixed(6)}, ${geolocation.longitude.toFixed(6)}`;
}

async function startVerification() {
    try {
        updateStatus('Menyiapkan kamera, lokasi, dan challenge...');
        isSubmitting = false;
        submitAttendanceButton.disabled = true;
        verificationReady = false;
        latestDescriptor = null;
        latestSnapshot = null;
        latestQualityMetrics = null;
        descriptorSamples = [];
        qualitySamples = [];
        completedSteps = [];
        challenge = null;
        challengeStatus.textContent = 'Belum dimulai';
        faceStatus.textContent = 'Menunggu scan';
        renderChallenge();
        stopTracking();
        stopStream();

        await loadModels();
        await requestChallenge();
        await requestGeolocation();

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
        trackChallenge();
        updateStatus('Ikuti urutan challenge di panel kanan.');
    } catch (error) {
        updateStatus(error.message || 'Verifikasi gagal dimulai.', true);
    }
}

async function submitAttendance() {
    if (isSubmitting) {
        return;
    }

    if (!verificationReady || !latestDescriptor || !latestSnapshot || !geolocation || !challenge) {
        updateStatus('Verifikasi belum lengkap.', true);
        return;
    }

    isSubmitting = true;
    updateStatus('Mengirim data absensi ke server...');
    submitAttendanceButton.disabled = true;

    try {
        const response = await fetch(submitUrl, {
            credentials: 'same-origin',
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify({
                image: latestSnapshot,
                embedding: latestDescriptor,
                quality_metrics: latestQualityMetrics,
                lat: geolocation.latitude,
                lng: geolocation.longitude,
                challenge_token: challenge.token,
                challenge_steps: completedSteps,
            }),
        });

        const result = await response.json();

        if (!response.ok) {
            const faceDistanceText = typeof result.face_distance === 'number'
                ? ` (jarak wajah ${result.face_distance})`
                : '';
            throw new Error((result.message || 'Absensi gagal diproses.') + faceDistanceText);
        }

        updateStatus(result.message || 'Absensi berhasil dikirim.');
        window.location.href = result.redirect || dashboardUrl;
    } catch (error) {
        isSubmitting = false;
        submitAttendanceButton.disabled = false;
        updateStatus(error.message || 'Absensi gagal dikirim.', true);
    }
}

startVerificationButton.addEventListener('click', startVerification);
submitAttendanceButton.addEventListener('click', submitAttendance);
window.addEventListener('beforeunload', stopStream);
</script>
@endsection
