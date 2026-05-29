@extends('layouts.admin')

@section('title', 'Pengaturan Lokasi Presensi')

@section('content')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

@php
    $currentRequestIp = request()->ip();
    $suggestedPublicSubnet = filter_var($currentRequestIp, FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)
        ? preg_replace('/\.\d+$/', '.0/24', $currentRequestIp)
        : null;
    $networkEntries = old('attendance_networks');
    if (!is_array($networkEntries)) {
        $networkEntries = \App\Support\IpNetwork::parseEntries($setting->attendance_allowed_networks);
    }
    $networkEntries = array_values($networkEntries);
    if (empty($networkEntries)) {
        $networkEntries[] = ['name' => '', 'network' => ''];
    }
@endphp

<div class="space-y-5">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-gray-700">Lokasi Presensi</h1>
            <p class="mt-0.5 text-sm text-gray-500">
                Atur titik GPS kantor, radius presensi, dan toleransi waktu absen untuk jadwal shift.
            </p>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-md border border-blue-100 bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-700">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-md border border-red-100 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    <div class="overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-3.5">
            <h2 class="text-sm font-semibold text-gray-700">Pengaturan Lokasi dan Toleransi</h2>
        </div>

        <form method="POST" action="{{ route('admin.settings.work.update') }}" class="space-y-5 p-5">
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
                        class="inline-flex items-center gap-2 rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-800">
                        <i class="fas fa-location-crosshairs text-xs"></i>
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
                            class="w-full rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-700 transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500"
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
                            class="w-full rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-700 transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500"
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
                                class="w-full rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-700 transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            >
                            <span class="text-sm text-gray-600">meter</span>
                        </div>
                    </div>
                </div>

                <p id="gpsStatus" class="mt-3 text-sm text-gray-500">
                    Klik peta untuk memilih titik kantor, atau klik Ambil GPS Saya saat perangkat berada di lokasi kantor.
                </p>

                <div class="mt-4 overflow-hidden rounded-md border border-gray-200">
                    <div id="officeMap" class="h-[360px] w-full bg-gray-100"></div>
                </div>
            </div>

            <div class="rounded-md border border-gray-200 p-4">
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
                                class="w-full rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-700 transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500"
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
                                class="w-full rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-700 transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required
                            >
                            <span class="text-sm text-gray-600">menit</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-md border border-gray-200 p-4">
                <div class="mb-4 flex flex-col gap-3 md:flex-row md:items-start md:justify-between">
                    <div>
                        <h3 class="font-bold text-gray-800">Jaringan Kantor</h3>
                        <p class="text-sm text-gray-500">Jika aktif, user hanya bisa absen dari IP atau subnet jaringan yang diizinkan.</p>
                    </div>
                    <label for="attendance_network_check_enabled" class="inline-flex cursor-pointer items-center gap-3 rounded-md border border-gray-200 bg-white px-3 py-2 text-sm font-semibold text-gray-700">
                        <input type="hidden" name="attendance_network_check_enabled" value="0">
                        <input
                            id="attendance_network_check_enabled"
                            type="checkbox"
                            name="attendance_network_check_enabled"
                            value="1"
                            class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500"
                            @checked(old('attendance_network_check_enabled', $setting->attendance_network_check_enabled ?? false))
                        >
                        Aktifkan pembatasan jaringan
                    </label>
                </div>

                <div class="overflow-hidden rounded-md border border-gray-200">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50 text-left text-xs font-bold uppercase tracking-wide text-gray-500">
                            <tr>
                                <th class="px-4 py-3">Nama Jaringan</th>
                                <th class="px-4 py-3">IP/Subnet</th>
                                <th class="w-16 px-4 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="attendanceNetworkRows" class="divide-y divide-gray-100 bg-white">
                            @foreach($networkEntries as $index => $entry)
                                <tr>
                                    <td class="px-4 py-3 align-top">
                                        <input
                                            type="text"
                                            name="attendance_networks[{{ $index }}][name]"
                                            value="{{ $entry['name'] ?? '' }}"
                                            placeholder="Wi-Fi Kantor / Router Utama"
                                            class="w-full rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-700 transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        >
                                    </td>
                                    <td class="px-4 py-3 align-top">
                                        <input
                                            type="text"
                                            name="attendance_networks[{{ $index }}][network]"
                                            value="{{ $entry['network'] ?? '' }}"
                                            placeholder="114.79.18.0/24 atau 36.77.44.7"
                                            class="w-full rounded-md border border-gray-200 px-3 py-2 font-mono text-sm text-gray-700 transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        >
                                    </td>
                                    <td class="px-4 py-3 text-center align-top">
                                        <button type="button" data-remove-network-row class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-gray-200 text-gray-500 transition hover:bg-red-50 hover:text-red-600" title="Hapus jaringan">
                                            <i class="fas fa-trash text-xs"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="mt-3 flex flex-col gap-2 sm:flex-row sm:items-center">
                    <button
                        id="addAttendanceNetwork"
                        type="button"
                        class="inline-flex items-center justify-center gap-2 rounded-md border border-gray-200 bg-white px-4 py-2 text-sm font-semibold text-gray-700 transition hover:bg-gray-50">
                        <i class="fas fa-plus text-xs"></i>
                        Tambah Jaringan
                    </button>
                    @if($currentRequestIp)
                        <button
                            id="useCurrentRequestIp"
                            type="button"
                            data-ip="{{ $currentRequestIp }}"
                            data-subnet="{{ $suggestedPublicSubnet }}"
                            class="inline-flex items-center justify-center gap-2 rounded-md bg-gray-900 px-4 py-2 text-sm font-semibold text-white transition hover:bg-gray-800">
                            <i class="fas fa-wifi text-xs"></i>
                            Pakai IP Saat Ini
                        </button>
                    @endif
                </div>

                <div>
                    <p class="mt-2 text-sm text-gray-500">
                        Isi IP publik kantor atau subnet jika IP sering berubah di angka terakhir. Contoh:
                        <span class="font-semibold text-gray-700">114.79.18.0/24</span>.
                    </p>
                    <p class="mt-1 text-xs text-gray-500">
                        IP request halaman admin saat ini: <span class="font-semibold text-gray-700">{{ $currentRequestIp }}</span>.
                        @if($suggestedPublicSubnet)
                            Jika IP publik koneksi ini sering berubah di angka terakhir, gunakan subnet:
                            <span class="font-semibold text-gray-700">{{ $suggestedPublicSubnet }}</span>.
                        @endif
                    </p>
                </div>
            </div>

            <div class="rounded-md border border-gray-200 bg-gray-50 p-4 text-sm text-gray-600">
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
                <p class="mt-1">
                    Pembatasan jaringan kantor
                    <span class="font-semibold text-gray-800">{{ ($setting->attendance_network_check_enabled ?? false) ? 'aktif' : 'nonaktif' }}</span>
                    @if($setting->attendance_network_check_enabled && filled($setting->attendance_allowed_networks))
                        untuk {{ count(\App\Support\IpNetwork::parseEntries($setting->attendance_allowed_networks)) }} IP/subnet.
                    @endif
                </p>
            </div>

            <div class="flex justify-end">
                <button class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                    <i class="fas fa-save text-xs"></i>
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

const attendanceNetworkRows = document.getElementById('attendanceNetworkRows');
const addAttendanceNetworkButton = document.getElementById('addAttendanceNetwork');
const useCurrentRequestIpButton = document.getElementById('useCurrentRequestIp');
let attendanceNetworkIndex = {{ count($networkEntries) }};

function createAttendanceNetworkRow(name = '', network = '') {
    const row = document.createElement('tr');
    row.innerHTML = `
        <td class="px-4 py-3 align-top">
            <input
                type="text"
                name="attendance_networks[${attendanceNetworkIndex}][name]"
                value="${escapeHtmlAttribute(name)}"
                placeholder="Wi-Fi Kantor / Router Utama"
                class="w-full rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-700 transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
        </td>
        <td class="px-4 py-3 align-top">
            <input
                type="text"
                name="attendance_networks[${attendanceNetworkIndex}][network]"
                value="${escapeHtmlAttribute(network)}"
                placeholder="114.79.18.0/24 atau 36.77.44.7"
                class="w-full rounded-md border border-gray-200 px-3 py-2 font-mono text-sm text-gray-700 transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
        </td>
        <td class="px-4 py-3 text-center align-top">
            <button type="button" data-remove-network-row class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-gray-200 text-gray-500 transition hover:bg-red-50 hover:text-red-600" title="Hapus jaringan">
                <i class="fas fa-trash text-xs"></i>
            </button>
        </td>
    `;
    attendanceNetworkIndex += 1;
    return row;
}

function escapeHtmlAttribute(value) {
    return String(value)
        .replaceAll('&', '&amp;')
        .replaceAll('"', '&quot;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;');
}

function addAttendanceNetworkRow(name = '', network = '') {
    attendanceNetworkRows.appendChild(createAttendanceNetworkRow(name, network));
}

addAttendanceNetworkButton?.addEventListener('click', () => {
    addAttendanceNetworkRow();
});

useCurrentRequestIpButton?.addEventListener('click', () => {
    addAttendanceNetworkRow(
        'Koneksi saat ini',
        useCurrentRequestIpButton.dataset.subnet || useCurrentRequestIpButton.dataset.ip || ''
    );
});

attendanceNetworkRows?.addEventListener('click', event => {
    const button = event.target.closest('[data-remove-network-row]');
    if (!button) return;

    const row = button.closest('tr');
    if (!row) return;

    if (attendanceNetworkRows.querySelectorAll('tr').length <= 1) {
        row.querySelectorAll('input').forEach(input => input.value = '');
        return;
    }

    row.remove();
});

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
