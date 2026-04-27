@extends('layouts.admin')

@section('title', 'Dashboard Presensi')

@section('content')
@php
    $badgeClass = [
        'hadir'        => 'bg-green-100 text-green-700',
        'telat'        => 'bg-yellow-100 text-yellow-700',
        'terlambat'    => 'bg-yellow-100 text-yellow-700',
        'izin'         => 'bg-sky-100 text-sky-700',
        'normal'       => 'bg-blue-100 text-blue-700',
        'pulang_cepat' => 'bg-red-100 text-red-700',
    ];

    $cards = [
        ['label'=>'Total Pegawai',   'value'=>$totalUser,    'icon'=>'fa-solid fa-users',           'from'=>'#3b82f6','to'=>'#2563eb'],
        ['label'=>'Presensi',     'value'=>$totalPresensi,'icon'=>'fa-solid fa-clipboard-list',   'from'=>'#10b981','to'=>'#059669'],
        ['label'=>'Hadir',        'value'=>$hadir,        'icon'=>'fa-solid fa-circle-check',     'from'=>'#22c55e','to'=>'#16a34a'],
        ['label'=>'Telat',        'value'=>$telat,        'icon'=>'fa-solid fa-clock',            'from'=>'#f59e0b','to'=>'#d97706'],
        ['label'=>'Pulang Cepat', 'value'=>$pulangCepat,  'icon'=>'fa-solid fa-person-running',   'from'=>'#ef4444','to'=>'#dc2626'],
        ['label'=>'Izin',         'value'=>$izin,         'icon'=>'fa-solid fa-file-circle-check','from'=>'#38bdf8','to'=>'#0284c7'],
        ['label'=>'Alpha',        'value'=>$alpha,        'icon'=>'fa-solid fa-user-xmark',       'from'=>'#94a3b8','to'=>'#475569'],
    ];
@endphp

{{-- Font Awesome CDN (skip jika sudah ada di layout) --}}
@once
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
@endonce

<div class="space-y-5 pb-5">

    {{-- ══════════════════════════════════════
         ROW 1 — 7 Stat Cards
    ══════════════════════════════════════ --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-4 gap-4">
        @foreach($cards as $card)
        <div class="relative overflow-hidden rounded-md p-3 text-white shadow flex flex-row gap-3 bg-white border border-gray-200">
            {{-- Icon --}}
            <div class="w-16 h-16 rounded-md flex items-center justify-center text-lg" style="background:linear-gradient(135deg,{{ $card['to']}})">
                <i class="{{ $card['icon'] }} text-3xl"></i>
            </div>
            <div class="flex flex-col gap-5 justify-center flex-grow">
                <p class="text-sm font-medium capitalize text-gray-900 tracking-[.2px]" style="opacity:.82">{{ $card['label'] }}</p>
                <p class="text-md font-extrabold leading-none text-gray-700">{{ $card['value'] }}</p>
            </div>
        </div>
        @endforeach
    </div>

    {{-- ══════════════════════════════════════
         ROW 2 — Unit / Pengumuman / Notifikasi
    ══════════════════════════════════════ --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

        <div class="rounded-md shadow flex items-center gap-4 p-3 bg-sky-600">
            <div class="w-12 h-12 rounded-md flex items-center justify-center text-sky-600 bg-white shrink-0 text-xl">
                <i class="fa-solid fa-building"></i>
            </div>
            <div>
                <p class="text-md capitalize uppercase tracking-[.2px] text-white">Total Unit</p>
                <p class="text-lg font-bold text-white">{{ $units }}</p>
            </div>
        </div>

        <div class="rounded-md shadow flex items-center gap-4 p-3 bg-emerald-600">
            <div class="w-12 h-12 rounded-md flex items-center justify-center text-emerald-600 bg-white shrink-0 text-xl">
                <i class="fa-solid fa-bullhorn"></i>
            </div>
            <div>
                <p class="text-md capitalize uppercase tracking-[.2px] text-white">Pengumuman</p>
                <p class="text-lg font-bold text-white">{{ $announcements }}</p>
            </div>
        </div>

        <div class="rounded-md shadow flex items-center gap-4 p-3 bg-amber-600">
            <div class="w-12 h-12 rounded-md flex items-center justify-center text-amber-600 bg-white shrink-0 text-base mt-0.5">
                <i class="fa-solid fa-bell"></i>
            </div>
            <div>
                <p class="text-md capitalize uppercase tracking-[.2px] text-white">Notifikasi Admin</p>
                <p class="text-xs text-white mt-1 leading-relaxed">
                    <strong>{{ $telat }}</strong> pegawai telat &nbsp;·&nbsp;
                    <strong>{{ $alpha }}</strong> pegawai belum hadir &nbsp;·&nbsp;
                    <strong>{{ $izin }}</strong> pegawai izin hari ini.
                </p>
            </div>
        </div>

    </div>

    {{-- ══════════════════════════════════════
         ROW 3 — Chart.js Smooth Area + Lokasi
    ══════════════════════════════════════ --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">

        {{-- Chart Card --}}
        <div class="lg:col-span-2 bg-white rounded-md shadow p-5 border border-gray-200">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-base font-bold text-gray-700 tracking-[.2px]">
                    <i class="fa-solid fa-chart-area mr-1.5 text-blue-500"></i> Grafik Kehadiran 7 Hari
                </h2>
                <div class="flex items-center gap-4">
                    <span class="flex items-center gap-1.5 text-xs text-gray-500">
                        <span class="inline-block w-3 h-3 rounded-sm bg-green-500"></span> Hadir
                    </span>
                    <span class="flex items-center gap-1.5 text-xs text-gray-500">
                        <span class="inline-block w-3 h-3 rounded-sm bg-yellow-400"></span> Telat
                    </span>
                    <span class="flex items-center gap-1.5 text-xs text-gray-500">
                        <span class="inline-block w-3 h-3 rounded-sm bg-sky-400"></span> Izin
                    </span>
                </div>
            </div>
            <div class="relative" style="height:220px">
                <canvas id="hadirChart"></canvas>
            </div>
        </div>

        {{-- Lokasi Card --}}
        <div class="rounded-md shadow p-5 text-white flex flex-col justify-between"
             style="background:linear-gradient(135deg,#1e3a8a,#2563eb)">
            <div class="space-y-4">
                <p class="text-sm  uppercase tracking-[.2px] flex items-center gap-2">
                    <i class="fa-solid fa-location-dot"></i> Lokasi & Jam Kerja
                </p>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest" style="opacity:.7">Koordinat GPS</p>
                    <p class="text-md font-bold mt-1">
                        {{ $workSetting?->office_latitude ?? config('attendance.office_latitude') }},
                        {{ $workSetting?->office_longitude ?? config('attendance.office_longitude') }}
                    </p>
                </div>
                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest" style="opacity:.7">Radius</p>
                    <p class="text-md font-bold mt-1">
                        {{ $workSetting?->radius_meters ?? config('attendance.radius_meters', 100) }}<span class="opacity-70 text-sm"> Meter</span>
                    </p>
                </div>
            </div>
            <a href="{{ route('admin.settings.work.edit') }}"
               class="flex items-center justify-center gap-2 rounded-md px-4 py-3 text-md font-bold text-white no-underline transition bg-white bg-opacity-30 hover:bg-opacity-40">
                <i class="fa-solid fa-gear"></i> Atur Lokasi</a>
        </div>

    </div>

    {{-- ══════════════════════════════════════
         ROW 4 — Tabel Presensi
    ══════════════════════════════════════ --}}
    <div class="bg-white rounded-md shadow overflow-hidden border border-gray-200">

        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 p-5 border-b border-gray-100">
            <div>
                <h2 class="text-base font-bold text-gray-700 tracking-[.2px]">
                    <i class="fa-solid fa-calendar-days mr-1.5 text-blue-500"></i> Data Presensi
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">
                    {{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}
                </p>
            </div>
            <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                <input type="date" name="tanggal" value="{{ $tanggal }}"
                       class="border border-gray-300 rounded-md px-3 py-2 text-sm text-gray-700
                              focus:outline-none focus:border-blue-400">
                <button type="submit"
                        class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-md text-sm font-semibold transition-colors flex items-center gap-2">
                    <i class="fa-solid fa-filter text-xs"></i> Filter
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50">
                    <tr class="border-y border-gray-200 text-left text-[11px] font-bold uppercase tracking-[0.14em] text-slate-500">
                        <th class="px-5 py-3.5">Nama</th>
                        <th class="px-5 py-3.5">Tanggal</th>
                        <th class="px-5 py-3.5">Masuk</th>
                        <th class="px-5 py-3.5">Pulang</th>
                        <th class="px-5 py-3.5">Status Masuk</th>
                        <th class="px-5 py-3.5">Status Pulang</th>
                        <th class="px-5 py-3.5">Foto</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 bg-white">
                    @forelse($presensis as $presensi)
                        <tr class="transition-colors duration-150 hover:bg-gray-50/80">
                            <td class="px-5 py-4">
                                <div class="font-medium text-gray-600">
                                    {{ $presensi->user->name ?? 'User dihapus' }}
                                </div>
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                {{ optional($presensi->tanggal)->format('d/m/Y') }}
                            </td>
                            <td class="px-5 py-4 font-medium text-gray-600">
                                {{ $presensi->jam_masuk ? $presensi->jam_masuk->format('H:i') : '-' }}
                            </td>
                            <td class="px-5 py-4 font-medium text-gray-600">
                                {{ $presensi->jam_keluar ? $presensi->jam_keluar->format('H:i') : '-' }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $badgeClass[$presensi->status] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ $presensi->status ? str_replace('_', ' ', ucfirst($presensi->status)) : '-' }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-bold {{ $badgeClass[$presensi->status_pulang] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ $presensi->status_pulang ? str_replace('_', ' ', ucfirst($presensi->status_pulang)) : '-' }}
                                </span>
                            </td>
                           
                            <td class="px-5 py-4">
                                <div class="flex flex-wrap gap-2">
                                    @if($presensi->foto_masuk)
                                        <a href="{{ asset('storage/' . $presensi->foto_masuk) }}" target="_blank"
                                           class="inline-flex items-center gap-1.5 rounded-full bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-700 transition hover:bg-blue-100">
                                            <i class="fa-solid fa-image text-[10px]"></i> Masuk
                                        </a>
                                    @endif
                                    @if($presensi->foto_keluar)
                                        <a href="{{ asset('storage/' . $presensi->foto_keluar) }}" target="_blank"
                                           class="inline-flex items-center gap-1.5 rounded-full bg-slate-100 px-3 py-1.5 text-xs font-semibold text-slate-700 transition hover:bg-slate-200">
                                            <i class="fa-solid fa-image text-[10px]"></i> Pulang
                                        </a>
                                    @endif
                                    @if(!$presensi->foto_masuk && !$presensi->foto_keluar)
                                        <span class="text-xs text-slate-300">-</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-14 text-center text-sm text-slate-400">
                                <i class="fa-solid fa-inbox mb-3 block text-3xl text-slate-200"></i>
                                Belum ada data presensi pada tanggal ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

{{-- Chart.js --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
(function () {
    const labels = @json(collect($chart)->pluck('label'));
    const hadir  = @json(collect($chart)->pluck('hadir'));
    const telat  = @json(collect($chart)->pluck('telat'));
    const izin   = @json(collect($chart)->pluck('izin'));

    const ctx = document.getElementById('hadirChart').getContext('2d');

    function makeGradient(ctx, colorTop, colorBottom) {
        const g = ctx.createLinearGradient(0, 0, 0, 220);
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
                    borderColor: '#16a34a',
                    backgroundColor: makeGradient(ctx, 'rgba(22,163,74,.35)', 'rgba(22,163,74,.02)'),
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.45,
                    pointBackgroundColor: '#16a34a',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                },
                {
                    label: 'Telat',
                    data: telat,
                    borderColor: '#f59e0b',
                    backgroundColor: makeGradient(ctx, 'rgba(245,158,11,.28)', 'rgba(245,158,11,.02)'),
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.45,
                    pointBackgroundColor: '#f59e0b',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                },
                {
                    label: 'Izin',
                    data: izin,
                    borderColor: '#38bdf8',
                    backgroundColor: makeGradient(ctx, 'rgba(56,189,248,.25)', 'rgba(56,189,248,.02)'),
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.45,
                    pointBackgroundColor: '#38bdf8',
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
                    backgroundColor: '#1e293b',
                    titleColor: '#94a3b8',
                    bodyColor: '#f1f5f9',
                    padding: 10,
                    cornerRadius: 10,
                    boxPadding: 4,
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { color: '#94a3b8', font: { size: 11, weight: '600' } },
                    border: { display: false },
                },
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' },
                    ticks: { color: '#94a3b8', font: { size: 11 }, stepSize: 1 },
                    border: { display: false },
                }
            }
        }
    });
})();
</script>
@endsection
