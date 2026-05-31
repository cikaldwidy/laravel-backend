@extends('layouts.admin')

@section('title', 'Dashboard Presensi')

@section('content')
@php
    $selectedDate = \Carbon\Carbon::parse($tanggal);
    $totalStatus = max($hadir + $telat + $pulangCepat + $izin + $alpha, 1);
    $attendanceRate = $totalUser > 0 ? round(($userMasukHariIni / $totalUser) * 100) : 0;
    $lateRate = $totalPresensi > 0 ? round(($telat / $totalPresensi) * 100) : 0;
    $earlyLeaveRate = $totalPresensi > 0 ? round(($pulangCepat / $totalPresensi) * 100) : 0;
    $alphaRate = ($totalShiftHariIni + $izin) > 0 ? round(($alpha / max($totalShiftHariIni + $izin, 1)) * 100) : 0;
    $yearTarget = max((int) ($yearlyKpis['target'] ?? 0), 1);
    $yearPercent = fn ($value) => round(((int) $value / $yearTarget) * 100);

    $badgeClass = [
        'hadir'        => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100',
        'telat'        => 'bg-amber-50 text-amber-700 ring-1 ring-amber-100',
        'terlambat'    => 'bg-amber-50 text-amber-700 ring-1 ring-amber-100',
        'izin'         => 'bg-sky-50 text-sky-700 ring-1 ring-sky-100',
        'normal'       => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-100',
        'pulang_cepat' => 'bg-indigo-50 text-indigo-700 ring-1 ring-indigo-100',
    ];

    $primaryCards = [
        ['label' => 'Total Pegawai', 'value' => $totalUser, 'icon' => 'fa-solid fa-users', 'note' => $attendanceRate . '% sudah masuk', 'tone' => 'blue'],
        ['label' => 'Presensi', 'value' => $yearlyKpis['presensi'], 'denominator' => $yearTarget, 'year' => $yearlyKpis['year'], 'icon' => 'fa-solid fa-clipboard-list', 'note' => 'Total presensi ' . $yearPercent($yearlyKpis['presensi']) . '%', 'tone' => 'blue'],
        ['label' => 'Hadir', 'value' => $yearlyKpis['hadir'], 'denominator' => $yearTarget, 'year' => $yearlyKpis['year'], 'icon' => 'fa-solid fa-circle-check', 'note' => 'Tepat waktu ' . $yearPercent($yearlyKpis['hadir']) . '%', 'tone' => 'emerald'],
        ['label' => 'Telat', 'value' => $yearlyKpis['telat'], 'denominator' => $yearTarget, 'year' => $yearlyKpis['year'], 'icon' => 'fa-solid fa-clock', 'note' => 'Terlambat masuk ' . $yearPercent($yearlyKpis['telat']) . '%', 'tone' => 'amber'],
        ['label' => 'Pulang Cepat', 'value' => $yearlyKpis['pulangCepat'], 'denominator' => $yearTarget, 'year' => $yearlyKpis['year'], 'icon' => 'fa-solid fa-person-running', 'note' => 'Pulang awal ' . $yearPercent($yearlyKpis['pulangCepat']) . '%', 'tone' => 'indigo'],
        ['label' => 'Izin', 'value' => $yearlyKpis['izin'], 'denominator' => $yearTarget, 'year' => $yearlyKpis['year'], 'icon' => 'fa-solid fa-file-circle-check', 'note' => 'Izin approved ' . $yearPercent($yearlyKpis['izin']) . '%', 'tone' => 'sky'],
        ['label' => 'Alpha', 'value' => $yearlyKpis['alpha'], 'denominator' => $yearTarget, 'year' => $yearlyKpis['year'], 'icon' => 'fa-solid fa-user-xmark', 'note' => 'Tidak absen ' . $yearPercent($yearlyKpis['alpha']) . '%', 'tone' => 'rose'],
        ['label' => 'Shift Hari Ini', 'value' => $totalShiftHariIni, 'icon' => 'fa-solid fa-calendar-day', 'note' => 'Jadwal aktif', 'tone' => 'blue'],
        ['label' => 'User Masuk', 'value' => $userMasukHariIni, 'icon' => 'fa-solid fa-user-check', 'note' => 'Pegawai unik', 'tone' => 'cyan'],
        ['label' => 'Swap Pending', 'value' => $swapPending, 'icon' => 'fa-solid fa-right-left', 'note' => 'Menunggu approval', 'tone' => 'violet'],
    ];

    $toneClass = [
        'blue' => 'from-blue-700 to-blue-500 shadow-blue-700/20',
        'emerald' => 'from-emerald-600 to-teal-500 shadow-emerald-700/20',
        'amber' => 'from-amber-500 to-orange-500 shadow-amber-700/20',
        'indigo' => 'from-indigo-600 to-blue-500 shadow-indigo-700/20',
        'sky' => 'from-sky-600 to-cyan-500 shadow-sky-700/20',
        'rose' => 'from-rose-600 to-red-500 shadow-rose-700/20',
        'cyan' => 'from-cyan-600 to-blue-500 shadow-cyan-700/20',
        'violet' => 'from-violet-600 to-indigo-500 shadow-violet-700/20',
    ];
    $operationalStats = [
        ['label' => 'Masuk', 'value' => $userMasukHariIni],
        ['label' => 'Pulang', 'value' => $userPulangHariIni],
        ['label' => 'Tepat Waktu', 'value' => $hadir],
        ['label' => 'Telat', 'value' => $telat],
        ['label' => 'Izin', 'value' => $izin],
    ];

    $statusValues = [$hadir, $telat, $izin, $alpha];
@endphp

@once
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
<style>
    .admin-dashboard .dashboard-select {
        border-radius: .375rem !important;
    }
    .admin-dashboard .dashboard-select:hover,
    .admin-dashboard .dashboard-select:focus {
        color: #374151 !important;
    }
    html[data-admin-theme="dark"] .admin-dashboard .dashboard-select:hover,
    html[data-admin-theme="dark"] .admin-dashboard .dashboard-select:focus {
        color: #f8fafc !important;
    }
    .admin-dashboard .dashboard-page-link {
        border-radius: .375rem !important;
    }
    html[data-admin-theme="dark"] .admin-dashboard .dashboard-page-link:not(.bg-blue-600) {
        background: #0b1728 !important;
        border-color: rgba(125, 170, 255, .18) !important;
        color: #94a3b8 !important;
    }
    html[data-admin-theme="dark"] .admin-dashboard .dashboard-page-link:not(.bg-blue-600):hover {
        background: rgba(96, 165, 250, .12) !important;
        color: #f8fafc !important;
    }
    .admin-dashboard thead.dashboard-scroll-table {
        background: #eff6ff;
    }
    html[data-admin-theme="dark"] .admin-dashboard thead.dashboard-scroll-table {
        background: #172f55 !important;
    }
    .admin-dashboard [data-dashboard-reveal] {
        opacity: 0;
        transform: translateY(14px);
        animation: dashboardReveal .85s ease forwards;
    }
    .admin-dashboard .dashboard-kpi-card {
        animation-delay: calc(var(--reveal-index, 0) * 120ms);
    }
    .admin-dashboard .dashboard-chart-card {
        animation-delay: 360ms;
    }
    .admin-dashboard .dashboard-kpi-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 22px 46px rgba(37,99,235,.12) !important;
    }
    html[data-admin-theme="dark"] .admin-dashboard .dashboard-kpi-card:hover {
        box-shadow: 0 22px 46px rgba(0,0,0,.35) !important;
    }
    html[data-admin-theme="dark"] .admin-dashboard .ring-emerald-100,
    html[data-admin-theme="dark"] .admin-dashboard .ring-amber-100,
    html[data-admin-theme="dark"] .admin-dashboard .ring-sky-100,
    html[data-admin-theme="dark"] .admin-dashboard .ring-indigo-100,
    html[data-admin-theme="dark"] .admin-dashboard .ring-gray-200 {
        --tw-ring-color: rgba(148, 163, 184, .20) !important;
    }
    .admin-dashboard .dashboard-kpi-card .kpi-icon {
        animation: kpiIconPop 1s cubic-bezier(.2,.8,.2,1) both;
        animation-delay: calc(260ms + (var(--reveal-index, 0) * 120ms));
    }
    @keyframes dashboardReveal {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    @keyframes kpiIconPop {
        0% {
            transform: scale(.78);
        }
        70% {
            transform: scale(1.08);
        }
        100% {
            transform: scale(1);
        }
    }
    @media (prefers-reduced-motion: reduce) {
        .admin-dashboard [data-dashboard-reveal],
        .admin-dashboard .dashboard-kpi-card .kpi-icon {
            animation: none;
            opacity: 1;
            transform: none;
        }
    }
</style>
@endonce

<div class="admin-dashboard space-y-5 pb-5">
    <section class="rounded-md border border-blue-100 bg-white p-5 shadow-sm">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <p class="text-sm font-bold text-blue-600">Dashboard Admin</p>
                <h1 class="mt-2 text-2xl font-black tracking-tight text-gray-950">Ringkasan Presensi</h1>
                <p class="mt-1 text-sm font-medium text-gray-500">{{ $selectedDate->translatedFormat('l, d F Y') }}</p>
            </div>
            <form method="GET" action="{{ route('admin.dashboard') }}" data-auto-filter>
                <input type="hidden" name="chart_period" value="{{ $chartPeriod }}">
                <label class="relative block">
                    <i class="fa-solid fa-calendar-days absolute left-4 top-1/2 -translate-y-1/2 text-blue-600"></i>
                    <input
                        type="date"
                        name="tanggal"
                        value="{{ $tanggal }}"
                        class="h-11 w-full rounded-md border border-blue-100 bg-blue-50/60 pl-11 pr-4 text-sm font-bold text-gray-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 sm:w-52"
                    >
                </label>
            </form>
        </div>
    </section>

    <section>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-5">
            @foreach($primaryCards as $card)
                <article class="dashboard-kpi-card min-h-32 rounded-md border border-blue-100 bg-white p-5 shadow-sm transition duration-200" data-dashboard-reveal style="--reveal-index: {{ $loop->index }}">
                    <div class="flex items-start gap-4">
                        <div class="kpi-icon flex h-12 w-12 shrink-0 items-center justify-center rounded-md bg-gradient-to-br {{ $toneClass[$card['tone']] }} text-white shadow-lg">
                            <i class="{{ $card['icon'] }}"></i>
                        </div>
                        <div class="min-w-0">
                            <p class="truncate text-sm font-extrabold text-gray-500">{{ $card['label'] }}</p>
                            <div class="mt-2 flex items-end gap-1 text-3xl font-black leading-none text-gray-950">
                                <span data-count-to="{{ $card['value'] }}">0</span>
                                @if(isset($card['denominator']))
                                    <span class="text-xl leading-none text-gray-500">/{{ number_format($card['denominator'], 0, ',', '.') }}</span>
                                @endif
                            </div>
                            <div class="mt-2 flex items-center justify-between gap-3">
                                <p class="truncate text-xs font-semibold text-gray-400">{{ $card['note'] }}</p>
                                @if(isset($card['year']))
                                    <span class="shrink-0 text-xs font-black text-blue-600">{{ $card['year'] }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </article>
            @endforeach
        </div>

    </section>

    <section>
        <article class="dashboard-chart-card rounded-md border border-blue-100 bg-white p-5 shadow-sm" data-dashboard-reveal>
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <p class="text-sm font-bold text-blue-600">Analitik</p>
                    <h2 class="mt-1 text-xl font-black text-gray-950">{{ $chartTitle }}</h2>
                </div>
                <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <form method="GET" action="{{ route('admin.dashboard') }}" data-auto-filter>
                        <input type="hidden" name="tanggal" value="{{ $tanggal }}">
                        <label class="relative block">
                            <select
                                name="chart_period"
                                class="dashboard-select h-10 appearance-none rounded-md border border-blue-100 bg-blue-50/70 pl-3 pr-9 text-sm font-bold text-blue-700 outline-none transition hover:text-gray-700 focus:border-blue-500 focus:text-gray-700 focus:ring-4 focus:ring-blue-500/10"
                            >
                                <option value="7_days" @selected($chartPeriod === '7_days')>7 Hari</option>
                                <option value="1_month" @selected($chartPeriod === '1_month')>1 Bulan</option>
                                <option value="1_year" @selected($chartPeriod === '1_year')>1 Tahun</option>
                            </select>
                            <i class="fa-solid fa-chevron-down pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-xs text-blue-500"></i>
                        </label>
                    </form>
                    <div class="flex flex-wrap items-center gap-3">
                        <span class="inline-flex items-center gap-2 text-xs font-bold text-gray-500"><span class="h-3 w-3 bg-emerald-500"></span>Hadir</span>
                        <span class="inline-flex items-center gap-2 text-xs font-bold text-gray-500"><span class="h-3 w-3 bg-orange-500"></span>Telat</span>
                        <span class="inline-flex items-center gap-2 text-xs font-bold text-gray-500"><span class="h-3 w-3 bg-sky-400"></span>Izin</span>
                        <span class="inline-flex items-center gap-2 text-xs font-bold text-gray-500"><span class="h-3 w-3 bg-red-500"></span>Alpha</span>
                    </div>
                </div>
            </div>
            <div class="relative mt-6 h-[320px]">
                <canvas id="attendanceTrendChart"></canvas>
            </div>
        </article>
    </section>

    <section class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        <article class="dashboard-chart-card rounded-md border border-blue-100 bg-white p-5 shadow-sm" data-dashboard-reveal>
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-bold text-blue-600">Komposisi</p>
                    <h2 class="mt-1 text-lg font-black text-gray-950">Status Presensi Harian</h2>
                </div>
                <p class="text-xs font-bold text-gray-400">{{ $selectedDate->format('d/m/Y') }}</p>
            </div>
            <div class="relative mx-auto mt-5 h-[280px] max-w-md">
                <canvas id="statusDoughnutChart"></canvas>
            </div>
        </article>

        <article class="dashboard-chart-card rounded-md border border-blue-100 bg-white p-5 shadow-sm" data-dashboard-reveal>
            <div class="flex items-center justify-between gap-4">
                <div>
                    <p class="text-sm font-bold text-blue-600">Operasional</p>
                    <h2 class="mt-1 text-lg font-black text-gray-950">Aktivitas Operasional Harian</h2>
                </div>
                <p class="text-xs font-bold text-gray-400">{{ $selectedDate->format('d/m/Y') }}</p>
            </div>
            <div class="relative mt-5 h-[280px]">
                <canvas id="operationalBarChart"></canvas>
            </div>
        </article>
    </section>

    <section class="grid grid-cols-1 gap-4 xl:grid-cols-[0.85fr_1.35fr]">
        <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-1">
            <article class="overflow-hidden rounded-md border border-blue-100 bg-white shadow-sm">
                <div class="border-b border-blue-50 px-5 py-4">
                    <h2 class="text-sm font-bold text-gray-700">Presensi Tepat Waktu</h2>
                </div>
                <div class="max-h-[260px] overflow-auto">
                    <table class="min-w-full text-sm">
                        <thead class="dashboard-scroll-table sticky top-0 z-10 text-[11px] font-black uppercase tracking-wide text-blue-800 shadow-sm">
                            <tr>
                                <th class="px-4 py-3 text-left">No</th>
                                <th class="px-4 py-3 text-left">Nama</th>
                                <th class="px-4 py-3 text-right">Tepat Waktu</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-blue-50">
                            @forelse($topOnTimeUsers as $item)
                                <tr class="hover:bg-blue-50/40">
                                    <td class="px-4 py-3 font-semibold text-gray-500">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-3 font-bold text-gray-700">{{ $item->user?->name ?? 'User dihapus' }}</td>
                                    <td class="px-4 py-3 text-right font-black text-gray-800">{{ $item->total }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-8 text-center text-sm font-semibold text-gray-400">Belum ada data tepat waktu.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="flex flex-col gap-3 border-t border-blue-50 px-5 py-3 text-xs text-gray-500">
                    <p class="italic">Tepat waktu adalah jumlah presensi masuk tepat waktu tahun {{ $selectedYear }}.</p>
                    <span class="font-semibold">Menampilkan {{ $topOnTimeUsers->count() }} data</span>
                </div>
            </article>

            <article class="overflow-hidden rounded-md border border-blue-100 bg-white shadow-sm">
                <div class="border-b border-blue-50 px-5 py-4">
                    <h2 class="text-sm font-bold text-gray-700">Presensi Telat</h2>
                </div>
                <div class="max-h-[260px] overflow-auto">
                    <table class="min-w-full text-sm">
                        <thead class="dashboard-scroll-table sticky top-0 z-10 text-[11px] font-black uppercase tracking-wide text-blue-800 shadow-sm">
                            <tr>
                                <th class="px-4 py-3 text-left">No</th>
                                <th class="px-4 py-3 text-left">Nama</th>
                                <th class="px-4 py-3 text-right">Telat</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-blue-50">
                            @forelse($topLateUsers as $item)
                                <tr class="hover:bg-blue-50/40">
                                    <td class="px-4 py-3 font-semibold text-gray-500">{{ $loop->iteration }}</td>
                                    <td class="px-4 py-3 font-bold text-gray-700">{{ $item->user?->name ?? 'User dihapus' }}</td>
                                    <td class="px-4 py-3 text-right font-black text-amber-600">{{ $item->total }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-8 text-center text-sm font-semibold text-gray-400">Belum ada data telat.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="flex flex-col gap-3 border-t border-blue-50 px-5 py-3 text-xs text-gray-500">
                    <p class="italic">Telat adalah jumlah presensi masuk terlambat tahun {{ $selectedYear }}.</p>
                    <span class="font-semibold">Menampilkan {{ $topLateUsers->count() }} data</span>
                </div>
            </article>
        </div>

        @include('admin.partials.latest-presensi')
    </section>

</div>

<div
    id="adminDashboardData"
    data-labels="{{ base64_encode(json_encode(collect($chart)->pluck('label'))) }}"
    data-hadir="{{ base64_encode(json_encode(collect($chart)->pluck('hadir'))) }}"
    data-telat="{{ base64_encode(json_encode(collect($chart)->pluck('telat'))) }}"
    data-izin="{{ base64_encode(json_encode(collect($chart)->pluck('izin'))) }}"
    data-alpha="{{ base64_encode(json_encode(collect($chart)->pluck('alpha'))) }}"
    data-operational="{{ base64_encode(json_encode($operationalStats)) }}"
    data-status-values="{{ base64_encode(json_encode($statusValues)) }}"
    hidden
></div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.0/chart.umd.min.js"></script>
<script>
(function () {
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const dashboardData = document.getElementById('adminDashboardData');
    const parseDashboardData = (key, fallback) => JSON.parse(atob(dashboardData?.dataset[key] || fallback));
    const labels = parseDashboardData('labels', 'W10=');
    const hadir = parseDashboardData('hadir', 'W10=');
    const telat = parseDashboardData('telat', 'W10=');
    const izin = parseDashboardData('izin', 'W10=');
    const alpha = parseDashboardData('alpha', 'W10=');
    const operational = parseDashboardData('operational', 'e30=');
    const statusLabels = ['Hadir', 'Telat', 'Izin', 'Alpha'];
    const statusValues = parseDashboardData('statusValues', 'W10=');
    const dashboardCharts = [];
    let latestPresensiAbortController = null;

    function chartTheme() {
        const isDark = document.documentElement.dataset.adminTheme === 'dark';

        return {
            tick: isDark ? '#94a3b8' : '#64748b',
            grid: isDark ? 'rgba(148,163,184,.16)' : '#eaf3ff',
            legend: isDark ? '#cbd5e1' : '#475569',
            tooltipBg: isDark ? '#020617' : '#0f172a',
            tooltipTitle: isDark ? '#93c5fd' : '#bfdbfe',
            tooltipBody: isDark ? '#f8fafc' : '#f8fafc',
            doughnutBorder: isDark ? '#111f33' : '#ffffff',
        };
    }

    const chartColors = chartTheme();

    const baseOptions = {
        responsive: true,
        maintainAspectRatio: false,
        animation: reduceMotion ? false : {
            duration: 1600,
            easing: 'easeOutQuart',
        },
        animations: reduceMotion ? {} : {
            tension: {
                duration: 1300,
                easing: 'easeOutQuart',
                from: .12,
                to: .42,
            }
        },
        plugins: {
            legend: { labels: { boxWidth: 10, boxHeight: 10, color: chartColors.legend, font: { size: 11, weight: '700' } } },
            tooltip: { backgroundColor: chartColors.tooltipBg, titleColor: chartColors.tooltipTitle, bodyColor: chartColors.tooltipBody, padding: 12, cornerRadius: 8, boxPadding: 5 }
        }
    };

    function themedScales() {
        const colors = chartTheme();

        return {
            x: { grid: { display: false }, ticks: { color: colors.tick, font: { size: 11, weight: '700' } }, border: { display: false } },
            y: { beginAtZero: true, grid: { color: colors.grid }, ticks: { color: colors.tick, precision: 0, font: { size: 11, weight: '600' } }, border: { display: false } }
        };
    }

    function syncChartTheme() {
        const colors = chartTheme();

        dashboardCharts.forEach((chart) => {
            if (chart.options.plugins?.legend?.labels) {
                chart.options.plugins.legend.labels.color = colors.legend;
            }

            if (chart.options.plugins?.tooltip) {
                chart.options.plugins.tooltip.backgroundColor = colors.tooltipBg;
                chart.options.plugins.tooltip.titleColor = colors.tooltipTitle;
                chart.options.plugins.tooltip.bodyColor = colors.tooltipBody;
            }

            if (chart.options.scales?.x?.ticks) {
                chart.options.scales.x.ticks.color = colors.tick;
            }

            if (chart.options.scales?.y) {
                chart.options.scales.y.grid.color = colors.grid;
                chart.options.scales.y.ticks.color = colors.tick;
            }

            chart.data.datasets.forEach((dataset) => {
                if (dataset.borderColor === '#ffffff' || dataset.borderColor === '#111f33') {
                    dataset.borderColor = colors.doughnutBorder;
                }
            });

            chart.update('none');
        });
    }

    function animateCounters() {
        document.querySelectorAll('[data-count-to]').forEach((counter) => {
            const target = Number(counter.dataset.countTo || 0);
            const duration = reduceMotion ? 0 : 1400;

            if (!duration || target === 0) {
                counter.textContent = new Intl.NumberFormat('id-ID').format(target);
                return;
            }

            const startTime = performance.now();
            const formatter = new Intl.NumberFormat('id-ID');

            function tick(now) {
                const progress = Math.min((now - startTime) / duration, 1);
                const eased = 1 - Math.pow(1 - progress, 4);
                counter.textContent = formatter.format(Math.round(target * eased));

                if (progress < 1) {
                    requestAnimationFrame(tick);
                }
            }

            requestAnimationFrame(tick);
        });
    }

    animateCounters();

    function latestHistoryUrl(url) {
        const historyUrl = new URL(url, window.location.href);
        historyUrl.searchParams.delete('latest_only');

        return historyUrl;
    }

    async function loadLatestPresensi(url, pushHistory = true) {
        const currentCard = document.querySelector('[data-latest-presensi-card]');

        if (!currentCard || !url || url.endsWith('#')) {
            return;
        }

        latestPresensiAbortController?.abort();
        latestPresensiAbortController = new AbortController();

        const requestUrl = new URL(url, window.location.href);
        requestUrl.searchParams.set('latest_only', '1');

        currentCard.classList.add('opacity-60', 'pointer-events-none');

        try {
            const response = await fetch(requestUrl.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html',
                },
                signal: latestPresensiAbortController.signal,
            });

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}`);
            }

            const html = await response.text();
            const template = document.createElement('template');
            template.innerHTML = html.trim();
            const nextCard = template.content.querySelector('[data-latest-presensi-card]');

            if (!nextCard) {
                throw new Error('Partial presensi terbaru tidak valid.');
            }

            currentCard.replaceWith(nextCard);

            if (pushHistory) {
                window.history.pushState({ latestPresensiUrl: latestHistoryUrl(requestUrl).toString() }, '', latestHistoryUrl(requestUrl));
            }
        } catch (error) {
            if (error.name !== 'AbortError') {
                window.location.href = latestHistoryUrl(requestUrl).toString();
            }
        } finally {
            latestPresensiAbortController = null;
            document.querySelector('[data-latest-presensi-card]')?.classList.remove('opacity-60', 'pointer-events-none');
        }
    }

    document.addEventListener('click', (event) => {
        const link = event.target.closest('[data-latest-presensi-page]');

        if (!link || link.classList.contains('pointer-events-none')) {
            return;
        }

        event.preventDefault();
        loadLatestPresensi(link.href);
    });

    document.addEventListener('change', (event) => {
        const field = event.target;
        const form = field.closest('[data-latest-presensi-form]');

        if (!form) {
            return;
        }

        event.preventDefault();
        loadLatestPresensi(`${form.action}?${new URLSearchParams(new FormData(form)).toString()}`);
    });

    document.addEventListener('submit', (event) => {
        const form = event.target.closest('[data-latest-presensi-form]');

        if (!form) {
            return;
        }

        event.preventDefault();
        loadLatestPresensi(`${form.action}?${new URLSearchParams(new FormData(form)).toString()}`);
    });

    window.addEventListener('popstate', () => {
        loadLatestPresensi(window.location.href, false);
    });

    const trendCanvas = document.getElementById('attendanceTrendChart');
    if (trendCanvas) {
        const ctx = trendCanvas.getContext('2d');
        const gradient = ctx.createLinearGradient(0, 0, 0, 320);
        gradient.addColorStop(0, 'rgba(34,197,94,.22)');
        gradient.addColorStop(1, 'rgba(34,197,94,.02)');

        const trendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels,
                datasets: [
                    { label: 'Hadir', data: hadir, borderColor: '#22c55e', backgroundColor: gradient, borderWidth: 3, fill: true, tension: .42, pointRadius: 3, pointHoverRadius: 6 },
                    { label: 'Telat', data: telat, borderColor: '#f97316', backgroundColor: 'rgba(249,115,22,.08)', borderWidth: 2, fill: false, tension: .42, pointRadius: 3, pointHoverRadius: 6 },
                    { label: 'Izin', data: izin, borderColor: '#38bdf8', backgroundColor: 'rgba(56,189,248,.08)', borderWidth: 2, fill: false, tension: .42, pointRadius: 3, pointHoverRadius: 6 },
                    { label: 'Alpha', data: alpha, borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,.08)', borderWidth: 2, fill: false, tension: .42, pointRadius: 3, pointHoverRadius: 6 },
                ]
            },
            options: {
                ...baseOptions,
                interaction: { mode: 'index', intersect: false },
                plugins: { ...baseOptions.plugins, legend: { display: false } },
                transitions: reduceMotion ? {} : {
                    show: {
                        animations: {
                            x: { from: 0 },
                            y: { from: 0 },
                        }
                    }
                },
                scales: themedScales()
            }
        });
        dashboardCharts.push(trendChart);
    }

    const doughnutCanvas = document.getElementById('statusDoughnutChart');
    if (doughnutCanvas) {
        const doughnutChart = new Chart(doughnutCanvas, {
            type: 'doughnut',
            data: {
                labels: statusLabels,
                datasets: [{
                    data: statusValues,
                    backgroundColor: ['#22c55e', '#f97316', '#38bdf8', '#ef4444'],
                    borderColor: chartTheme().doughnutBorder,
                    borderWidth: 3,
                    hoverOffset: 8,
                }]
            },
            options: {
                ...baseOptions,
                cutout: '62%',
                animation: reduceMotion ? false : {
                    animateRotate: true,
                    animateScale: true,
                    duration: 1500,
                    easing: 'easeOutQuart',
                }
            }
        });
        dashboardCharts.push(doughnutChart);
    }

    const operationalCanvas = document.getElementById('operationalBarChart');
    if (operationalCanvas) {
        const operationalChart = new Chart(operationalCanvas, {
            type: 'bar',
            data: {
                labels: operational.map(item => item.label),
                datasets: [{
                    label: 'Jumlah',
                    data: operational.map(item => item.value),
                    backgroundColor: ['#2563eb', '#06b6d4', '#22c55e', '#f97316', '#38bdf8'],
                    borderRadius: 6,
                    maxBarThickness: 44,
                }]
            },
            options: {
                ...baseOptions,
                plugins: { ...baseOptions.plugins, legend: { display: false } },
                animation: reduceMotion ? false : {
                    duration: 1300,
                    easing: 'easeOutQuart',
                    delay: (context) => context.type === 'data' ? context.dataIndex * 140 : 0,
                },
                scales: themedScales()
            }
        });
        dashboardCharts.push(operationalChart);
    }

    window.addEventListener('admin-theme-change', syncChartTheme);
})();
</script>
@endsection
