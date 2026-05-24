@extends('layouts.admin')

@section('title', 'Data Wajah')

@section('content')
<div class="space-y-6">
    <div id="alertBox" class="hidden"></div>
    <div
        id="faceDataConfig"
        class="hidden"
        data-current-unit-id="{{ $selectedUnitId ? (string) $selectedUnitId : '' }}"
        data-store-url="{{ route('admin.face_data.store') }}"
        data-index-url="{{ route('admin.face_data.index') }}"
        data-csrf-token="{{ csrf_token() }}"
    ></div>

    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <div class="flex items-center gap-3">
                <span class="flex h-12 w-12 items-center justify-center rounded-md bg-gradient-to-br from-blue-600 to-sky-500 text-white shadow-lg shadow-blue-500/20">
                    <i class="fa-solid fa-face-smile text-lg"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">Data Wajah</h1>
                    <p class="mt-1 text-sm text-gray-500">Kelola template wajah pegawai untuk presensi biometrik.</p>
                </div>
            </div>
        </div>

        <form method="GET" action="{{ route('admin.face_data.index') }}" class="flex flex-col gap-2 sm:flex-row sm:items-center">
            <select
                id="unitFilter"
                name="unit_id"
                class="h-11 rounded-md border border-blue-100 bg-white px-3 text-sm font-medium text-gray-700 shadow-sm outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                onchange="this.form.submit()"
            >
                <option value="">Semua unit kerja/bagian</option>
                @foreach($units as $unit)
                    <option value="{{ $unit->id }}" @selected((string) $selectedUnitId === (string) $unit->id)>
                        {{ $unit->nama_departemen }}
                    </option>
                @endforeach
            </select>
            @if($selectedUnitId)
                <a href="{{ route('admin.face_data.index') }}" class="inline-flex h-11 items-center justify-center gap-2 rounded-md border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-600 transition hover:bg-gray-50">
                    <i class="fa-solid fa-xmark text-xs"></i>
                    Reset
                </a>
            @endif
        </form>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-md border border-blue-100 bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-gray-500">Sudah Terdaftar</p>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $usersWithFaceData->count() }}</p>
        </div>
        <div class="rounded-md border border-blue-100 bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-gray-500">Belum Terdaftar</p>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $usersWithoutFaceData->count() }}</p>
        </div>
        <div class="rounded-md border border-blue-100 bg-white p-5 shadow-sm">
            <p class="text-sm font-semibold text-gray-500">Template Wajah</p>
            <p class="mt-2 text-3xl font-bold text-gray-900">{{ $faceEmbeddings->count() }}</p>
        </div>
    </div>

    <section class="overflow-hidden rounded-md border border-blue-100 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-blue-50 px-6 py-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 id="formTitle" class="text-lg font-bold text-gray-900">Capture Data Wajah</h2>
                <p id="formSubtitle" class="mt-1 text-sm text-gray-500">Pilih pegawai, aktifkan kamera, verifikasi kedipan, lalu simpan.</p>
            </div>
            <button id="resetMode" type="button" class="hidden inline-flex h-10 items-center justify-center gap-2 rounded-md border border-blue-100 bg-white px-4 text-sm font-semibold text-blue-600 transition hover:bg-blue-50">
                <i class="fa-solid fa-plus"></i>
                Mode Tambah
            </button>
        </div>

        <div class="grid gap-0 xl:grid-cols-[minmax(0,1fr)_360px]">
            <div class="p-6">
                <div class="relative aspect-[4/3] overflow-hidden rounded-md bg-gray-950">
                    <video id="video" autoplay muted playsinline class="w-full h-full object-cover"></video>
                    <div class="pointer-events-none absolute inset-0 bg-black/10"></div>
                    <div id="previewWrap" class="hidden absolute inset-0 bg-black">
                        <img id="previewImage" src="" alt="Preview wajah" class="w-full h-full object-cover">
                    </div>
                    <div class="absolute left-4 top-4 inline-flex items-center gap-2 rounded-md bg-white px-3 py-2 text-xs font-semibold text-gray-700 shadow-sm">
                        <span class="h-2 w-2 rounded-full bg-blue-500"></span>
                        <span id="cameraBadge">Kamera belum aktif</span>
                    </div>
                    <div class="absolute bottom-4 left-4 right-4 rounded-md bg-gray-950/80 px-4 py-3 text-white shadow-sm">
                        <p id="status" class="text-sm">Siapkan kamera untuk capture wajah.</p>
                    </div>
                </div>

                <div class="mt-4 grid gap-3 sm:grid-cols-2">
                    <div class="rounded-md border border-blue-100 bg-blue-50/50 px-4 py-3">
                        <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <i class="fa-solid fa-user-check text-blue-500"></i>
                            Wajah
                        </div>
                        <p id="faceStatus" class="mt-1 text-sm font-semibold text-gray-900">Menunggu kamera</p>
                    </div>
                    <div class="rounded-md border border-blue-100 bg-blue-50/50 px-4 py-3">
                        <div class="flex items-center gap-2 text-xs font-semibold uppercase tracking-wide text-gray-500">
                            <i class="fa-solid fa-eye text-blue-500"></i>
                            Kedipan
                        </div>
                        <p id="blinkStatus" class="mt-1 text-sm font-semibold text-gray-900">Belum terverifikasi</p>
                    </div>
                </div>

                <div class="mt-4 grid gap-3 sm:grid-cols-3">
                    <button id="startCamera" type="button" class="inline-flex h-11 items-center justify-center gap-2 rounded-md bg-gradient-to-r from-blue-600 to-sky-500 px-4 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:from-blue-700 hover:to-sky-600">
                        <i class="fa-solid fa-video"></i>
                        Aktifkan
                    </button>
                    <button id="captureFace" type="button" class="inline-flex h-11 items-center justify-center gap-2 rounded-md bg-blue-600 px-4 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-gray-200 disabled:text-gray-400 disabled:shadow-none" disabled>
                        <i class="fa-solid fa-camera-retro"></i>
                        Capture
                    </button>
                    <button id="saveFace" type="button" class="inline-flex h-11 items-center justify-center gap-2 rounded-md bg-sky-600 px-4 text-sm font-semibold text-white shadow-lg shadow-sky-500/20 transition hover:bg-sky-700 disabled:cursor-not-allowed disabled:bg-gray-200 disabled:text-gray-400 disabled:shadow-none" disabled>
                        <i class="fa-solid fa-floppy-disk"></i>
                        Simpan
                    </button>
                </div>
            </div>

            <aside class="border-t border-blue-50 bg-blue-50/30 p-6 xl:border-l xl:border-t-0">
                <input type="hidden" id="mode" value="store">
                <input type="hidden" id="updateUrl" value="">

                <label for="userId" class="mb-2 block text-sm font-semibold text-gray-700">Pegawai</label>
                <select id="userId" class="h-11 w-full rounded-md border border-blue-100 bg-white px-3 text-sm font-medium text-gray-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    <option value="">Pilih pegawai</option>
                    @foreach($usersWithoutFaceData as $user)
                        <option value="{{ $user->id }}">
                            {{ $user->name }} - {{ $user->email }}{{ $user->employeeDetail?->department?->nama_departemen ? ' - ' . $user->employeeDetail?->department?->nama_departemen : '' }}
                        </option>
                    @endforeach
                </select>

                @if($selectedUnit)
                    <div class="mt-3 rounded-md border border-blue-100 bg-white px-3 py-2 text-sm text-gray-500">
                        Unit Kerja/Bagian aktif:
                        <span class="font-semibold text-gray-900">{{ $selectedUnit->nama_departemen }}</span>
                    </div>
                @endif

                <div id="selectedUserCard" class="hidden mt-4 rounded-md border border-blue-100 bg-white p-4">
                    <div class="flex items-start gap-3">
                        <span class="flex h-9 w-9 items-center justify-center rounded-md bg-blue-600 text-white">
                            <i class="fa-solid fa-rotate"></i>
                        </span>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold uppercase tracking-wide text-blue-600">Update data wajah</p>
                            <p id="selectedUserName" class="mt-1 truncate text-sm font-semibold text-gray-900"></p>
                            <p id="selectedUserEmail" class="truncate text-xs text-gray-500"></p>
                        </div>
                    </div>
                </div>

                <div class="mt-5 rounded-md border border-blue-100 bg-white p-4">
                    <p class="font-semibold text-gray-900">Langkah Capture</p>
                    <div class="mt-4 space-y-0">
                        <div class="relative flex gap-3 pb-5">
                            <span class="absolute left-[15px] top-8 h-full w-px bg-blue-100"></span>
                            <span class="relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-blue-50 text-blue-500">
                                <i class="fa-solid fa-user-check text-xs"></i>
                            </span>
                            <div>
                                <p class="text-xs font-semibold text-blue-500">Step 1</p>
                                <p class="text-sm font-semibold text-gray-700">Pilih pegawai</p>
                                <p class="mt-1 text-sm leading-relaxed text-gray-500">Pilih pegawai yang belum punya data wajah.</p>
                            </div>
                        </div>
                        <div class="relative flex gap-3 pb-5">
                            <span class="absolute left-[15px] top-8 h-full w-px bg-blue-100"></span>
                            <span class="relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-blue-50 text-blue-500">
                                <i class="fa-solid fa-video text-xs"></i>
                            </span>
                            <div>
                                <p class="text-xs font-semibold text-blue-500">Step 2</p>
                                <p class="text-sm font-semibold text-gray-700">Aktifkan kamera</p>
                                <p class="mt-1 text-sm leading-relaxed text-gray-500">Posisikan wajah dengan jelas di area kamera.</p>
                            </div>
                        </div>
                        <div class="relative flex gap-3">
                            <span class="relative z-10 flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-blue-50 text-blue-500">
                                <i class="fa-solid fa-camera text-xs"></i>
                            </span>
                            <div>
                                <p class="text-xs font-semibold text-blue-500">Step 3</p>
                                <p class="text-sm font-semibold text-gray-700">Capture dan simpan</p>
                                <p class="mt-1 text-sm leading-relaxed text-gray-500">Kedipkan mata, capture wajah, lalu simpan data.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>

        <canvas id="captureCanvas" class="hidden"></canvas>
    </section>

    <section class="overflow-hidden rounded-md border border-blue-100 bg-white shadow-sm">
        <div class="flex flex-col gap-2 border-b border-blue-50 px-6 py-5 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Daftar Data Wajah</h2>
                <p class="text-sm text-gray-500">
                    {{ $faceEmbeddings->count() }} template wajah tersimpan{{ $selectedUnit ? ' di ' . $selectedUnit->nama_departemen : '' }}
                </p>
            </div>
            <span class="inline-flex w-fit items-center gap-2 rounded-md bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-600">
                <i class="fa-solid fa-database"></i>
                {{ $faceEmbeddings->count() }} data
            </span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-sm">
                <thead class="bg-blue-50/70 text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3 text-left">Foto</th>
                        <th class="px-6 py-3 text-left">Pegawai</th>
                        <th class="px-6 py-3 text-left">Unit Kerja/Bagian</th>
                        <th class="px-6 py-3 text-left">Update</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-blue-50">
                    @forelse($faceEmbeddings as $faceEmbedding)
                        <tr class="hover:bg-blue-50/40">
                            <td class="px-6 py-4">
                                <div class="h-14 w-14 overflow-hidden rounded-md border border-blue-100 bg-blue-50">
                                    @if($faceEmbedding->photo_path)
                                        <img src="{{ asset('storage/' . $faceEmbedding->photo_path) }}" alt="Foto wajah {{ $faceEmbedding->user?->name }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="flex h-full w-full items-center justify-center text-blue-300">
                                            <i class="fa-regular fa-image text-xl"></i>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="font-semibold text-gray-900">{{ $faceEmbedding->user?->name ?? '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $faceEmbedding->user?->email ?? '-' }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-md bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                                    {{ $faceEmbedding->user?->employeeDetail?->department?->nama_departemen ?? $faceEmbedding->user?->employeeDetail?->departemen ?? '-' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-gray-500">{{ $faceEmbedding->updated_at?->format('d M Y H:i') }}</td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">
                                    <button
                                        type="button"
                                        class="inline-flex items-center justify-center gap-2 rounded-md border border-blue-100 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-600 transition hover:bg-blue-100"
                                        data-edit-face
                                        data-user-id="{{ $faceEmbedding->user_id }}"
                                        data-user-name="{{ $faceEmbedding->user?->name }}"
                                        data-user-email="{{ $faceEmbedding->user?->email }}"
                                        data-update-url="{{ route('admin.face_data.update', $faceEmbedding) }}"
                                    >
                                        <i class="fa-solid fa-camera"></i>
                                        Capture Ulang
                                    </button>
                                    <form method="POST" action="{{ route('admin.face_data.destroy', $faceEmbedding) }}" onsubmit="return confirm('Hapus data wajah user ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="inline-flex items-center justify-center gap-2 rounded-md border border-red-100 bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-100">
                                            <i class="fa-solid fa-trash"></i>
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-md bg-blue-50 text-blue-400">
                                    <i class="fa-regular fa-face-smile text-xl"></i>
                                </div>
                                <p class="mt-3 font-semibold text-gray-800">Belum ada data wajah</p>
                                <p class="text-sm text-gray-500">Data akan muncul setelah template wajah pegawai disimpan.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
const video = document.getElementById('video');
const canvas = document.getElementById('captureCanvas');
const faceDataConfig = document.getElementById('faceDataConfig');
const faceCoverageBar = document.getElementById('faceCoverageBar');
const startCameraButton = document.getElementById('startCamera');
const captureFaceButton = document.getElementById('captureFace');
const saveFaceButton = document.getElementById('saveFace');
const resetModeButton = document.getElementById('resetMode');
const statusText = document.getElementById('status');
const faceStatus = document.getElementById('faceStatus');
const blinkStatus = document.getElementById('blinkStatus');
const cameraBadge = document.getElementById('cameraBadge');
const previewWrap = document.getElementById('previewWrap');
const previewImage = document.getElementById('previewImage');
const alertBox = document.getElementById('alertBox');
const userIdSelect = document.getElementById('userId');
const modeInput = document.getElementById('mode');
const updateUrlInput = document.getElementById('updateUrl');
const selectedUserCard = document.getElementById('selectedUserCard');
const selectedUserName = document.getElementById('selectedUserName');
const selectedUserEmail = document.getElementById('selectedUserEmail');
const formTitle = document.getElementById('formTitle');
const formSubtitle = document.getElementById('formSubtitle');
const currentUnitId = faceDataConfig.dataset.currentUnitId;
const storeFaceUrl = faceDataConfig.dataset.storeUrl;
const indexFaceUrl = faceDataConfig.dataset.indexUrl;
const csrfToken = faceDataConfig.dataset.csrfToken;

if (faceCoverageBar) {
    faceCoverageBar.style.width = `${faceCoverageBar.dataset.faceCoverage || 0}%`;
}

let stream = null;
let modelsLoaded = false;
let capturedEmbedding = null;
let capturedImage = null;
let blinkVerified = false;
let eyesWereOpen = false;
let lastEar = null;
let maxOpenEar = 0;
let trackingFrame = null;
let processingBlinkDetection = false;
let lastBlinkDetectionAt = 0;
const modelBaseUrl = '/face-api/models';
const detectorOptions = new faceapi.TinyFaceDetectorOptions({
    inputSize: 224,
    scoreThreshold: 0.5,
});
const MIN_BRIGHTNESS = 38;
const MAX_BRIGHTNESS = 210;
const MIN_SHARPNESS = 8;
const BLINK_OPEN_EAR = 0.22;
const BLINK_CLOSED_EAR = 0.19;
const BLINK_DROP_RATIO = 0.72;

function setStatus(message, isError = false) {
    statusText.textContent = message;
    statusText.className = isError ? 'text-sm font-semibold text-red-200' : 'text-sm text-white';
}

function showAlert(message, type = 'success') {
    alertBox.textContent = message;
    alertBox.className = type === 'error'
        ? 'rounded-md border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800'
        : 'rounded-md border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800';
}

function clearCapture() {
    capturedEmbedding = null;
    capturedImage = null;
    faceStatus.textContent = stream ? 'Kamera aktif' : 'Menunggu kamera';
    previewImage.src = '';
    previewWrap.classList.add('hidden');
    saveFaceButton.disabled = true;
}

function resetBlinkVerification() {
    blinkVerified = false;
    eyesWereOpen = false;
    lastEar = null;
    maxOpenEar = 0;
    setBlinkVerified(false);
}

function setBlinkVerified(value) {
    blinkVerified = value;
    blinkStatus.textContent = value ? 'Terverifikasi' : 'Belum terverifikasi';
    blinkStatus.className = value ? 'mt-1 text-sm font-semibold text-blue-600' : 'mt-1 text-sm font-semibold text-gray-900';
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
    if (!video.videoWidth || !video.videoHeight) {
        return { brightness: 0, sharpness: 0 };
    }

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
    return quality.brightness >= MIN_BRIGHTNESS &&
        quality.brightness <= MAX_BRIGHTNESS &&
        quality.sharpness >= MIN_SHARPNESS;
}

function stopBlinkTracking() {
    if (trackingFrame) {
        cancelAnimationFrame(trackingFrame);
        trackingFrame = null;
    }
}

function startBlinkTracking() {
    stopBlinkTracking();
    lastBlinkDetectionAt = 0;

    const runTracking = async () => {
        trackingFrame = requestAnimationFrame(runTracking);

        if (!video.srcObject || blinkVerified || processingBlinkDetection) {
            return;
        }

        const now = performance.now();
        if (now - lastBlinkDetectionAt < 150) {
            return;
        }

        processingBlinkDetection = true;
        lastBlinkDetectionAt = now;

        try {
            const detection = await faceapi
                .detectSingleFace(video, detectorOptions)
                .withFaceLandmarks(true);

            if (!detection) {
                faceStatus.textContent = 'Wajah tidak terdeteksi';
                return;
            }

            faceStatus.textContent = 'Wajah terdeteksi';
            if (detectBlink(detection.landmarks)) {
                setBlinkVerified(true);
                setStatus('Kedipan berhasil. Tekan Capture saat wajah jelas.');
                return;
            }

            if (lastEar !== null) {
                blinkStatus.textContent = `Kedipkan mata (${lastEar.toFixed(2)})`;
            }
        } finally {
            processingBlinkDetection = false;
        }
    };

    runTracking();
}

async function loadModels() {
    if (modelsLoaded) {
        return;
    }

    setStatus('Memuat model deteksi wajah...');
    await Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri(modelBaseUrl),
        faceapi.nets.faceLandmark68TinyNet.loadFromUri(modelBaseUrl),
        faceapi.nets.faceRecognitionNet.loadFromUri(modelBaseUrl),
    ]);
    modelsLoaded = true;
    setStatus('Model siap. Aktifkan kamera untuk capture wajah.');
}

async function startCamera() {
    try {
        clearCapture();
        resetBlinkVerification();
        await loadModels();

        if (stream) {
            stream.getTracks().forEach((track) => track.stop());
        }
        stopBlinkTracking();

        stream = await navigator.mediaDevices.getUserMedia({
            video: {
                facingMode: 'user',
                width: { ideal: 640 },
                height: { ideal: 480 },
            },
            audio: false,
        });

        video.srcObject = stream;
        await video.play();
        captureFaceButton.disabled = false;
        cameraBadge.textContent = 'Kamera aktif';
        faceStatus.textContent = 'Kamera aktif';
        setStatus('Kamera aktif. Posisikan wajah jelas, kedipkan mata, lalu capture.');
        startBlinkTracking();
    } catch (error) {
        setStatus(error.message || 'Kamera gagal diaktifkan.', true);
    }
}

function getSnapshot() {
    canvas.width = video.videoWidth || 640;
    canvas.height = video.videoHeight || 480;
    const context = canvas.getContext('2d');
    context.drawImage(video, 0, 0, canvas.width, canvas.height);
    return canvas.toDataURL('image/jpeg', 0.85);
}

async function captureFace() {
    if (!video.srcObject) {
        setStatus('Aktifkan kamera terlebih dahulu.', true);
        return;
    }

    try {
        setStatus('Mendeteksi wajah...');
        const detection = await faceapi
            .detectSingleFace(video, detectorOptions)
            .withFaceLandmarks(true)
            .withFaceDescriptor();

        if (!detection) {
            clearCapture();
            faceStatus.textContent = 'Wajah tidak terdeteksi';
            setStatus('Wajah belum terdeteksi. Pastikan wajah jelas dan menghadap kamera.', true);
            return;
        }

        const quality = getFrameQuality(detection.detection.box);
        faceStatus.textContent = 'Wajah terdeteksi';

        if (!isFrameQualityGood(quality)) {
            clearCapture();
            setStatus(`Wajah belum cukup jelas. Cahaya ${Math.round(quality.brightness)}, ketajaman ${Math.round(quality.sharpness)}.`, true);
            return;
        }

        if (!blinkVerified) {
            clearCapture();
            setStatus('Kedipan belum terverifikasi. Kedipkan mata lalu tekan Capture lagi.', true);
            return;
        }

        capturedEmbedding = Array.from(detection.descriptor);
        capturedImage = getSnapshot();
        previewImage.src = capturedImage;
        previewWrap.classList.remove('hidden');
        saveFaceButton.disabled = false;
        setStatus('Foto wajah dan kedipan berhasil diverifikasi. Klik Simpan untuk menyimpan data.');
    } catch (error) {
        clearCapture();
        setStatus(error.message || 'Gagal capture wajah.', true);
    }
}

function payload() {
    return {
        user_id: userIdSelect.value,
        embedding: capturedEmbedding,
        image: capturedImage,
        blink_verified: blinkVerified ? 'true' : 'false',
        unit_id: currentUnitId,
    };
}

function redirectKeepingUnit(url) {
    if (!currentUnitId || url.includes('unit_id=')) {
        window.location.href = url;
        return;
    }

    const separator = url.includes('?') ? '&' : '?';
    window.location.href = `${url}${separator}unit_id=${encodeURIComponent(currentUnitId)}`;
}

async function saveFace() {
    if (!capturedEmbedding || !capturedImage) {
        setStatus('Capture wajah terlebih dahulu.', true);
        return;
    }

    if (!blinkVerified) {
        setStatus('Verifikasi kedipan belum berhasil.', true);
        return;
    }

    if (modeInput.value === 'store' && !userIdSelect.value) {
        setStatus('Pilih user terlebih dahulu.', true);
        return;
    }

    const isUpdate = modeInput.value === 'update';
    const url = isUpdate ? updateUrlInput.value : storeFaceUrl;
    const body = payload();
    if (isUpdate) {
        body._method = 'PUT';
    }

    saveFaceButton.disabled = true;
    setStatus('Menyimpan data wajah...');

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
            },
            body: JSON.stringify(body),
        });

        const result = await response.json();
        if (!response.ok) {
            throw new Error(result.message || Object.values(result.errors || {})[0]?.[0] || 'Gagal menyimpan data wajah.');
        }

        showAlert(result.message || 'Data wajah berhasil disimpan.');
        redirectKeepingUnit(result.redirect || indexFaceUrl);
    } catch (error) {
        saveFaceButton.disabled = false;
        showAlert(error.message || 'Gagal menyimpan data wajah.', 'error');
        setStatus(error.message || 'Gagal menyimpan data wajah.', true);
    }
}

function setUpdateMode(button) {
    modeInput.value = 'update';
    updateUrlInput.value = button.dataset.updateUrl;
    userIdSelect.value = button.dataset.userId;
    userIdSelect.disabled = true;
    selectedUserName.textContent = button.dataset.userName || '-';
    selectedUserEmail.textContent = button.dataset.userEmail || '-';
    selectedUserCard.classList.remove('hidden');
    resetModeButton.classList.remove('hidden');
    formTitle.textContent = 'Capture Ulang Data Wajah';
    formSubtitle.textContent = 'Ambil ulang foto wajah untuk memperbarui data user ini.';
    clearCapture();
    resetBlinkVerification();
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function resetMode() {
    modeInput.value = 'store';
    updateUrlInput.value = '';
    userIdSelect.disabled = false;
    userIdSelect.value = '';
    selectedUserCard.classList.add('hidden');
    resetModeButton.classList.add('hidden');
    formTitle.textContent = 'Capture Data Wajah';
    formSubtitle.textContent = 'Pilih pegawai, aktifkan kamera, verifikasi kedipan, lalu simpan.';
    clearCapture();
    resetBlinkVerification();
}

startCameraButton.addEventListener('click', startCamera);
captureFaceButton.addEventListener('click', captureFace);
saveFaceButton.addEventListener('click', saveFace);
resetModeButton.addEventListener('click', resetMode);
document.querySelectorAll('[data-edit-face]').forEach((button) => {
    button.addEventListener('click', () => setUpdateMode(button));
});
window.addEventListener('beforeunload', () => {
    stopBlinkTracking();
    if (stream) {
        stream.getTracks().forEach((track) => track.stop());
    }
});
loadModels().catch(() => {
    setStatus('Model deteksi wajah belum siap. Klik Aktifkan Kamera untuk mencoba lagi.', true);
});
</script>
@endsection
