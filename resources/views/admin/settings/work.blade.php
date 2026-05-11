@extends('layouts.admin')

@section('title', 'Pengaturan Lokasi Presensi')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

<div class="max-w-5xl mx-auto">
    <div class="bg-white rounded-lg shadow">
        <div class="p-5 border-b">
            <h2 class="font-bold text-lg text-gray-800">Lokasi Presensi</h2>
            <p class="text-sm text-gray-500 mt-1">
                Atur titik GPS kantor, radius presensi, dan toleransi waktu absen untuk jadwal shift.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.settings.work.update') }}" class="p-5 space-y-5">
            @csrf

            <div>
                <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
                    <div>
                        <h3 class="font-bold text-gray-800">GPS Kantor</h3>
                        <p class="text-sm text-gray-500">User hanya bisa absen jika berada di dalam radius ini.</p>
                    </div>
                    <button
                        id="useCurrentLocation"
                        type="button"
                        class="bg-gray-900 hover:bg-gray-800 text-white px-4 py-2 rounded-md text-sm font-semibold">
                        Ambil GPS Saya
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                    <div>
                        <label for="office_latitude" class="block text-sm font-semibold text-gray-700 mb-2">
                            Latitude Kantor
                        </label>
                        <input
                            id="office_latitude"
                            type="number"
                            step="0.0000001"
                            min="-90"
                            max="90"
                            name="office_latitude"
                            value="{{ old('office_latitude', $setting->office_latitude ?? config('attendance.office_latitude')) }}"
                            class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required
                        >
                    </div>

                    <div>
                        <label for="office_longitude" class="block text-sm font-semibold text-gray-700 mb-2">
                            Longitude Kantor
                        </label>
                        <input
                            id="office_longitude"
                            type="number"
                            step="0.0000001"
                            min="-180"
                            max="180"
                            name="office_longitude"
                            value="{{ old('office_longitude', $setting->office_longitude ?? config('attendance.office_longitude')) }}"
                            class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                            required
                        >
                    </div>

                    <div>
                        <label for="radius_meters" class="block text-sm font-semibold text-gray-700 mb-2">
                            Radius
                        </label>
                        <div class="flex items-center gap-3">
                            <input
                                id="radius_meters"
                                type="number"
                                min="10"
                                max="5000"
                                name="radius_meters"
                                value="{{ old('radius_meters', $setting->radius_meters ?? config('attendance.radius_meters', 100)) }}"
                                class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            >
                            <span class="text-sm text-gray-600">meter</span>
                        </div>
                    </div>
                </div>

                <p id="gpsStatus" class="mt-3 text-sm text-gray-500">
                    Klik peta untuk memilih titik kantor, atau klik Ambil GPS Saya saat perangkat berada di lokasi kantor.
                </p>

                <div class="mt-4 overflow-hidden rounded-lg border">
                    <div id="officeMap" class="h-[360px] w-full bg-gray-100"></div>
                </div>
            </div>

            <div class="border rounded-lg p-4">
                <div class="mb-4">
                    <h3 class="font-bold text-gray-800">Toleransi Absen Shift</h3>
                    <p class="text-sm text-gray-500">Jadwal utama tetap mengikuti shift per pegawai. Pengaturan ini hanya menentukan kapan tombol absen boleh aktif.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    <div>
                        <label for="checkin_early_minutes" class="block text-sm font-semibold text-gray-700 mb-2">
                            Boleh absen sebelum shift
                        </label>
                        <div class="flex items-center gap-3">
                            <input
                                id="checkin_early_minutes"
                                type="number"
                                min="0"
                                max="240"
                                name="checkin_early_minutes"
                                value="{{ old('checkin_early_minutes', $setting->checkin_early_minutes ?? \App\Models\WorkSetting::DEFAULT_CHECKIN_EARLY_MINUTES) }}"
                                class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            >
                            <span class="text-sm text-gray-600">menit</span>
                        </div>
                    </div>

                    <div>
                        <label for="checkout_late_minutes" class="block text-sm font-semibold text-gray-700 mb-2">
                            Sesi absen berakhir setelah shift
                        </label>
                        <div class="flex items-center gap-3">
                            <input
                                id="checkout_late_minutes"
                                type="number"
                                min="0"
                                max="480"
                                name="checkout_late_minutes"
                                value="{{ old('checkout_late_minutes', $setting->checkout_late_minutes ?? \App\Models\WorkSetting::DEFAULT_CHECKOUT_LATE_MINUTES) }}"
                                class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            >
                            <span class="text-sm text-gray-600">menit</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-gray-50 border rounded-lg p-4 text-sm text-gray-600">
                <p>
                    Titik GPS kantor:
                    <span class="font-semibold text-gray-800">
                        {{ $setting->office_latitude ?? config('attendance.office_latitude') }},
                        {{ $setting->office_longitude ?? config('attendance.office_longitude') }}
                    </span>
                    dengan radius
                    <span class="font-semibold text-gray-800">{{ $setting->radius_meters ?? config('attendance.radius_meters', 100) }} meter</span>.
                </p>
                <p class="mt-1">
                    Absen dapat dimulai
                    <span class="font-semibold text-gray-800">{{ $setting->checkin_early_minutes ?? \App\Models\WorkSetting::DEFAULT_CHECKIN_EARLY_MINUTES }} menit</span>
                    sebelum jam masuk shift dan sesi absen berakhir
                    <span class="font-semibold text-gray-800">{{ $setting->checkout_late_minutes ?? \App\Models\WorkSetting::DEFAULT_CHECKOUT_LATE_MINUTES }} menit</span>
                    setelah jam pulang shift.
                </p>
            </div>

            <div class="flex justify-end">
                <button class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-md font-semibold">
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</div>

<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
const useCurrentLocationButton = document.getElementById('useCurrentLocation');
const latitudeInput = document.getElementById('office_latitude');
const longitudeInput = document.getElementById('office_longitude');
const radiusInput = document.getElementById('radius_meters');
const gpsStatus = document.getElementById('gpsStatus');
const defaultLatitude = Number(latitudeInput.value) || {{ config('attendance.office_latitude') }};
const defaultLongitude = Number(longitudeInput.value) || {{ config('attendance.office_longitude') }};
let officeMap = null;
let officeMarker = null;
let officeCircle = null;

function setGpsStatus(message, className = 'text-gray-500') {
    gpsStatus.textContent = message;
    gpsStatus.className = `mt-3 text-sm ${className}`;
}

function updateMapPoint(latitude, longitude, shouldPan = true) {
    latitudeInput.value = Number(latitude).toFixed(7);
    longitudeInput.value = Number(longitude).toFixed(7);

    if (!officeMap || !window.L) return;

    const latLng = [Number(latitudeInput.value), Number(longitudeInput.value)];
    const radius = Number(radiusInput.value) || 100;

    if (!officeMarker) {
        officeMarker = L.marker(latLng, { draggable: true }).addTo(officeMap);
        officeMarker.on('dragend', event => {
            const position = event.target.getLatLng();
            updateMapPoint(position.lat, position.lng, false);
            setGpsStatus('Titik kantor diperbarui dari marker. Simpan pengaturan untuk memakai lokasi ini.', 'text-green-600');
        });
    } else {
        officeMarker.setLatLng(latLng);
    }

    if (!officeCircle) {
        officeCircle = L.circle(latLng, {
            radius,
            color: '#2563eb',
            fillColor: '#3b82f6',
            fillOpacity: 0.16,
            weight: 2,
        }).addTo(officeMap);
    } else {
        officeCircle.setLatLng(latLng);
        officeCircle.setRadius(radius);
    }

    if (shouldPan) {
        officeMap.setView(latLng, officeMap.getZoom() || 17);
    }
}

function initializeOfficeMap() {
    if (!window.L) {
        setGpsStatus('Map gagal dimuat. Latitude dan longitude masih bisa diisi manual.', 'text-red-600');
        return;
    }

    officeMap = L.map('officeMap').setView([defaultLatitude, defaultLongitude], 17);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap contributors',
    }).addTo(officeMap);

    officeMap.on('click', event => {
        updateMapPoint(event.latlng.lat, event.latlng.lng, false);
        setGpsStatus('Titik kantor dipilih dari peta. Simpan pengaturan untuk memakai lokasi ini.', 'text-green-600');
    });

    radiusInput.addEventListener('input', () => {
        if (officeCircle) {
            officeCircle.setRadius(Number(radiusInput.value) || 100);
        }
    });

    [latitudeInput, longitudeInput].forEach(input => {
        input.addEventListener('change', () => {
            const latitude = Number(latitudeInput.value);
            const longitude = Number(longitudeInput.value);

            if (Number.isFinite(latitude) && Number.isFinite(longitude)) {
                updateMapPoint(latitude, longitude);
            }
        });
    });

    updateMapPoint(defaultLatitude, defaultLongitude, false);
}

useCurrentLocationButton.addEventListener('click', () => {
    if (!navigator.geolocation) {
        setGpsStatus('Browser tidak mendukung GPS.', 'text-red-600');
        return;
    }

    setGpsStatus('Mengambil lokasi perangkat...');
    useCurrentLocationButton.disabled = true;

    navigator.geolocation.getCurrentPosition(
        position => {
            updateMapPoint(position.coords.latitude, position.coords.longitude);
            setGpsStatus('GPS berhasil diambil. Simpan pengaturan untuk memakai lokasi ini.', 'text-green-600');
            useCurrentLocationButton.disabled = false;
        },
        () => {
            setGpsStatus('Gagal mengambil GPS. Pastikan izin lokasi browser aktif.', 'text-red-600');
            useCurrentLocationButton.disabled = false;
        },
        { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
    );
});

initializeOfficeMap();
</script>
@endsection
