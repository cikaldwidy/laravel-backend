@extends('layouts.app')

@section('title', 'E-Presensi')

@section('content')
<style>
    @media (min-width: 768px) {
        .user-attendance-main {
            display: grid;
            grid-template-columns: minmax(0, 1.15fr) 22rem;
            align-items: start;
        }
    }
</style>
@php
    $jadwalMasuk = isset($scheduledShift) && $scheduledShift?->jam_masuk
        ? $scheduledShift->jam_masuk->format('H:i')
        : '--:--';
    $jadwalPulang = isset($scheduledShift) && $scheduledShift?->jam_pulang
        ? $scheduledShift->jam_pulang->format('H:i')
        : '--:--';
    $jamMasuk = $presensi?->jam_masuk?->format('H:i') ?? $jadwalMasuk;
    $jamPulang = $presensi?->jam_keluar?->format('H:i') ?? $jadwalPulang;
    $sudahMasuk = (bool) $presensi?->jam_masuk;
    $sudahPulang = (bool) $presensi?->jam_keluar;
    $jenisAbsen = !$presensi ? 'Masuk' : (!$presensi->jam_keluar ? 'Pulang' : 'Selesai');
    $fotoAktif = $presensi?->foto_keluar ?? $presensi?->foto_masuk ?? $presensi?->foto ?? null;
    $hasScheduledShift = isset($scheduledShift) && $scheduledShift;
    $isShiftOff = $hasScheduledShift && $scheduledShift->status === 'libur';
    $shiftLabel = $hasScheduledShift ? $scheduledShift->nama_shift : 'Belum Dijadwalkan';
@endphp

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

<div class="user-page">
    <div class="user-phone">
        <header class="h-14 bg-emerald-800 text-white flex items-center px-4 shadow">
            <a href="{{ route('dashboard') }}" class="w-8 h-8 flex items-center justify-center rounded-full hover:bg-white/10">
                <i class="fa-solid fa-chevron-left"></i>
            </a>
            <h1 class="flex-1 text-center text-sm font-bold tracking-wide">E-Presensi</h1>
            <div class="w-8"></div>
        </header>

        <main class="user-attendance-main px-4 pt-5 gap-4 space-y-4 md:space-y-0">
            <form id="attendanceForm" method="POST" action="{{ route('absen.store') }}" class="hidden">
                @csrf
                <input type="hidden" name="blink_verified" id="blinkVerified" value="false">
            </form>

            <section class="bg-slate-950 rounded-2xl overflow-hidden shadow-xl">
                <div class="relative aspect-[4/3] bg-slate-900">
                    @if($sudahPulang && $fotoAktif)
                        <img src="{{ asset('storage/' . $fotoAktif) }}" alt="Foto presensi" class="w-full h-full object-cover">
                    @elseif($sudahPulang && !$fotoAktif)
                        <div class="w-full h-full flex flex-col items-center justify-center text-white/70 bg-gradient-to-br from-slate-800 to-slate-950">
                            <i class="fa-solid fa-camera text-4xl"></i>
                            <p class="text-xs mt-3">Foto presensi akan tampil di sini</p>
                        </div>
                    @else
                        <video id="video" autoplay muted playsinline class="w-full h-full object-cover"></video>
                        <div id="cameraOverlay" class="absolute inset-0 flex items-center justify-center bg-black/35 text-white text-xs font-semibold">
                            Tekan Masuk/Verifikasi untuk menyalakan kamera
                        </div>
                    @endif
                    <div class="absolute inset-4 rounded-2xl border-2 border-dashed border-emerald-300/80 pointer-events-none"></div>
                    <div class="absolute top-3 left-3 bg-white/95 text-slate-800 text-xs font-semibold px-3 py-1 rounded-md shadow">
                        {{ now()->translatedFormat('d F Y') }}
                    </div>
                    <div id="clock" class="absolute top-3 right-3 bg-white/95 text-slate-800 text-xs font-bold px-3 py-1 rounded-md shadow">
                        --:--:--
                    </div>
                    <div class="absolute bottom-3 left-3 right-3 bg-black/70 text-white text-xs px-3 py-2 rounded-xl">
                        <i class="fa-solid fa-camera mr-1"></i>
                        Kamera verifikasi wajah
                    </div>
                </div>

                <div class="p-3">
                    <div class="relative rounded-xl overflow-hidden border border-white/10">
                        <div
                            id="attendanceMap"
                            class="h-28 bg-slate-700"
                            data-office-lat="{{ (float) $officeLatitude }}"
                            data-office-lng="{{ (float) $officeLongitude }}"
                            data-office-radius="{{ (int) $officeRadius }}"
                        ></div>
                        <div class="absolute left-3 bottom-3 bg-black/75 text-white text-xs px-3 py-2 rounded-lg max-w-[88%]">
                            <i class="fa-solid fa-location-dot mr-1"></i>
                            <span id="gpsStatus">GPS belum diambil</span>
                        </div>
                    </div>
                </div>
            </section>

            @if(!isset($scheduledShift) || !$scheduledShift)
                <section class="bg-amber-50 border border-amber-200 text-amber-900 rounded-2xl p-4 text-sm shadow-sm">
                    Shift kamu belum diatur oleh admin. Hubungi admin untuk assign jadwal shift terlebih dulu.
                </section>
            @elseif($isShiftOff)
                <section class="bg-slate-50 border border-slate-200 text-slate-700 rounded-2xl p-4 text-sm shadow-sm">
                    Hari ini kamu dijadwalkan libur, jadi absensi tidak dibuka.
                </section>
            @elseif(isset($approvedLeave) && $approvedLeave)
                <section class="bg-sky-50 border border-sky-200 text-sky-900 rounded-2xl p-4 text-sm shadow-sm">
                    Anda memiliki izin yang telah disetujui hari ini: {{ ucfirst($approvedLeave->jenis_izin) }}. Absensi dilewati otomatis.
                </section>
            @elseif(empty($canAttend))
                <section class="bg-sky-50 border border-sky-200 text-sky-900 rounded-2xl p-4 text-sm shadow-sm">
                    Shift kamu sudah dijadwalkan ({{ $shiftLabel }} {{ $jadwalMasuk }} - {{ $jadwalPulang }}), tapi belum masuk jam absensi.
                </section>
            @endif

            <section class="grid grid-cols-3 gap-2">
                <div class="bg-emerald-700 text-white rounded-xl p-3 text-center shadow">
                    <i class="fa-solid fa-user-clock text-sm"></i>
                    <p class="text-[11px] mt-1 opacity-90">Shift</p>
                    <p class="text-xs font-bold">{{ $shiftLabel }}</p>
                </div>
                <div class="bg-emerald-700 text-white rounded-xl p-3 text-center shadow">
                    <i class="fa-solid fa-right-to-bracket text-sm"></i>
                    <p class="text-[11px] mt-1 opacity-90">Jam Masuk</p>
                    <p class="text-xs font-bold">{{ $jamMasuk }}</p>
                </div>
                <div class="bg-emerald-700 text-white rounded-xl p-3 text-center shadow">
                    <i class="fa-solid fa-right-from-bracket text-sm"></i>
                    <p class="text-[11px] mt-1 opacity-90">Jam Pulang</p>
                    <p class="text-xs font-bold">{{ $jamPulang }}</p>
                </div>
            </section>

            <section class="bg-white/80 backdrop-blur rounded-2xl border border-white/70 shadow-sm p-3">
                <div class="relative h-14 rounded-full bg-slate-100 overflow-hidden">
                    <div class="absolute inset-0 grid grid-cols-2">
                        <div class="bg-emerald-600/15"></div>
                        <div class="bg-orange-500/15"></div>
                    </div>

                    <div class="relative z-10 h-full grid grid-cols-2 gap-2 p-2">
                        <button
                            id="startVerification"
                            type="button"
                            class="h-full rounded-full flex items-center justify-center gap-2 font-bold text-sm shadow-sm transition
                                {{ ($sudahPulang || empty($canAttend) || (isset($approvedLeave) && $approvedLeave)) ? 'bg-slate-200 text-slate-400' : (!$sudahMasuk ? 'bg-emerald-700 text-white' : 'bg-white text-emerald-800 border border-emerald-100') }}"
                            @if($sudahPulang || empty($canAttend) || (isset($approvedLeave) && $approvedLeave)) disabled @endif
                        >
                            <i class="fa-solid fa-fingerprint"></i>
                            {{ ($sudahPulang || empty($canAttend) || (isset($approvedLeave) && $approvedLeave)) ? 'Selesai' : ($jenisAbsen === 'Pulang' ? 'Verifikasi' : 'Masuk') }}
                        </button>

                        <button
                            id="submitAttendance"
                            type="button"
                            class="h-full rounded-full flex items-center justify-center gap-2 font-bold text-sm shadow-sm transition
                                bg-orange-600 text-white disabled:bg-orange-100 disabled:text-orange-400"
                            disabled
                        >
                            <i class="fa-solid fa-paper-plane"></i>
                            {{ $jenisAbsen === 'Pulang' ? 'Pulang' : 'Kirim' }}
                        </button>
                    </div>

                    <div class="pointer-events-none absolute top-1/2 -translate-y-1/2 left-1/2 -translate-x-1/2 w-12 h-12 rounded-full bg-white shadow border border-slate-200 flex items-center justify-center">
                        <span class="w-9 h-9 rounded-full bg-emerald-700/10 flex items-center justify-center">
                            <i class="fa-solid fa-circle text-amber-400 text-[10px]"></i>
                        </span>
                    </div>
                </div>
            </section>

            <section class="bg-white/85 backdrop-blur rounded-2xl border border-white/70 shadow-sm p-4 space-y-3">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-xs text-slate-500">Status Hari Ini</p>
                        <p class="font-bold text-slate-800">{{ auth()->user()->name }}</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ $sudahPulang ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                        {{ $jenisAbsen }}
                    </span>
                </div>
                <div class="grid grid-cols-2 gap-3 text-sm text-slate-600">
                    <div>
                        <p class="text-xs text-slate-400">Radius kantor</p>
                        <p class="font-semibold text-slate-800">{{ $officeRadius }} meter</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400">Threshold wajah</p>
                        <p class="font-semibold text-slate-800">{{ $faceThreshold }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400">Sampel</p>
                        <p id="sampleStatus" class="font-semibold text-slate-800">Belum dimulai</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400">Wajah</p>
                        <p id="faceStatus" class="font-semibold text-slate-800">Menunggu scan</p>
                    </div>
                    <div>
                        <p class="text-xs text-slate-400">Kedipan</p>
                        <p id="blinkStatus" class="font-semibold text-slate-800">Belum terverifikasi</p>
                    </div>
                </div>
                <p id="status" class="text-sm text-slate-500">
                    {{ $sudahPulang ? 'Absensi hari ini sudah selesai.' : 'Klik Masuk/Verifikasi untuk menyalakan kamera dan GPS.' }}
                </p>
            </section>

            <section class="bg-white/85 backdrop-blur rounded-2xl border border-white/70 shadow-sm p-4">
                <h2 class="font-bold text-slate-800">Liveness Kedipan</h2>
                <p class="mt-2 text-sm text-slate-600">Hadapkan wajah ke kamera dan kedipkan mata satu kali. Sistem akan mengirim absensi otomatis setelah wajah jelas dan kedipan terverifikasi.</p>
            </section>
        </main>

        <nav class="user-bottom-nav">
            <div class="user-bottom-nav-inner">
                <a href="{{ route('dashboard') }}" class="text-gray-500 text-center text-xs">
                    <i class="fa-solid fa-house text-lg"></i>
                    <p>Home</p>
                </a>
                <a href="{{ route('history.index') }}" class="text-gray-500 text-center text-xs">
                    <i class="fa-solid fa-file-lines text-lg"></i>
                    <p>Histori</p>
                </a>
                <a href="{{ route('absen.page') }}" class="w-14 h-14 -mt-8 bg-emerald-700 text-white rounded-full flex items-center justify-center border-4 border-white shadow-lg">
                    <i class="fa-solid fa-fingerprint text-xl"></i>
                </a>
                <a href="{{ route('leave_requests.index') }}" class="text-gray-500 text-center text-xs">
                    <i class="fa-solid fa-calendar-days text-lg"></i>
                    <p>Izin</p>
                </a>
                <a href="{{ route('announcements.index') }}" class="text-gray-500 text-center text-xs">
                    <i class="fa-solid fa-circle-info text-lg"></i>
                    <p>Info</p>
                </a>
            </div>
        </nav>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
const modelBaseUrl = '/face-api/models';
const submitUrl = "{{ route('absen.store', [], false) }}";
const dashboardUrl = "{{ route('dashboard', [], false) }}";
const attendanceForm = document.getElementById('attendanceForm');
const csrfToken = attendanceForm?.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}';
const blinkVerifiedInput = document.getElementById('blinkVerified');
const video = document.getElementById('video');
const startVerificationButton = document.getElementById('startVerification');
const submitAttendanceButton = document.getElementById('submitAttendance');
const statusText = document.getElementById('status');
const gpsStatus = document.getElementById('gpsStatus');
const sampleStatus = document.getElementById('sampleStatus');
const faceStatus = document.getElementById('faceStatus');
const blinkStatus = document.getElementById('blinkStatus');
const attendanceMapElement = document.getElementById('attendanceMap');
const officeLatitude = Number(attendanceMapElement?.dataset.officeLat ?? 0);
const officeLongitude = Number(attendanceMapElement?.dataset.officeLng ?? 0);
const officeRadius = Number(attendanceMapElement?.dataset.officeRadius ?? 0);

function updateClock() {
    const now = new Date();
    const clock = document.getElementById('clock');
    if (clock) clock.innerText = now.toLocaleTimeString('id-ID');
}
setInterval(updateClock, 1000);
updateClock();

let attendanceMap = null;
let userMarker = null;
let officeCircle = null;

function initializeAttendanceMap() {
    if (!window.L) return;

    attendanceMap = L.map('attendanceMap', {
        zoomControl: false,
        dragging: false,
        scrollWheelZoom: false,
        doubleClickZoom: false,
        boxZoom: false,
        keyboard: false,
    }).setView([officeLatitude, officeLongitude], 16);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap',
    }).addTo(attendanceMap);

    officeCircle = L.circle([officeLatitude, officeLongitude], {
        radius: officeRadius,
        color: '#047857',
        fillColor: '#10b981',
        fillOpacity: 0.15,
        weight: 2,
    }).addTo(attendanceMap);

    userMarker = L.marker([officeLatitude, officeLongitude]).addTo(attendanceMap);
}

function updateAttendanceMap(latitude, longitude) {
    if (!attendanceMap || !userMarker) return;

    const latLng = [latitude, longitude];
    userMarker.setLatLng(latLng);
    attendanceMap.setView(latLng, 17);
}

initializeAttendanceMap();

let stream;
let modelsLoaded = false;
let geolocation = null;
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
let blinkVerified = false;
let eyesWereOpen = false;
let lastEar = null;
let maxOpenEar = 0;
const REQUIRED_SAMPLES = 3;

const MIN_BRIGHTNESS = 38;
const MAX_BRIGHTNESS = 210;
const MIN_SHARPNESS = 10;
const BLINK_OPEN_EAR = 0.22;
const BLINK_CLOSED_EAR = 0.19;
const BLINK_DROP_RATIO = 0.72;

// ✅ LEBIH CEPAT: inputSize 160 (dari 224), deteksi ~40% lebih cepat
const detectorOptions = new faceapi.TinyFaceDetectorOptions({
    inputSize: 160,
    scoreThreshold: 0.45,
});

// ✅ Preload model saat halaman dimuat, bukan saat tombol diklik
let modelsPromise = null;
function preloadModels() {
    if (modelsPromise) return modelsPromise;
    modelsPromise = Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri(modelBaseUrl),
        faceapi.nets.faceLandmark68TinyNet.loadFromUri(modelBaseUrl),
        faceapi.nets.faceRecognitionNet.loadFromUri(modelBaseUrl),
    ]).then(() => { modelsLoaded = true; });
    return modelsPromise;
}

// Mulai preload segera saat halaman terbuka
preloadModels().catch(() => {});

function updateStatus(message, isError = false) {
    statusText.textContent = message;
    statusText.className = isError ? 'mt-4 text-sm text-red-600' : 'mt-4 text-sm text-slate-600';
}

function setBlinkVerified(value) {
    blinkVerified = value;
    blinkVerifiedInput.value = value ? 'true' : 'false';
    blinkStatus.textContent = value ? 'Terverifikasi' : 'Belum terverifikasi';
    blinkStatus.className = value
        ? 'font-semibold text-emerald-700'
        : 'font-semibold text-gray-800';
}

function humanizeStep(step) {
    const labels = {
        center: 'Arahkan wajah lurus ke tengah',
    };
    return labels[step] || step;
}

function renderChallenge() {
    sampleStatus.textContent = `${descriptorSamples.length}/${REQUIRED_SAMPLES}`;
    return;
    [].forEach((step, index) => {
        const li = document.createElement('li');
        const isDone = false;
        li.textContent = `${index + 1}. ${humanizeStep(step)}${isDone ? ' – selesai' : ''}`;
        li.className = isDone ? 'text-emerald-600 font-semibold' : 'text-slate-600';
    });
}

async function loadModels() {
    if (modelsLoaded) return;
    updateStatus('Memuat model deteksi wajah...');
    await preloadModels();
}

async function requestChallenge() {
    sampleStatus.textContent = '0/3';
}

function distanceBetween(pointA, pointB) {
    return Math.hypot(pointA.x - pointB.x, pointA.y - pointB.y);
}

function calculateEyeAspectRatio(eye) {
    const verticalA = distanceBetween(eye[1], eye[5]);
    const verticalB = distanceBetween(eye[2], eye[4]);
    const horizontal = distanceBetween(eye[0], eye[3]);

    if (horizontal === 0) return 0;

    return (verticalA + verticalB) / (2 * horizontal);
}

function detectBlink(landmarks) {
    const leftEar = calculateEyeAspectRatio(landmarks.getLeftEye());
    const rightEar = calculateEyeAspectRatio(landmarks.getRightEye());
    const ear = (leftEar + rightEar) / 2;
    lastEar = ear;

    maxOpenEar = Math.max(maxOpenEar, ear);

    if (ear > BLINK_OPEN_EAR) {
        eyesWereOpen = true;
        return false;
    }

    return eyesWereOpen && (ear < BLINK_CLOSED_EAR || (maxOpenEar > 0 && ear <= maxOpenEar * BLINK_DROP_RATIO));
}

function maybeCompleteVerification() {
    if (blinkVerified && descriptorSamples.length >= REQUIRED_SAMPLES) {
        verificationReady = true;
        submitAttendanceButton.disabled = false;
        updateStatus('Wajah jelas dan kedipan terverifikasi. Mengirim absensi...');
        stopTracking();
        submitAttendance();
    }
}

function captureSnapshot() {
    const canvas = document.createElement('canvas');
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext('2d').drawImage(video, 0, 0, canvas.width, canvas.height);
    return canvas.toDataURL('image/jpeg', 0.85);
}

function getFrameQuality(faceBox = null) {
    const canvas = document.createElement('canvas');
    canvas.width = 160;
    canvas.height = 120;
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
    return quality.brightness >= MIN_BRIGHTNESS
        && quality.brightness <= MAX_BRIGHTNESS
        && quality.sharpness >= 8;
}

function averageDescriptors(samples) {
    const averaged = new Array(samples[0].length).fill(0);
    samples.forEach(sample => sample.forEach((v, i) => { averaged[i] += v; }));
    return averaged.map(v => v / samples.length);
}

function summarizeQuality(samples) {
    return {
        brightness: samples.reduce((t, s) => t + s.brightness, 0) / samples.length,
        sharpness: samples.reduce((t, s) => t + s.sharpness, 0) / samples.length,
    };
}

function stopTracking() {
    if (trackingFrame) {
        cancelAnimationFrame(trackingFrame);
        trackingFrame = null;
    }
}

function stopStream() {
    if (stream) {
        stream.getTracks().forEach(t => t.stop());
        stream = null;
    }
}

function getCameraErrorMessage(error) {
    const name = error?.name || '';
    if (!window.isSecureContext) return 'Kamera hanya bisa dipakai lewat HTTPS (atau localhost).';
    if (name === 'NotAllowedError' || name === 'PermissionDeniedError') return 'Izin kamera ditolak. Aktifkan izin kamera di browser.';
    if (name === 'NotFoundError' || name === 'DevicesNotFoundError') return 'Kamera tidak ditemukan.';
    if (name === 'NotReadableError' || name === 'TrackStartError') return 'Kamera sedang dipakai aplikasi lain.';
    return error?.message || 'Kamera gagal dinyalakan.';
}

async function startCamera() {
    if (!navigator.mediaDevices?.getUserMedia) {
        throw new Error('Browser tidak mendukung akses kamera.');
    }

    if (!window.isSecureContext) {
        throw new Error('Kamera hanya bisa dipakai lewat HTTPS (atau localhost).');
    }

    updateStatus('Meminta izin kamera...');

    // Minta kamera duluan supaya tetap dianggap "user gesture" di iOS Safari.
    stream = await navigator.mediaDevices.getUserMedia({
        video: {
            facingMode: 'user',
            width: { ideal: 480 },
            height: { ideal: 360 },
            frameRate: { ideal: 24, max: 30 }
        },
        audio: false
    }).catch((error) => {
        throw new Error(getCameraErrorMessage(error));
    });

    video.setAttribute('playsinline', '');
    video.muted = true;
    video.autoplay = true;
    video.srcObject = stream;

    await video.play().catch(() => {});

    // Tunggu video siap sebelum mulai deteksi
    await new Promise(resolve => {
        if (video.readyState >= 2) return resolve();
        video.addEventListener('loadeddata', resolve, { once: true });
    });

    const overlay = document.getElementById('cameraOverlay');
    if (overlay) overlay.classList.add('hidden');
}

function trackChallenge() {
    stopTracking();

    // ✅ Interval 150ms (dari 220ms) — lebih responsif
    const DETECTION_INTERVAL = 150;

    const runDetection = async () => {
        trackingFrame = requestAnimationFrame(runDetection);

        if (processingDetection || isSubmitting) return;

        const now = performance.now();
        if (now - lastDetectionAt < DETECTION_INTERVAL) return;

        processingDetection = true;
        lastDetectionAt = now;

        try {
            const detection = await faceapi
                .detectSingleFace(video, detectorOptions)
                .withFaceLandmarks(true)
                .withFaceDescriptor();

            if (!detection) {
                faceStatus.textContent = 'Wajah tidak terdeteksi';
                updateStatus('Dekatkan wajah ke kamera.', true);
                return;
            }

            const quality = getFrameQuality(detection.detection.box);
            const blinkDetected = detectBlink(detection.landmarks);

            faceStatus.textContent = 'Wajah terdeteksi';

            if (!isFrameQualityGood(quality)) {
                updateStatus(`Pencahayaan kurang. Cahaya: ${Math.round(quality.brightness)}, ketajaman: ${Math.round(quality.sharpness)}.`, true);
                return;
            }

            if (blinkDetected && !blinkVerified) {
                setBlinkVerified(true);
                updateStatus('Kedipan berhasil diverifikasi.');
            } else if (!blinkVerified && lastEar !== null) {
                blinkStatus.textContent = `Kedipkan mata (${lastEar.toFixed(2)})`;
            }

            if (Date.now() - lastCaptureAt >= 500 && descriptorSamples.length < REQUIRED_SAMPLES) {
                lastCaptureAt = Date.now();
                descriptorSamples.push(Array.from(detection.descriptor));
                qualitySamples.push(quality);
                latestDescriptor = averageDescriptors(descriptorSamples);
                latestSnapshot = captureSnapshot();
                latestQualityMetrics = summarizeQuality(qualitySamples);
                renderChallenge();
                updateStatus(blinkVerified ? 'Sampel wajah cukup. Menyiapkan absensi...' : 'Wajah jelas. Kedipkan mata satu kali.');
            }

            if (blinkVerified && descriptorSamples.length >= REQUIRED_SAMPLES) {
                verificationReady = true;
                submitAttendanceButton.disabled = false;
                updateStatus('Wajah jelas dan kedipan terverifikasi. Mengirim absensi...');
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
    if (!navigator.geolocation) throw new Error('Browser tidak mendukung geolokasi.');
    geolocation = await new Promise((resolve, reject) => {
        navigator.geolocation.getCurrentPosition(
            pos => resolve(pos.coords),
            () => reject(new Error('Izin lokasi ditolak atau GPS tidak tersedia.')),
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
        );
    });
    gpsStatus.textContent = `${geolocation.latitude.toFixed(6)}, ${geolocation.longitude.toFixed(6)}`;
    updateAttendanceMap(geolocation.latitude, geolocation.longitude);
}

async function startVerification() {
    try {
        updateStatus('Menyiapkan kamera, lokasi, dan verifikasi kedipan...');
        isSubmitting = false;
        submitAttendanceButton.disabled = true;
        verificationReady = false;
        latestDescriptor = null;
        latestSnapshot = null;
        latestQualityMetrics = null;
        descriptorSamples = [];
        qualitySamples = [];
        setBlinkVerified(false);
        eyesWereOpen = false;
        lastEar = null;
        maxOpenEar = 0;
        lastCaptureAt = 0;
        sampleStatus.textContent = '0/3';
        faceStatus.textContent = 'Menunggu scan';
        renderChallenge();
        stopTracking();
        stopStream();

        await startCamera();
        updateStatus('Mempersiapkan sistem...');
        await Promise.all([
            loadModels(),
            requestGeolocation(),
        ]);

        // startCamera() sudah menyalakan kamera dan menunggu video siap.

        trackChallenge();
        updateStatus('Hadapkan wajah ke kamera dan kedipkan mata.');
    } catch (error) {
        const overlay = document.getElementById('cameraOverlay');
        if (overlay) {
            overlay.textContent = error.message || 'Kamera gagal dinyalakan.';
            overlay.classList.remove('hidden');
        }
        updateStatus(error.message || 'Verifikasi gagal dimulai.', true);
    }
}

async function submitAttendance() {
    if (isSubmitting) return;
    if (!verificationReady || !latestDescriptor || !latestSnapshot || !geolocation) {
        updateStatus('Verifikasi belum lengkap.', true);
        return;
    }

    if (blinkVerifiedInput.value !== 'true') {
        updateStatus('Verifikasi kedipan belum berhasil.', true);
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
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify({
                image: latestSnapshot,
                embedding: latestDescriptor,
                quality_metrics: latestQualityMetrics,
                lat: geolocation.latitude,
                lng: geolocation.longitude,
                blink_verified: blinkVerifiedInput.value,
            }),
        });

        const result = await response.json();

        if (!response.ok) {
            const faceDistanceText = typeof result.face_distance === 'number'
                ? ` (jarak wajah: ${result.face_distance})`
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
