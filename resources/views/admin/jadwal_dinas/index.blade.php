@extends('layouts.admin')

@section('title', 'Jadwal Bulanan')

@section('content')
@php
    $monthName = $monthStart->translatedFormat('F');
    $scheduleTableWidth = 400 + (count($dates) * 60);
    $shiftClasses = [
        'P' => 'shift-p',
        'S' => 'shift-s',
        'M' => 'shift-m',
        'O' => 'shift-o',
    ];
@endphp

<style>
    #main-content,
    #main-content > .flex-1,
    .duty-page,
    .duty-card {
        min-width: 0 !important;
        max-width: 100% !important;
        overflow-x: hidden !important;
    }

    .duty-table-scroll {
        display: block;
        width: 100%;
        max-width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
        overscroll-behavior-x: contain;
        -webkit-overflow-scrolling: touch;
    }

    .duty-table {
        width: max-content;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .duty-table th,
    .duty-table td {
        border: 1px solid #cbd5e1;
        font-size: 12px;
    }

    .duty-table th {
        padding: 10px 8px;
        font-weight: 800;
    }

    .duty-table td {
        padding: 6px 7px;
    }

    .duty-name-cell {
        width: 400px;
        min-width: 400px;
        max-width: 400px;
        text-align: left;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .duty-date-cell {
        width: 60px;
        min-width: 60px;
        text-align: center;
    }

    .duty-sticky-name {
        position: sticky;
        left: 0;
        z-index: 20;
        box-shadow: 1px 0 0 #cbd5e1;
    }

    .duty-table thead .duty-sticky-name {
        z-index: 30;
        background: #ccfbf1;
    }

    .duty-table tbody .duty-sticky-name {
        background: #fff;
    }

    .duty-table tfoot .duty-sticky-name {
        background: inherit;
        z-index: 28;
    }

    .duty-table tfoot tr.bg-teal-600 .duty-sticky-name {
        background: #0d9488;
    }

    .duty-table tfoot tr:not(.bg-teal-600) .duty-sticky-name {
        background: #f0fdfa;
    }

    .shift-select {
        width: 46px;
        min-width: 46px;
        height: 34px;
        border: 0;
        border-radius: 6px;
        background: #f1f5f9;
        color: #64748b;
        font-weight: 800;
        text-align: center;
    }

    .shift-p .shift-select,
    .shift-p { background: #bbf7d0; color: #166534; }
    .shift-s .shift-select,
    .shift-s { background: #fef08a; color: #854d0e; }
    .shift-m .shift-select,
    .shift-m { background: #bfdbfe; color: #1e3a8a; }
    .shift-o .shift-select,
    .shift-o { background: #fecdd3; color: #991b1b; }
</style>

<div class="duty-page space-y-5 w-full min-w-0 max-w-full overflow-x-hidden">
    <div class="bg-white rounded-xl shadow border border-gray-200 p-4 flex flex-col xl:flex-row xl:items-end xl:justify-between gap-3">
        <form method="GET" action="{{ route('jadwal-dinas.index') }}" data-auto-filter class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-[11rem_8rem_minmax(12rem,18rem)] gap-3 items-end w-full xl:w-auto">
            <div class="min-w-0">
                <label class="text-xs font-semibold text-gray-600">Bulan</label>
                <select name="bulan" class="block border rounded-md px-3 py-2 text-sm w-full">
                    @for($month = 1; $month <= 12; $month++)
                        <option value="{{ $month }}" @selected((int) $bulan === $month)>{{ \Illuminate\Support\Carbon::create($tahun, $month, 1)->translatedFormat('F') }}</option>
                    @endfor
                </select>
            </div>
            <div class="min-w-0">
                <label class="text-xs font-semibold text-gray-600">Tahun</label>
                <input type="number" name="tahun" value="{{ $tahun }}" min="2000" max="2100" class="block border rounded-md px-3 py-2 text-sm w-full">
            </div>
            <div class="min-w-0">
                <label class="text-xs font-semibold text-gray-600">Unit Kerja/Bagian</label>
                <select name="unit_id" class="block border rounded-md px-3 py-2 text-sm w-full" required>
                    <option value="" disabled @selected(blank($selectedUnitId))>Pilih Unit Kerja/Bagian</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" @selected((string) $selectedUnitId === (string) $unit->id)>{{ $unit->nama_departemen }}</option>
                    @endforeach
                </select>
            </div>
        </form>

        @if($selectedUnitId)
            <a href="{{ route('jadwal-dinas.export', ['bulan' => $bulan, 'tahun' => $tahun, 'unit_id' => $selectedUnitId]) }}" class="bg-emerald-600 text-white px-4 py-2 rounded-md text-sm font-semibold text-center w-full xl:w-auto">Export Excel</a>
        @else
            <span class="bg-slate-200 text-slate-500 px-4 py-2 rounded-md text-sm font-semibold text-center w-full xl:w-auto cursor-not-allowed">Export Excel</span>
        @endif
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-md text-sm">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('jadwal-dinas.store') }}" class="space-y-5">
        @csrf
        <input type="hidden" name="bulan" value="{{ $bulan }}">
        <input type="hidden" name="tahun" value="{{ $tahun }}">
        <input type="hidden" name="unit_id" value="{{ $selectedUnitId }}">

        @forelse($unitGroups as $unitGroup)
            <div class="duty-card bg-white rounded-xl shadow border border-gray-200 overflow-hidden w-full min-w-0 max-w-full">
                <div class="px-4 py-3 border-b border-slate-100 bg-slate-50 flex flex-wrap items-center justify-between gap-2">
                    <div>
                        <h2 class="font-bold text-sm text-slate-900 leading-tight">Unit Kerja/Bagian {{ $unitGroup['unit'] }}</h2>
                        <p class="text-xs text-slate-500 leading-tight">{{ count($unitGroup['employees']) }} pegawai · {{ $unitGroup['shift_totals']['TOTAL'] }} total dinas</p>
                    </div>
                    <div class="text-xs text-slate-500 leading-tight">
                        Pagi {{ $unitGroup['shift_totals']['P'] }} · Sore {{ $unitGroup['shift_totals']['S'] }} · Malam {{ $unitGroup['shift_totals']['M'] }}
                    </div>
                </div>

                <div class="duty-table-scroll pb-1">
                    <table class="duty-table" data-duty-table-width="{{ $scheduleTableWidth }}">
                        <thead>
                            <tr class="bg-teal-100 text-slate-900">
                                <th class="duty-name-cell duty-sticky-name align-middle" rowspan="2">Nama / Tanggal</th>
                                @foreach($dates as $date)
                                    <th class="duty-date-cell">{{ $date->translatedFormat('D') }}</th>
                                @endforeach
                            </tr>
                            <tr class="bg-teal-100 text-slate-900">
                                @foreach($dates as $date)
                                    <th class="duty-date-cell">{{ $date->format('j') }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($unitGroup['employees'] as $row)
                                <tr>
                                    <td class="duty-name-cell duty-sticky-name font-semibold" title="{{ $row['employee']->name }}">{{ $row['employee']->name }}</td>
                                    @foreach($dates as $date)
                                        @php($code = $row['cells'][$date->toDateString()] ?? '')
                                        <td class="duty-date-cell {{ $shiftClasses[$code] ?? '' }}" data-shift-cell>
                                            <select name="jadwal[{{ $row['employee']->id }}][{{ $date->toDateString() }}]" class="shift-select" data-shift-select>
                                                <option value="">-</option>
                                                @foreach($shiftOptions as $optionCode => $label)
                                                    <option value="{{ $optionCode }}" @selected($code === $optionCode)>{{ $optionCode }}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="bg-teal-600 text-white">
                                <td class="duty-name-cell duty-sticky-name font-bold">Jumlah Grup Karyawan / Shift (hari)</td>
                                @foreach($dates as $date)
                                    <td class="duty-date-cell font-bold">{{ $unitGroup['daily_totals'][$date->toDateString()]['TOTAL'] }}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <td class="duty-name-cell duty-sticky-name bg-teal-50 font-semibold">Shift Pagi</td>
                                @foreach($dates as $date)
                                    <td class="duty-date-cell">{{ $unitGroup['daily_totals'][$date->toDateString()]['P'] }}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <td class="duty-name-cell duty-sticky-name bg-teal-50 font-semibold">Shift Sore</td>
                                @foreach($dates as $date)
                                    <td class="duty-date-cell">{{ $unitGroup['daily_totals'][$date->toDateString()]['S'] }}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <td class="duty-name-cell duty-sticky-name bg-teal-50 font-semibold">Shift Malam</td>
                                @foreach($dates as $date)
                                    <td class="duty-date-cell">{{ $unitGroup['daily_totals'][$date->toDateString()]['M'] }}</td>
                                @endforeach
                            </tr>
                            <tr>
                                <td class="duty-name-cell duty-sticky-name bg-teal-50 font-semibold">Off</td>
                                @foreach($dates as $date)
                                    <td class="duty-date-cell">{{ $unitGroup['daily_totals'][$date->toDateString()]['O'] }}</td>
                                @endforeach
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl shadow border border-gray-200 p-6 text-center text-sm text-gray-500">
                {{ $selectedUnitId ? 'Belum ada pegawai untuk unit kerja/bagian ini.' : 'Pilih unit kerja/bagian terlebih dahulu untuk menampilkan jadwal dinas.' }}
            </div>
        @endforelse

        @if($selectedUnitId)
            <div class="bg-white rounded-xl shadow border border-gray-200 p-4 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                <div class="flex flex-wrap items-center gap-2 text-xs text-gray-600">
                    <span class="px-2 py-1 border shift-p">P = Pagi</span>
                    <span class="px-2 py-1 border shift-s">S = Sore</span>
                    <span class="px-2 py-1 border shift-m">M = Malam</span>
                    <span class="px-2 py-1 border shift-o">O = Off</span>
                </div>
                <button class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-md text-sm font-semibold w-full lg:w-auto">Simpan Jadwal</button>
            </div>
        @endif
    </form>
</div>

<script>
document.querySelectorAll('[data-shift-select]').forEach((select) => {
    const syncClass = () => {
        const cell = select.closest('[data-shift-cell]');
        cell.classList.remove('shift-p', 'shift-s', 'shift-m', 'shift-o');

        if (select.value) {
            cell.classList.add(`shift-${select.value.toLowerCase()}`);
        }
    };

    select.addEventListener('change', syncClass);
    syncClass();
});

document.querySelectorAll('[data-duty-table-width]').forEach((table) => {
    const width = `${table.dataset.dutyTableWidth}px`;
    table.style.width = width;
    table.style.minWidth = width;
});
</script>
@endsection
