@extends('layouts.admin')

@section('title', 'Dashboard Presensi')

@section('content')
@php
    $badgeClass = [
        'hadir'        => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100',
        'telat'        => 'bg-amber-50 text-amber-700 ring-1 ring-amber-100',
        'terlambat'    => 'bg-amber-50 text-amber-700 ring-1 ring-amber-100',
        'izin'         => 'bg-sky-50 text-sky-700 ring-1 ring-sky-100',
        'normal'       => 'bg-blue-50 text-blue-700 ring-1 ring-blue-100',
        'pulang_cepat' => 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100',
    ];

    $cards = [
        ['label' => 'Total Pegawai', 'value' => $totalUser, 'icon' => 'fa-solid fa-users'],
        ['label' => 'Presensi', 'value' => $totalPresensi, 'icon' => 'fa-solid fa-clipboard-list'],
        ['label' => 'Hadir', 'value' => $hadir, 'icon' => 'fa-solid fa-circle-check'],
        ['label' => 'Telat', 'value' => $telat, 'icon' => 'fa-solid fa-clock'],
        ['label' => 'Pulang Cepat', 'value' => $pulangCepat, 'icon' => 'fa-solid fa-person-running'],
        ['label' => 'Izin', 'value' => $izin, 'icon' => 'fa-solid fa-file-circle-check'],
        ['label' => 'Alpha', 'value' => $alpha, 'icon' => 'fa-solid fa-user-xmark'],
        ['label' => 'Shift Hari Ini', 'value' => $totalShiftHariIni, 'icon' => 'fa-solid fa-calendar-day'],
        ['label' => 'User Masuk', 'value' => $userMasukHariIni, 'icon' => 'fa-solid fa-user-check'],
        ['label' => 'Swap Pending', 'value' => $swapPending, 'icon' => 'fa-solid fa-right-left'],
    ];
@endphp

@once
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
@endonce

<div class="admin-dashboard space-y-6 pb-5">
    <section class="rounded-[1.35rem] border border-blue-100 bg-white/90 p-5 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-semibold text-blue-600">Dashboard Admin</p>
                <h1 class="mt-1 text-2xl font-extrabold text-gray-950">Ringkasan Presensi</h1>
                <p class="mt-1 text-sm text-gray-500">{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}</p>
            </div>
            <form method="GET" action="{{ route('admin.dashboard') }}" data-auto-filter class="flex flex-col gap-3 sm:flex-row sm:items-center">
                <input type="hidden" name="chart_period" value="{{ $chartPeriod }}">
                <label class="relative block">
                    <i class="fa-solid fa-calendar-days absolute left-4 top-1/2 -translate-y-1/2 text-blue-500"></i>
                    <input
                        type="date"
                        name="tanggal"
                        value="{{ $tanggal }}"
                        class="h-12 rounded-2xl border border-blue-100 bg-blue-50/60 pl-11 pr-4 text-sm font-semibold text-gray-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10"
                    >
                </label>
            </form>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-5 xl:grid-cols-[1fr_22rem]">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-5">
            @foreach($cards as $card)
                <article class="group relative overflow-hidden rounded-[1.35rem] border border-blue-100 bg-white p-5 shadow-sm transition hover:-translate-y-0.5 hover:shadow-lg hover:shadow-blue-700/10">
                    <div class="relative flex items-center gap-4">
                        <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-blue-700 to-blue-500 text-lg text-white shadow-lg shadow-blue-700/20">
                            <i class="{{ $card['icon'] }}"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-bold text-gray-500">{{ $card['label'] }}</p>
                            <p class="mt-2 text-3xl font-black leading-none text-gray-950">{{ $card['value'] }}</p>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="grid gap-4">
            <article class="rounded-[1.35rem] bg-gradient-to-br from-blue-800 via-blue-700 to-cyan-600 p-6 text-white shadow-lg shadow-blue-800/20">
                <div class="flex items-start gap-4">
                    <div class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-white text-blue-700">
                        <i class="fa-solid fa-bell text-lg"></i>
                    </div>
                    <div>
                        <p class="text-base font-extrabold">Notifikasi Admin</p>
                        <p class="mt-2 text-sm leading-6 text-blue-50">
                            <strong>{{ $telat }}</strong> pegawai telat, <strong>{{ $alpha }}</strong> pegawai belum hadir, dan <strong>{{ $izin }}</strong> pegawai izin hari ini.
                        </p>
                    </div>
                </div>
            </article>

            <article class="rounded-[1.35rem] border border-blue-100 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-50 text-blue-700">
                        <i class="fa-solid fa-building text-lg"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-500">Total Unit Kerja/Bagian</p>
                        <p class="mt-1 text-3xl font-black text-gray-950">{{ $units }}</p>
                    </div>
                </div>
            </article>

            <article class="rounded-[1.35rem] border border-blue-100 bg-white p-5 shadow-sm">
                <div class="flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-cyan-50 text-cyan-700">
                        <i class="fa-solid fa-bullhorn text-lg"></i>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-gray-500">Pengumuman</p>
                        <p class="mt-1 text-3xl font-black text-gray-950">{{ $announcements }}</p>
                    </div>
                </div>
            </article>
        </div>
    </section>

    <section class="grid grid-cols-1 gap-5 xl:grid-cols-[1fr_22rem]">
        <article class="rounded-[1.35rem] border border-blue-100 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 xl:flex-row xl:items-center xl:justify-between">
                <div>
                    <p class="text-sm font-semibold text-blue-600">Analitik</p>
                    <h2 class="text-xl font-extrabold text-gray-950">{{ $chartTitle }}</h2>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <form method="GET" action="{{ route('admin.dashboard') }}" data-auto-filter>
                        <input type="hidden" name="tanggal" value="{{ $tanggal }}">
                        <label class="relative block">
                            <select
                                name="chart_period"
                                class="h-10 appearance-none rounded-md border border-blue-100 bg-blue-50/70 pl-3 pr-9 text-sm font-semibold text-blue-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10"
                            >
                                <option value="7_days" @selected($chartPeriod === '7_days')>7 Hari</option>
                                <option value="1_month" @selected($chartPeriod === '1_month')>1 Bulan</option>
                                <option value="1_year" @selected($chartPeriod === '1_year')>1 Tahun</option>
                            </select>
                            <i class="fa-solid fa-chevron-down pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-blue-500"></i>
                        </label>
                    </form>
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="inline-flex items-center gap-2 text-xs font-semibold text-gray-500"><span class="h-3 w-3 rounded bg-sky-400"></span>Hadir</span>
                        <span class="inline-flex items-center gap-2 text-xs font-semibold text-gray-500"><span class="h-3 w-3 rounded bg-orange-500"></span>Telat</span>
                        <span class="inline-flex items-center gap-2 text-xs font-semibold text-gray-500"><span class="h-3 w-3 rounded bg-yellow-400"></span>Izin</span>
                    </div>
                </div>
            </div>
            <div class="relative mt-6 h-[260px]">
                <canvas id="hadirChart"></canvas>
            </div>
        </article>

        <article class="rounded-[1.35rem] bg-blue-950 p-6 text-white shadow-lg shadow-blue-950/20">
            <div class="space-y-5">
                <p class="flex items-center gap-2 text-sm font-bold uppercase tracking-wide text-blue-100">
                    <i class="fa-solid fa-location-dot text-cyan-300"></i> Lokasi Presensi
                </p>
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-blue-300">Koordinat GPS</p>
                    <p class="mt-1 text-lg font-extrabold">
                        {{ $workSetting?->office_latitude ?? config('attendance.office_latitude') }},
                        {{ $workSetting?->office_longitude ?? config('attendance.office_longitude') }}
                    </p>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-blue-300">Radius</p>
                    <p class="mt-1 text-lg font-extrabold">
                        {{ $workSetting?->radius_meters ?? config('attendance.radius_meters', 100) }}<span class="text-sm font-semibold text-blue-200"> Meter</span>
                    </p>
                </div>
                <div>
                    <p class="text-xs font-bold uppercase tracking-widest text-blue-300">Toleransi Shift</p>
                    <p class="mt-1 text-lg font-extrabold">
                        {{ $workSetting?->checkin_early_minutes ?? \App\Models\WorkSetting::DEFAULT_CHECKIN_EARLY_MINUTES }} /
                        {{ $workSetting?->checkout_late_minutes ?? \App\Models\WorkSetting::DEFAULT_CHECKOUT_LATE_MINUTES }}<span class="text-sm font-semibold text-blue-200"> Menit</span>
                    </p>
                </div>
            </div>
            <a href="{{ route('admin.settings.work.edit') }}"
               class="mt-7 inline-flex w-full items-center justify-center gap-2 rounded-2xl bg-white px-4 py-3 text-sm font-extrabold text-blue-800 no-underline transition hover:bg-blue-50">
                <i class="fa-solid fa-gear"></i> Atur Lokasi
            </a>
        </article>
    </section>

    <section class="overflow-hidden rounded-[1.35rem] border border-blue-100 bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-blue-50 p-5 md:flex-row md:items-center md:justify-between">
            <div>
                <p class="text-sm font-semibold text-blue-600">Riwayat Hari Ini</p>
                <h2 class="text-xl font-extrabold text-gray-950">Data Presensi</h2>
            </div>
            <p class="text-sm font-semibold text-gray-500">{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}</p>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-blue-50/60">
                    <tr class="border-y border-blue-100 text-left text-[11px] font-black uppercase tracking-[0.14em] text-blue-800">
                        <th class="px-5 py-4">Nama</th>
                        <th class="px-5 py-4">Tanggal</th>
                        <th class="px-5 py-4">Masuk</th>
                        <th class="px-5 py-4">Pulang</th>
                        <th class="px-5 py-4">Status Masuk</th>
                        <th class="px-5 py-4">Status Pulang</th>
                        <th class="px-5 py-4">Foto</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-blue-50 bg-white">
                    @forelse($presensis as $presensi)
                        <tr class="transition hover:bg-blue-50/40">
                            <td class="px-5 py-4">
                                <div class="font-bold text-gray-700">{{ $presensi->user->name ?? 'User dihapus' }}</div>
                            </td>
                            <td class="px-5 py-4 font-semibold text-gray-600">{{ optional($presensi->tanggal)->format('d/m/Y') }}</td>
                            <td class="px-5 py-4 font-semibold text-gray-600">{{ $presensi->jam_masuk ? $presensi->jam_masuk->format('H:i') : '-' }}</td>
                            <td class="px-5 py-4 font-semibold text-gray-600">{{ $presensi->jam_keluar ? $presensi->jam_keluar->format('H:i') : '-' }}</td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-extrabold {{ $badgeClass[$presensi->status] ?? 'bg-gray-100 text-gray-600 ring-1 ring-gray-200' }}">
                                    {{ $presensi->status ? str_replace('_', ' ', ucfirst($presensi->status)) : '-' }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full px-3 py-1 text-xs font-extrabold {{ $badgeClass[$presensi->status_pulang] ?? 'bg-gray-100 text-gray-600 ring-1 ring-gray-200' }}">
                                    {{ $presensi->status_pulang ? str_replace('_', ' ', ucfirst($presensi->status_pulang)) : '-' }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-2">
                                    @if($presensi->foto_masuk)
                                        <a href="{{ asset('storage/' . $presensi->foto_masuk) }}" target="_blank"
                                           class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1.5 text-xs font-bold text-blue-700 transition hover:bg-blue-100">
                                            <i class="fa-solid fa-image text-[10px]"></i> Masuk
                                        </a>
                                    @endif
                                    @if($presensi->foto_keluar)
                                        <a href="{{ asset('storage/' . $presensi->foto_keluar) }}" target="_blank"
                                           class="inline-flex items-center gap-1.5 rounded-full bg-cyan-50 px-3 py-1.5 text-xs font-bold text-cyan-700 transition hover:bg-cyan-100">
                                            <i class="fa-solid fa-image text-[10px]"></i> Pulang
                                        </a>
                                    @endif
                                    @if(!$presensi->foto_masuk && !$presensi->foto_keluar)
                                        <span class="text-xs font-bold text-gray-300">-</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-14 text-center text-sm font-semibold text-gray-400">
                                <i class="fa-solid fa-inbox mb-3 block text-3xl text-blue-100"></i>
                                Belum ada data presensi pada tanggal ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
(function () {
    const labels = @json(collect($chart)->pluck('label'));
    const hadir  = @json(collect($chart)->pluck('hadir'));
    const telat  = @json(collect($chart)->pluck('telat'));
    const izin   = @json(collect($chart)->pluck('izin'));

    const chart = document.getElementById('hadirChart');
    if (!chart) return;

    const ctx = chart.getContext('2d');

    function makeGradient(colorTop, colorBottom) {
        const g = ctx.createLinearGradient(0, 0, 0, 260);
        g.addColorStop(0, colorTop);
        g.addColorStop(1, colorBottom);
        return g;
    }

    new Chart(ctx, {
        type: 'line',
        data: {
            labels,
            datasets: [
                {
                    label: 'Hadir',
                    data: hadir,
                    borderColor: '#38bdf8',
                    backgroundColor: makeGradient('rgba(56,189,248,.24)', 'rgba(56,189,248,.02)'),
                    borderWidth: 3,
                    fill: true,
                    tension: 0.42,
                    pointBackgroundColor: '#38bdf8',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                },
                {
                    label: 'Telat',
                    data: telat,
                    borderColor: '#f97316',
                    backgroundColor: makeGradient('rgba(249,115,22,.24)', 'rgba(249,115,22,.02)'),
                    borderWidth: 3,
                    fill: true,
                    tension: 0.42,
                    pointBackgroundColor: '#f97316',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                },
                {
                    label: 'Izin',
                    data: izin,
                    borderColor: '#facc15',
                    backgroundColor: makeGradient('rgba(250,204,21,.24)', 'rgba(250,204,21,.02)'),
                    borderWidth: 3,
                    fill: true,
                    tension: 0.42,
                    pointBackgroundColor: '#facc15',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                },
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: '#0f172a',
                    titleColor: '#bfdbfe',
                    bodyColor: '#f8fafc',
                    padding: 12,
                    cornerRadius: 14,
                    boxPadding: 5,
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: '#64748b', font: { size: 11, weight: '700' } },
                    border: { display: false },
                },
                y: {
                    beginAtZero: true,
                    grid: { color: '#eaf3ff' },
                    ticks: { color: '#64748b', font: { size: 11, weight: '600' }, stepSize: 1 },
                    border: { display: false },
                }
            }
        }
    });
})();
</script>
@endsection
