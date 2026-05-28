@extends('layouts.app')

@section('title', 'E-Presensi')

@section('content')
<style>
    @keyframes attendance-face-scan-line {
        0% { top: 0%; opacity: 0; }
        8% { opacity: 1; }
        50% { top: calc(100% - 3px); opacity: 1; }
        92% { opacity: 1; }
        100% { top: 0%; opacity: 0; }
    }

    @keyframes attendance-face-scan-glow {
        0% { top: 0%; opacity: 0; }
        8% { opacity: 0.6; }
        50% { top: calc(82% - 3px); opacity: 0.85; }
        92% { opacity: 0.5; }
        100% { top: 0%; opacity: 0; }
    }

    .attendance-face-guide {
        width: min(68%, 29rem);
        aspect-ratio: 1;
    }

    .attendance-capture-shell {
        position: relative;
        border: 1px solid rgba(148, 163, 184, 0.16);
        box-shadow:
            0 24px 54px rgba(15, 23, 42, 0.2),
            inset 0 1px 0 rgba(255, 250, 245, 0.05);
    }

    .attendance-capture-shell::before {
        content: '';
        position: absolute;
        inset: 0;
        pointer-events: none;
        background:
            radial-gradient(circle at top left, rgba(251, 191, 36, 0.1), transparent 24%),
            radial-gradient(circle at right center, rgba(96, 165, 250, 0.16), transparent 30%);
        opacity: 0.82;
    }

    .attendance-capture-stage::after {
        content: '';
        position: absolute;
        inset: 1.1rem;
        border-radius: 1.4rem;
        border: 1px solid rgba(255, 255, 255, 0.06);
        pointer-events: none;
    }

    .attendance-face-blur-outer {
        position: absolute;
        inset: 8%;
        border-radius: 50%;
        pointer-events: none;
        box-shadow:
            0 0 0 9999px rgba(8, 15, 40, 0.32),
            0 0 40px 8px rgba(0, 0, 0, 0.38) inset;
        opacity: 0;
        transition: opacity 0.35s ease;
    }

    .attendance-face-inner-mask {
        position: absolute;
        inset: 10%;
        border-radius: 50%;
        background: radial-gradient(circle at center, rgba(255, 255, 255, 0.98) 0%, rgba(248, 250, 252, 0.95) 60%, rgba(241, 245, 249, 0.92) 100%);
        box-shadow: inset 0 0 28px rgba(148, 163, 184, 0.12);
        transition: opacity 0.35s ease, background 0.35s ease;
    }

    .attendance-face-guide.is-camera-ready .attendance-face-inner-mask {
        opacity: 0.08;
    }

    .attendance-face-guide.is-camera-ready .attendance-face-blur-outer {
        opacity: 1;
    }

    .attendance-face-frame {
        position: absolute;
        inset: 10%;
        border-radius: 50%;
        border: 3px solid rgba(59, 130, 246, 0.75);
        transition: all 0.35s ease;
    }

    .attendance-face-frame.valid {
        border-color: rgba(59, 130, 246, 0.95);
        box-shadow:
            inset 0 0 30px 6px rgba(59, 130, 246, 0.1),
            0 0 0 12px rgba(59, 130, 246, 0.08);
    }

    .attendance-face-frame.invalid {
        border-color: rgba(248, 113, 113, 0.9);
        box-shadow:
            inset 0 0 20px 4px rgba(248, 113, 113, 0.08),
            0 0 0 12px rgba(255, 255, 255, 0.08);
    }

    .attendance-face-scan-track {
        position: absolute;
        top: 10%;
        right: 10%;
        bottom: 10%;
        left: 10%;
        border-radius: 50%;
    }

    .attendance-face-scan-line {
        position: absolute;
        left: -5%;
        right: -5%;
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
    }

    .attendance-face-scan-glow {
        position: absolute;
        left: 10%;
        right: 10%;
        top: 0;
        height: 22%;
        border-radius: 9999px;
        background: radial-gradient(ellipse at center, rgba(59, 130, 246, 0.22), transparent 70%);
        opacity: 0;
    }

    .attendance-face-scan-track.is-active .attendance-face-scan-line {
        animation: attendance-face-scan-line 1.6s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        opacity: 1;
    }

    .attendance-face-scan-track.is-active .attendance-face-scan-glow {
        animation: attendance-face-scan-glow 1.6s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        opacity: 1;
    }

    .attendance-datetime-chip {
        backdrop-filter: blur(10px);
        background: rgba(255, 247, 237, 0.16);
        border: 1px solid rgba(255, 237, 213, 0.14);
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.16);
        color: #f8fafc;
    }

    .attendance-map-panel {
        background: linear-gradient(180deg, rgba(30, 41, 59, 0.94), rgba(15, 23, 42, 0.96));
    }

    .attendance-map-frame {
        box-shadow:
            inset 0 1px 0 rgba(255, 255, 255, 0.08),
            0 14px 30px rgba(15, 23, 42, 0.14);
    }

    .attendance-gps-badge {
        backdrop-filter: blur(12px);
        background: rgba(30, 41, 59, 0.56);
        border: 1px solid rgba(255, 237, 213, 0.1);
    }

    .attendance-topbar {
        min-height: calc(3.5rem + env(safe-area-inset-top));
        padding-top: env(safe-area-inset-top);
    }

    @media (min-width: 768px) {
        .user-attendance-main {
            display: grid;
            grid-template-columns: minmax(0, 1fr) minmax(18rem, 26rem);
            align-items: start;
        }
    }

    @media (min-width: 1180px) {
        .user-attendance-main {
            grid-template-columns: minmax(36rem, 1fr) minmax(24rem, 30rem);
        }
    }

    @media (max-width: 767px) {
        .user-attendance-main {
            padding-top: 0.75rem !important;
            padding-left: 0.75rem !important;
            padding-right: 0.75rem !important;
            gap: 0.75rem;
        }

        .attendance-face-guide {
            width: min(78vw, 19rem);
        }

        .attendance-capture-shell {
            border-radius: 0.375rem;
        }

        .attendance-capture-stage {
            aspect-ratio: 1 / 1.02;
            min-height: 23rem;
        }

        .attendance-overlay {
            padding: 0 1.25rem;
            text-align: center;
            line-height: 1.5;
            font-size: 0.95rem;
        }

        .attendance-datetime-chip {
            font-size: 0.72rem;
            padding: 0.55rem 0.8rem;
        }

        .attendance-camera-badge {
            left: 1rem;
            right: 1rem;
            bottom: 1rem;
            font-size: 0.95rem;
            padding: 0.75rem 1rem;
        }

        .attendance-map-panel {
            padding: 0.75rem;
        }

        .attendance-map-frame {
            border-radius: 0.375rem;
        }

        #attendanceMap {
            height: 9.5rem;
        }

        .attendance-gps-badge {
            left: 0.75rem;
            right: 0.75rem;
            bottom: 0.75rem;
            max-width: none;
        }

        .attendance-action-card,
        .attendance-info-card {
            border-radius: 1.25rem;
        }

        .attendance-schedule-grid {
            gap: 0.5rem;
        }

        .attendance-schedule-card {
            padding: 0.75rem 0.5rem;
        }

        .attendance-schedule-card p.text-xs {
            font-size: 0.72rem;
        }

        .attendance-status-grid {
            gap: 0.75rem;
        }

        .attendance-status-text {
            margin-top: 0;
            line-height: 1.55;
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
    $shiftDisplayName = 'Belum Dijadwalkan';

    if ($hasScheduledShift && $scheduledShift?->jam_masuk) {
        $shiftHour = (int) $scheduledShift->jam_masuk->format('H');
        $shiftDisplayName = match (true) {
            $shiftHour < 12 => 'Shift Pagi',
            $shiftHour < 18 => 'Shift Sore',
            default => 'Shift Malam',
        };
    }
@endphp

<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">

<div class="user-page">
    <div class="user-phone">
        <header class="attendance-topbar bg-blue-800 text-white flex items-center px-4 shadow">
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

            <div class="space-y-4">
                <section class="attendance-capture-shell bg-gradient-to-br from-slate-950 via-slate-900 to-blue-950 rounded-md overflow-hidden shadow-xl">
                    <div class="attendance-capture-stage relative aspect-[4/3] bg-[radial-gradient(circle_at_top,_rgba(251,191,36,0.08),_transparent_24%),radial-gradient(circle_at_right,_rgba(59,130,246,0.12),_transparent_30%),linear-gradient(135deg,_#111827_0%,_#172554_48%,_#1e293b_100%)]">
                        @if($sudahPulang && $fotoAktif)
                            <img src="{{ asset('storage/' . $fotoAktif) }}" alt="Foto presensi" class="w-full h-full object-cover">
                        @elseif($sudahPulang && !$fotoAktif)
                            <div class="w-full h-full flex flex-col items-center justify-center text-white/70 bg-gradient-to-br from-slate-800 to-slate-950">
                                <i class="fa-solid fa-camera text-4xl"></i>
                                <p class="text-xs mt-3">Foto presensi akan tampil di sini</p>
                            </div>
                        @else
                            <video id="video" autoplay muted playsinline class="w-full h-full object-cover"></video>
                            <div
                                id="cameraOverlay"
                                class="attendance-overlay absolute inset-0 z-20 flex items-center justify-center bg-black/35 text-white text-xs font-semibold"
                                data-mobile-text="Tekan tombol Masuk"
                                data-desktop-text="Tekan Masuk/Verifikasi untuk menyalakan kamera"
                            >
                                Tekan Masuk/Verifikasi untuk menyalakan kamera
                            </div>
                        @endif
                        <div id="attendanceHeadGuide" class="attendance-face-guide pointer-events-none absolute left-1/2 top-1/2 z-10 -translate-x-1/2 -translate-y-1/2">
                            <div class="attendance-face-blur-outer"></div>
                            <div class="attendance-face-inner-mask"></div>
                            <div id="attendanceHeadFrame" class="attendance-face-frame"></div>
                            <div id="attendanceScanTrack" class="attendance-face-scan-track">
                                <div class="attendance-face-scan-glow"></div>
                                <div class="attendance-face-scan-line"></div>
                            </div>
                        </div>
                        <div class="attendance-datetime-chip absolute top-3 left-3 text-white text-xs font-semibold px-3 py-1 rounded-xl shadow">
                            {{ now()->translatedFormat('d F Y') }}
                        </div>
                        <div id="clock" class="attendance-datetime-chip absolute top-3 right-3 text-white text-xs font-bold px-3 py-1 rounded-xl shadow">
                            --:--:--
                        </div>
                    </div>

                    <div class="attendance-map-panel p-3">
                        <div class="attendance-map-frame relative rounded-md overflow-hidden border border-slate-200/10 bg-slate-900/20">
                            <div
                                id="attendanceMap"
                                class="h-28 bg-blue-700"
                                data-office-lat="{{ (float) $officeLatitude }}"
                                data-office-lng="{{ (float) $officeLongitude }}"
                                data-office-radius="{{ (int) $officeRadius }}"
                            ></div>
                            <div class="attendance-gps-badge absolute left-3 bottom-3 text-white text-xs px-3 py-2 rounded-xl max-w-[88%]">
                                <i class="fa-solid fa-location-dot mr-1"></i>
                                <span id="gpsStatus">Menunggu lokasi user</span>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="attendance-action-card bg-white/80 backdrop-blur rounded-2xl border border-white/70 shadow-sm p-3">
                    <div class="relative h-14 rounded-full bg-slate-100 overflow-hidden">
                        <div class="absolute inset-0 grid grid-cols-2">
                            <div class="bg-blue-600/15"></div>
                            <div class="bg-red-500/15"></div>
                        </div>

                        <div class="relative z-10 h-full grid grid-cols-2 gap-2 p-2">
                            <button
                                id="startVerification"
                                type="button"
                                class="h-full rounded-full flex items-center justify-center gap-2 font-bold text-sm shadow-sm transition
                                    {{ ($sudahPulang || empty($canAttend) || (isset($approvedLeave) && $approvedLeave)) ? 'bg-slate-200 text-slate-400' : (!$sudahMasuk ? 'bg-blue-700 text-white' : 'bg-white text-blue-800 border border-blue-100') }}"
                                @if($sudahPulang || empty($canAttend) || (isset($approvedLeave) && $approvedLeave)) disabled @endif
                            >
                                <i class="fa-solid fa-fingerprint"></i>
                                {{ ($sudahPulang || empty($canAttend) || (isset($approvedLeave) && $approvedLeave)) ? 'Selesai' : ($jenisAbsen === 'Pulang' ? 'Verifikasi' : 'Masuk') }}
                            </button>

                            <button
                                id="submitAttendance"
                                type="button"
                                class="h-full rounded-full flex items-center justify-center gap-2 font-bold text-sm shadow-sm transition
                                    bg-red-600 text-white disabled:bg-red-100 disabled:text-red-400"
                                disabled
                            >
                                <i class="fa-solid fa-paper-plane"></i>
                                {{ $jenisAbsen === 'Pulang' ? 'Pulang' : 'Kirim' }}
                            </button>
                        </div>

                        <div class="pointer-events-none absolute top-1/2 -translate-y-1/2 left-1/2 -translate-x-1/2 w-12 h-12 rounded-full bg-white shadow border border-slate-200 flex items-center justify-center">
                            <span class="w-9 h-9 rounded-full bg-blue-700/10 flex items-center justify-center">
                                <i class="fa-solid fa-circle text-amber-400 text-[10px]"></i>
                            </span>
                        </div>
                    </div>
                </section>

            </div>

            <div class="space-y-4">
                <section class="attendance-schedule-grid grid grid-cols-3 gap-2">
                    <div class="attendance-schedule-card bg-blue-700 text-white rounded-xl p-3 text-center shadow">
                        <i class="fa-solid fa-user-clock text-sm"></i>
                        <p class="text-[11px] mt-1 opacity-90">Shift</p>
                        <p class="text-xs font-bold">{{ $shiftDisplayName }}</p>
                    </div>
                    <div class="attendance-schedule-card bg-blue-700 text-white rounded-xl p-3 text-center shadow">
                        <i class="fa-solid fa-right-to-bracket text-sm"></i>
                        <p class="text-[11px] mt-1 opacity-90">Jam Masuk</p>
                        <p class="text-xs font-bold">{{ $jamMasuk }}</p>
                    </div>
                    <div class="attendance-schedule-card bg-red-600 text-white rounded-xl p-3 text-center shadow">
                        <i class="fa-solid fa-right-from-bracket text-sm"></i>
                        <p class="text-[11px] mt-1 opacity-90">Jam Pulang</p>
                        <p class="text-xs font-bold">{{ $jamPulang }}</p>
                    </div>
                </section>

                <section class="attendance-info-card bg-white/85 backdrop-blur rounded-2xl border border-white/70 shadow-sm p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-xs text-slate-500">Status Hari Ini</p>
                            <p class="font-bold text-slate-800">{{ auth()->user()->name }}</p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold {{ $sudahPulang ? 'bg-blue-100 text-blue-700' : 'bg-amber-100 text-amber-700' }}">
                            {{ $jenisAbsen }}
                        </span>
                    </div>
                    <div class="attendance-status-grid grid grid-cols-2 gap-3 text-sm text-slate-600">
                        <div>
                            <p class="text-xs text-slate-400">User berada</p>
                            <p id="officeDistanceStatus" class="font-semibold text-slate-800">Menunggu GPS</p>
                        </div>
                        <div>
                            <p class="text-xs text-slate-400">Sampel wajah</p>
                            <p id="sampleStatus" class="font-semibold text-slate-800">0/3 terekam</p>
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
                    <p id="status" class="attendance-status-text text-sm text-slate-500">
                        {{ $sudahPulang ? 'Absensi hari ini sudah selesai.' : 'Klik Masuk/Verifikasi untuk menyalakan kamera dan GPS.' }}
                    </p>
                </section>

                <section class="bg-white/85 backdrop-blur rounded-2xl border border-white/70 shadow-sm p-4">
                    <h2 class="font-bold text-slate-800">Verifikasi Kehadiran</h2>
                    <p class="mt-2 text-sm text-slate-600">Ikuti instruksi singkat di bawah ini. Sistem akan memastikan wajah terbaca jelas dan Anda hadir langsung sebelum absensi dikirim.</p>
                    <div class="mt-3 rounded-xl bg-slate-50 border border-slate-200 p-3">
                        <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Instruksi Saat Ini</p>
                        <p id="challengeInstruction" class="mt-1 text-sm font-semibold text-slate-800">Tekan Masuk untuk memulai verifikasi.</p>
                        <p id="challengeProgress" class="mt-2 text-xs text-slate-500">Langkah verifikasi: 0/2</p>
                    </div>
                </section>

                <section id="iosSafariAttendanceHandoff" class="hidden bg-blue-50 border border-blue-200 text-blue-900 rounded-2xl p-4 text-sm shadow-sm">
                    <p class="font-bold">Kamera live iPhone PWA kurang stabil</p>
                    <p class="mt-1 text-xs leading-relaxed">Lanjutkan verifikasi di Safari. Setelah absen berhasil, buka kembali aplikasi Presensi; dashboard akan diperbarui otomatis.</p>
                    <div class="mt-3 grid grid-cols-1 gap-2 sm:grid-cols-2">
                        <a id="openSafariAttendance" href="{{ route('absen.page', ['handoff' => 'safari'], false) }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-2 text-xs font-bold text-white">
                            <i class="fa-brands fa-safari mr-2"></i>BUKA DI SAFARI
                        </a>
                        <button type="button" id="copySafariAttendance" class="rounded-xl border border-blue-200 bg-white px-4 py-2 text-xs font-bold text-blue-700">SALIN LINK</button>
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
                    <section class="bg-blue-50 border border-blue-200 text-blue-900 rounded-2xl p-4 text-sm shadow-sm">
                        Anda memiliki izin yang telah disetujui hari ini: {{ ucfirst($approvedLeave->jenis_izin) }}. Absensi dilewati otomatis.
                    </section>
                @elseif(empty($canAttend))
                    <section class="bg-blue-50 border border-blue-200 text-blue-900 rounded-2xl p-4 text-sm shadow-sm">
                        Shift kamu sudah dijadwalkan ({{ $shiftLabel }} {{ $jadwalMasuk }} - {{ $jadwalPulang }}), tapi belum masuk jam absensi.
                    </section>
                @endif

            </div>
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
                <a href="{{ route('absen.page') }}" class="w-14 h-14 -mt-8 bg-red-600 text-white rounded-full flex items-center justify-center border-4 border-white shadow-lg shadow-red-600/20">
                    @include('user.partials.face-id-icon', ['class' => 'w-7 h-7'])
                </a>
                <a href="{{ route('user.shifts.index') }}" class="text-gray-500 text-center text-xs">
                    <i class="fa-solid fa-calendar-days text-lg"></i>
                    <p>Jadwal</p>
                </a>
                <a href="{{ route('profile.index') }}" class="text-gray-500 text-center text-xs">
                    <i class="fa-solid fa-id-card text-lg"></i>
                    <p>Biodata</p>
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
const attendanceStatusUrl = "{{ route('absen.status', [], false) }}";
const safariAttendanceUrl = "{{ route('absen.page', ['handoff' => 'safari'], false) }}";
const attendanceForm = document.getElementById('attendanceForm');
const csrfToken = attendanceForm?.querySelector('input[name="_token"]')?.value || '{{ csrf_token() }}';
const blinkVerifiedInput = document.getElementById('blinkVerified');
const video = document.getElementById('video');
const startVerificationButton = document.getElementById('startVerification');
const submitAttendanceButton = document.getElementById('submitAttendance');
const statusText = document.getElementById('status');
const gpsStatus = document.getElementById('gpsStatus');
const officeDistanceStatus = document.getElementById('officeDistanceStatus');
const sampleStatus = document.getElementById('sampleStatus');
const faceStatus = document.getElementById('faceStatus');
const blinkStatus = document.getElementById('blinkStatus');
const challengeInstruction = document.getElementById('challengeInstruction');
const challengeProgress = document.getElementById('challengeProgress');
const attendanceHeadGuide = document.getElementById('attendanceHeadGuide');
const attendanceHeadFrame = document.getElementById('attendanceHeadFrame');
const attendanceScanTrack = document.getElementById('attendanceScanTrack');
const cameraOverlay = document.getElementById('cameraOverlay');
const attendanceMapElement = document.getElementById('attendanceMap');
const iosSafariAttendanceHandoff = document.getElementById('iosSafariAttendanceHandoff');
const openSafariAttendanceLink = document.getElementById('openSafariAttendance');
const copySafariAttendanceButton = document.getElementById('copySafariAttendance');
const officeLatitude = Number(attendanceMapElement?.dataset.officeLat ?? 0);
const officeLongitude = Number(attendanceMapElement?.dataset.officeLng ?? 0);
const officeRadius = Number(attendanceMapElement?.dataset.officeRadius ?? 0);
let attendanceHandoffStatusChecking = false;

function isIosDevice() {
    return /iPad|iPhone|iPod/.test(navigator.userAgent)
        || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
}

function isStandalonePwa() {
    return window.matchMedia('(display-mode: standalone)').matches
        || window.navigator.standalone === true;
}

function setupAttendanceSafariHandoff() {
    if (!iosSafariAttendanceHandoff || !isIosDevice() || !isStandalonePwa()) return;

    iosSafariAttendanceHandoff.classList.remove('hidden');

    if (openSafariAttendanceLink) {
        openSafariAttendanceLink.href = safariAttendanceUrl;
    }

    copySafariAttendanceButton?.addEventListener('click', async () => {
        const absoluteUrl = new URL(safariAttendanceUrl, window.location.origin).href;

        try {
            await navigator.clipboard.writeText(absoluteUrl);
            updateStatus('Link Safari disalin. Tempel di Safari jika tombol buka tidak berpindah.', false);
        } catch (error) {
            updateStatus('Buka link ini di Safari: ' + absoluteUrl, false);
        }
    });
}

async function checkAttendanceHandoffStatus() {
    if (!isIosDevice() || !isStandalonePwa() || attendanceHandoffStatusChecking) return;

    attendanceHandoffStatusChecking = true;

    try {
        const response = await fetch(attendanceStatusUrl, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            cache: 'no-store',
        });

        if (!response.ok) return;

        const data = await response.json();

        if (data.has_attendance && data.redirect) {
            window.location.href = data.redirect;
        }
    } catch (error) {
        // Dicek lagi saat PWA aktif kembali.
    } finally {
        attendanceHandoffStatusChecking = false;
    }
}

function updateClock() {
    const now = new Date();
    const clock = document.getElementById('clock');
    if (clock) clock.innerText = now.toLocaleTimeString('id-ID');
}
setInterval(updateClock, 1000);
updateClock();

function updateCameraOverlayCopy() {
    if (!cameraOverlay) return;

    const isMobile = window.matchMedia('(max-width: 767px)').matches;
    cameraOverlay.textContent = isMobile
        ? (cameraOverlay.dataset.mobileText || cameraOverlay.textContent)
        : (cameraOverlay.dataset.desktopText || cameraOverlay.textContent);
}

updateCameraOverlayCopy();
window.addEventListener('resize', updateCameraOverlayCopy);

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

    attendanceMap.fitBounds(officeCircle.getBounds(), {
        maxZoom: 17,
        padding: [18, 18],
    });
}

function updateAttendanceMap(latitude, longitude) {
    if (!attendanceMap) return;

    const latLng = [latitude, longitude];

    if (!userMarker) {
        userMarker = L.marker(latLng, {
            title: 'User berada',
        }).addTo(attendanceMap);
        userMarker.bindTooltip('User berada', {
            direction: 'top',
            offset: [0, -12],
            opacity: 0.95,
        });
    }

    userMarker.setLatLng(latLng);
    attendanceMap.setView(latLng, 17);
}

function calculateOfficeDistanceMeters(latitude, longitude) {
    const earthRadiusMeters = 6371000;
    const toRadians = value => value * Math.PI / 180;
    const latDelta = toRadians(latitude - officeLatitude);
    const lngDelta = toRadians(longitude - officeLongitude);
    const startLat = toRadians(officeLatitude);
    const endLat = toRadians(latitude);
    const a = Math.sin(latDelta / 2) ** 2
        + Math.cos(startLat) * Math.cos(endLat) * Math.sin(lngDelta / 2) ** 2;

    return earthRadiusMeters * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}

function formatDistance(meters) {
    if (meters >= 1000) return `${(meters / 1000).toFixed(2)} km`;
    return `${Math.round(meters)} meter`;
}

function updateOfficeDistance(latitude, longitude) {
    if (!officeDistanceStatus) return;

    const distance = calculateOfficeDistanceMeters(latitude, longitude);
    officeDistanceStatus.textContent = formatDistance(distance);
    officeDistanceStatus.className = distance <= officeRadius
        ? 'font-semibold text-blue-700'
        : 'font-semibold text-red-600';
}

function normalizeLocationSample(position) {
    const coords = position.coords ?? position;
    const timestampMs = typeof position.timestamp === 'number' ? position.timestamp : Date.now();

    return {
        latitude: Number(coords.latitude),
        longitude: Number(coords.longitude),
        accuracy: Number(coords.accuracy ?? 9999),
        timestamp: new Date(timestampMs).toISOString(),
        age_seconds: Math.max(0, (Date.now() - timestampMs) / 1000),
    };
}

function appendLocationSample(position) {
    const sample = normalizeLocationSample(position);
    const lastSample = geolocationSamples[geolocationSamples.length - 1];

    if (lastSample
        && lastSample.timestamp === sample.timestamp
        && Math.abs(lastSample.latitude - sample.latitude) < 0.000001
        && Math.abs(lastSample.longitude - sample.longitude) < 0.000001) {
        return sample;
    }

    geolocationSamples.push(sample);
    if (geolocationSamples.length > MAX_LOCATION_SAMPLE_BUFFER) {
        geolocationSamples = geolocationSamples.slice(-MAX_LOCATION_SAMPLE_BUFFER);
    }

    return sample;
}

function hasEnoughLocationSamples() {
    if (!geolocation) return false;

    if (Number.isFinite(geolocation.accuracy) && geolocation.accuracy <= FAST_LOCATION_ACCURACY) {
        return geolocationSamples.length >= 1;
    }

    return geolocationSamples.length >= REQUIRED_LOCATION_SAMPLES;
}

function currentLocationAgeSeconds(timestamp) {
    const timestampMs = Date.parse(timestamp);
    if (Number.isNaN(timestampMs)) return 9999;
    return Math.max(0, (Date.now() - timestampMs) / 1000);
}

function updateGeolocation(position) {
    const sample = appendLocationSample(position);
    geolocation = {
        latitude: sample.latitude,
        longitude: sample.longitude,
        accuracy: sample.accuracy,
        timestamp: sample.timestamp,
    };
    gpsStatus.textContent = 'User berada';
    updateOfficeDistance(sample.latitude, sample.longitude);
    updateAttendanceMap(sample.latitude, sample.longitude);
}

initializeAttendanceMap();

let stream;
let modelsLoaded = false;
let geolocation = null;
let geolocationSamples = [];
let geolocationWatchId = null;
let geolocationReadyPromise = null;
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
let lastOpenEar = 0;
let baselineFaceWidth = null;
let livenessChallenge = [];
let currentChallengeIndex = 0;
let completedChallengeSteps = [];
let movementHoldFrames = 0;
const REQUIRED_SAMPLES = 3;
const REQUIRED_LOCATION_SAMPLES = 2;
const MAX_LOCATION_SAMPLE_BUFFER = 5;
const FAST_LOCATION_ACCURACY = 25;
const REQUIRED_LIVENESS_STEPS = 2;

const MIN_BRIGHTNESS = {{ (float) config('attendance.min_brightness', 30) }};
const MAX_BRIGHTNESS = {{ (float) config('attendance.max_brightness', 220) }};
const MIN_SHARPNESS = {{ (float) config('attendance.min_sharpness', 8) }};
const BLINK_OPEN_EAR = 0.27;
const BLINK_CLOSED_EAR = 0.26;
const BLINK_DROP_RATIO = 0.9;
const BLINK_MIN_DROP = 0.025;

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
        ? 'font-semibold text-blue-700'
        : 'font-semibold text-gray-800';
    renderChallenge();
}

function setCameraGuideReady(isReady) {
    attendanceHeadGuide?.classList.toggle('is-camera-ready', isReady);
    attendanceScanTrack?.classList.toggle('is-active', isReady);
}

function updateFrameIndicator(isValid) {
    attendanceHeadFrame?.classList.toggle('valid', isValid);
    attendanceHeadFrame?.classList.toggle('invalid', !isValid);
}

function humanizeStep(step) {
    const labels = {
        samples: 'Hadapkan wajah lurus ke kamera sampai sampel lengkap',
        blink: 'Kedipkan mata satu kali untuk verifikasi',
    };
    return labels[step] || step;
}

function renderChallenge() {
    const sampleCount = Math.min(descriptorSamples.length, REQUIRED_SAMPLES);
    const completedSteps = [
        sampleCount >= REQUIRED_SAMPLES,
        blinkVerified,
    ].filter(Boolean).length;

    sampleStatus.textContent = `${sampleCount}/${REQUIRED_SAMPLES} terekam`;
    challengeProgress.textContent = `Langkah verifikasi: ${completedSteps}/2`;

    if (sampleCount < REQUIRED_SAMPLES) {
        challengeInstruction.textContent = humanizeStep('samples');
        return;
    }

    if (!blinkVerified) {
        challengeInstruction.textContent = humanizeStep('blink');
        return;
    }

    challengeInstruction.textContent = 'Verifikasi selesai. Absensi sedang dikirim.';
}

async function loadModels() {
    if (modelsLoaded) return;
    updateStatus('Memuat model deteksi wajah...');
    await preloadModels();
}

async function requestChallenge() {
    sampleStatus.textContent = '0/3 terekam';
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

    if (ear >= BLINK_OPEN_EAR) {
        maxOpenEar = Math.max(maxOpenEar, ear);
        eyesWereOpen = true;
        lastOpenEar = ear;
        return false;
    }

    if (!eyesWereOpen) return false;

    const openReference = Math.max(maxOpenEar, lastOpenEar);
    return ear <= BLINK_CLOSED_EAR
        || (openReference > 0 && ear <= openReference * BLINK_DROP_RATIO)
        || (openReference > 0 && openReference - ear >= BLINK_MIN_DROP);
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
        && quality.sharpness >= MIN_SHARPNESS;
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
    setCameraGuideReady(false);
    updateFrameIndicator(false);
}

function stopGeolocationWatch() {
    if (geolocationWatchId !== null) {
        navigator.geolocation.clearWatch(geolocationWatchId);
        geolocationWatchId = null;
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
    setCameraGuideReady(true);
}

function trackChallenge() {
    stopTracking();

    // ✅ Interval 75ms supaya kedipan singkat lebih mudah tertangkap.
    const DETECTION_INTERVAL = 75;

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
                updateFrameIndicator(false);
                updateStatus(
                    descriptorSamples.length >= REQUIRED_SAMPLES
                        ? 'Sampel wajah sudah cukup. Hadapkan wajah lalu kedipkan mata sekali.'
                        : 'Dekatkan wajah ke kamera.',
                    true
                );
                return;
            }

            const quality = getFrameQuality(detection.detection.box);
            const blinkDetected = detectBlink(detection.landmarks);
            const isClear = isFrameQualityGood(quality);

            faceStatus.textContent = 'Wajah terdeteksi';
            updateFrameIndicator(isClear || descriptorSamples.length >= REQUIRED_SAMPLES);

            if (blinkDetected && !blinkVerified) {
                setBlinkVerified(true);
                updateStatus('Kedipan berhasil diverifikasi.');
                maybeCompleteVerification();
                return;
            }

            if (descriptorSamples.length < REQUIRED_SAMPLES && !isClear) {
                updateStatus(`Pencahayaan kurang. Cahaya: ${Math.round(quality.brightness)}, ketajaman: ${Math.round(quality.sharpness)}.`, true);
                return;
            }

            if (!blinkVerified && lastEar !== null) {
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
                updateStatus(blinkVerified ? 'Sampel wajah sudah lengkap. Menyiapkan absensi...' : 'Wajah sudah jelas. Kedipkan mata satu kali.');
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
    stopGeolocationWatch();

    const waitForStableLocation = () => new Promise((resolve, reject) => {
        const startedAt = Date.now();

        const tick = () => {
            if (hasEnoughLocationSamples()) {
                resolve();
                return;
            }

            if (Date.now() - startedAt >= 15000) {
                if (geolocation) {
                    resolve();
                    return;
                }

                reject(new Error('GPS belum stabil. Tunggu beberapa detik lalu coba lagi.'));
                return;
            }

            setTimeout(tick, 250);
        };

        tick();
    });

    const currentLocation = await new Promise((resolve, reject) => {
        navigator.geolocation.getCurrentPosition(
            pos => resolve(pos),
            () => reject(new Error('Izin lokasi ditolak atau GPS tidak tersedia.')),
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
        );
    });
    updateGeolocation(currentLocation);
    updateStatus('GPS didapatkan. Lanjutkan verifikasi wajah.');

    geolocationWatchId = navigator.geolocation.watchPosition(
        pos => updateGeolocation(pos),
        () => {},
        { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
    );

    await waitForStableLocation();
}

async function startVerification() {
    try {
        updateStatus('Menyiapkan kamera, lokasi, dan verifikasi kehadiran...');
        isSubmitting = false;
        submitAttendanceButton.disabled = true;
        verificationReady = false;
        latestDescriptor = null;
        latestSnapshot = null;
        latestQualityMetrics = null;
        descriptorSamples = [];
        qualitySamples = [];
        geolocation = null;
        geolocationSamples = [];
        geolocationReadyPromise = null;
        setBlinkVerified(false);
        eyesWereOpen = false;
        lastEar = null;
        maxOpenEar = 0;
        lastOpenEar = 0;
        lastCaptureAt = 0;
        sampleStatus.textContent = '0/3 terekam';
        faceStatus.textContent = 'Menunggu scan';
        renderChallenge();
        stopTracking();
        stopStream();

        await startCamera();
        updateStatus('Mempersiapkan sistem...');
        geolocationReadyPromise = requestGeolocation().catch((error) => {
            updateStatus(error.message || 'GPS gagal disiapkan.', true);
            throw error;
        });
        await loadModels();

        // startCamera() sudah menyalakan kamera dan menunggu video siap.

        trackChallenge();
        updateStatus('Hadapkan wajah ke kamera dan kedipkan mata. GPS sedang dipastikan di background.');
    } catch (error) {
        setCameraGuideReady(false);
        updateFrameIndicator(false);
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
    if (!verificationReady || !latestDescriptor || !latestSnapshot) {
        updateStatus('Verifikasi belum lengkap.', true);
        return;
    }

    if (!geolocation && geolocationReadyPromise) {
        try {
            await geolocationReadyPromise;
        } catch (error) {
            updateStatus(error.message || 'GPS gagal disiapkan.', true);
            return;
        }
    }

    if (!geolocation) {
        updateStatus('Lokasi belum tersedia. Aktifkan GPS lalu coba lagi.', true);
        return;
    }

    if (!hasEnoughLocationSamples()) {
        updateStatus('GPS sedang dipastikan. Coba kirim lagi dalam 1-2 detik.', true);
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
                descriptor_samples: descriptorSamples,
                quality_metrics: latestQualityMetrics,
                lat: geolocation.latitude,
                lng: geolocation.longitude,
                location_accuracy: geolocation.accuracy,
                location_timestamp: geolocation.timestamp,
                location_age_seconds: currentLocationAgeSeconds(geolocation.timestamp),
                location_samples: geolocationSamples.slice(-MAX_LOCATION_SAMPLE_BUFFER).map(sample => ({
                    latitude: sample.latitude,
                    longitude: sample.longitude,
                    accuracy: sample.accuracy,
                    timestamp: sample.timestamp,
                    age_seconds: currentLocationAgeSeconds(sample.timestamp),
                })),
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
        stopGeolocationWatch();
        window.location.href = result.redirect || dashboardUrl;
    } catch (error) {
        isSubmitting = false;
        submitAttendanceButton.disabled = false;
        updateStatus(error.message || 'Absensi gagal dikirim.', true);
    }
}

startVerificationButton.addEventListener('click', startVerification);
submitAttendanceButton.addEventListener('click', submitAttendance);
setupAttendanceSafariHandoff();
checkAttendanceHandoffStatus();
window.addEventListener('focus', checkAttendanceHandoffStatus);
window.addEventListener('beforeunload', stopStream);
window.addEventListener('beforeunload', stopGeolocationWatch);
document.addEventListener('visibilitychange', () => {
    if (!document.hidden) {
        checkAttendanceHandoffStatus();
    }
});
</script>
@endsection
