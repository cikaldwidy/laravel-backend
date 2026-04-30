@extends('layouts.admin')

@section('title', 'Data Wajah')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="bg-green-100 text-green-800 px-4 py-3 rounded">{{ session('success') }}</div>
    @endif

    <div id="alertBox" class="hidden px-4 py-3 rounded"></div>

    <div class="bg-white p-6 rounded-xl shadow">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-5">
            <div>
                <h2 id="formTitle" class="text-lg font-bold text-gray-800">Capture Data Wajah</h2>
                <p id="formSubtitle" class="text-sm text-gray-500 mt-1">Pilih user, aktifkan kamera, lalu ambil foto wajah.</p>
            </div>
            <button id="resetMode" type="button" class="hidden border border-gray-300 text-gray-700 px-4 py-2 rounded font-semibold hover:bg-gray-50">
                Mode Tambah
            </button>
        </div>

        <div class="grid lg:grid-cols-[1.2fr_0.8fr] gap-6">
            <div>
                <div class="relative rounded-md overflow-hidden bg-slate-100 border border-gray-100 aspect-[4/3]">
                    <video id="video" autoplay muted playsinline class="w-full h-full object-cover"></video>
                    <div class="absolute inset-5 border-4 border-white rounded-2xl pointer-events-none"></div>
                    <div id="previewWrap" class="hidden absolute inset-0 bg-black">
                        <img id="previewImage" src="" alt="Preview wajah" class="w-full h-full object-cover">
                    </div>
                    <div class="absolute top-4 left-4 bg-blue-600/90 text-white text-xs px-3 py-2 rounded-full shadow">
                        <span id="cameraBadge">Kamera belum aktif</span>
                    </div>
                </div>

                <p id="status" class="mt-4 text-sm text-gray-500">Siapkan kamera untuk capture wajah.</p>
                <div class="mt-3 grid sm:grid-cols-2 gap-3 text-sm">
                    <div class="border rounded-md px-3 py-2 bg-white">
                        <p class="text-gray-500">Wajah</p>
                        <p id="faceStatus" class="font-semibold text-gray-800">Menunggu kamera</p>
                    </div>
                    <div class="border rounded-md px-3 py-2 bg-white">
                        <p class="text-gray-500">Kedipan</p>
                        <p id="blinkStatus" class="font-semibold text-gray-800">Belum terverifikasi</p>
                    </div>
                </div>

                <div class="mt-5 grid sm:grid-cols-3 gap-3">
                    <button id="startCamera" type="button" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-3 rounded-md font-semibold text-sm shadow transition">
                        <i class="fa-solid fa-camera mr-2"></i>Aktifkan Kamera
                    </button>
                    <button id="captureFace" type="button" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-3 rounded-md font-semibold text-sm shadow disabled:opacity-50 disabled:cursor-not-allowed transition" disabled>
                        <i class="fa-solid fa-circle-dot mr-2"></i>Capture
                    </button>
                    <button id="saveFace" type="button" class="bg-amber-500 hover:bg-amber-600 text-white px-4 py-3 rounded-md font-semibold text-sm shadow disabled:opacity-50 disabled:cursor-not-allowed transition" disabled>
                        <i class="fa-solid fa-floppy-disk mr-2"></i>Simpan
                    </button>
                </div>
            </div>

            <div class="bg-[#fbfdff] border border-gray-100 rounded-md p-5">
                <input type="hidden" id="mode" value="store">
                <input type="hidden" id="updateUrl" value="">

                <label for="userId" class="block text-sm font-semibold text-gray-700 mb-2">User</label>
                <select id="userId" class="w-full border rounded px-3 py-2">
                    <option value="">Pilih user</option>
                    @foreach($usersWithoutFaceData as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} - {{ $user->email }}</option>
                    @endforeach
                </select>

                <div id="selectedUserCard" class="hidden mt-4 p-4 rounded-md border border-blue-100 bg-blue-50">
                    <p class="text-xs text-blue-600 font-semibold">Update data wajah</p>
                    <p id="selectedUserName" class="text-sm font-bold text-gray-800 mt-1"></p>
                    <p id="selectedUserEmail" class="text-xs text-gray-500"></p>
                </div>

                <div class="mt-6 grid grid-cols-2 gap-3 text-sm">
                    <div class="p-4 rounded-md bg-white border">
                        <p class="text-gray-500">User Belum Terdaftar</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">{{ $usersWithoutFaceData->count() }}</p>
                    </div>
                    <div class="p-4 rounded-md bg-white border">
                        <p class="text-gray-500">Data Wajah</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">{{ $faceEmbeddings->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <canvas id="captureCanvas" class="hidden"></canvas>
    </div>

    <div class="bg-white p-6 rounded-xl shadow">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-bold text-gray-800">Daftar Data Wajah</h2>
            <span class="text-sm text-gray-500">{{ $faceEmbeddings->count() }} data</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="p-3 text-left">Foto</th>
                        <th class="p-3 text-left">User</th>
                        <th class="p-3 text-left">Unit</th>
                        <th class="p-3 text-left">Terakhir Update</th>
                        <th class="p-3 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($faceEmbeddings as $faceEmbedding)
                        <tr>
                            <td class="p-3">
                                <div class="w-20 h-20 rounded-md overflow-hidden bg-gray-100 border">
                                    @if($faceEmbedding->photo_path)
                                        <img src="{{ asset('storage/' . $faceEmbedding->photo_path) }}" alt="Foto wajah {{ $faceEmbedding->user?->name }}" class="w-full h-full object-cover">
                                    @else
                                        <div class="w-full h-full flex items-center justify-center text-gray-400">
                                            <i class="fa-regular fa-image text-2xl"></i>
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="p-3">
                                <div class="font-semibold text-gray-800">{{ $faceEmbedding->user?->name ?? '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $faceEmbedding->user?->email ?? '-' }}</div>
                            </td>
                            <td class="p-3">{{ $faceEmbedding->user?->employeeDetail?->unit?->nama_unit ?? '-' }}</td>
                            <td class="p-3 whitespace-nowrap">{{ $faceEmbedding->updated_at?->format('d M Y H:i') }}</td>
                            <td class="p-3">
                                <div class="flex flex-wrap gap-3">
                                    <button
                                        type="button"
                                        class="text-blue-600 font-semibold"
                                        data-edit-face
                                        data-user-id="{{ $faceEmbedding->user_id }}"
                                        data-user-name="{{ $faceEmbedding->user?->name }}"
                                        data-user-email="{{ $faceEmbedding->user?->email }}"
                                        data-update-url="{{ route('admin.face_data.update', $faceEmbedding) }}"
                                    >
                                        Capture Ulang
                                    </button>
                                    <form method="POST" action="{{ route('admin.face_data.destroy', $faceEmbedding) }}" onsubmit="return confirm('Hapus data wajah user ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 font-semibold">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-6 text-center text-gray-500">Belum ada data wajah.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
<script>
const video = document.getElementById('video');
const canvas = document.getElementById('captureCanvas');
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
    statusText.className = isError ? 'mt-4 text-sm text-red-500' : 'mt-4 text-sm text-gray-500';
}

function showAlert(message, type = 'success') {
    alertBox.textContent = message;
    alertBox.className = type === 'error'
        ? 'bg-red-100 text-red-800 px-4 py-3 rounded'
        : 'bg-green-100 text-green-800 px-4 py-3 rounded';
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
    blinkStatus.className = value ? 'font-semibold text-emerald-700' : 'font-semibold text-gray-800';
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
    };
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
    const url = isUpdate ? updateUrlInput.value : '{{ route('admin.face_data.store') }}';
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
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
            },
            body: JSON.stringify(body),
        });

        const result = await response.json();
        if (!response.ok) {
            throw new Error(result.message || Object.values(result.errors || {})[0]?.[0] || 'Gagal menyimpan data wajah.');
        }

        showAlert(result.message || 'Data wajah berhasil disimpan.');
        window.location.href = result.redirect || '{{ route('admin.face_data.index') }}';
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
    formSubtitle.textContent = 'Pilih user, aktifkan kamera, lalu ambil foto wajah.';
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
