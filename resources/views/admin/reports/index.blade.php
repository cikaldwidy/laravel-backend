@extends('layouts.admin')

@section('title', 'Laporan')

@section('content')
<style>
    #main-content,
    #main-content > .flex-1,
    .report-page,
    .report-table-card {
        min-width: 0 !important;
        max-width: 100% !important;
        overflow-x: hidden !important;
    }

    .report-table-scroll {
        display: block;
        width: 100%;
        max-width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
        overscroll-behavior-x: contain;
        -webkit-overflow-scrolling: touch;
    }

    .report-table {
        width: max-content;
    }
</style>
@php
    $reportTableMinWidth = 260 + (count($matrix['dates']) * 46) + 280;
    $cellClasses = [
        'cell-present' => 'bg-emerald-500 text-white',
        'cell-late' => 'bg-amber-400 text-slate-950',
        'cell-warning' => 'bg-yellow-200 text-yellow-900',
        'cell-danger' => 'bg-red-600 text-white',
        'cell-off' => 'bg-red-600 text-white',
        'cell-leave' => 'bg-sky-500 text-white',
        'cell-empty' => 'bg-slate-100 text-slate-400',
    ];
@endphp
<div class="report-page w-full min-w-0 max-w-full space-y-5 overflow-x-hidden">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-gray-700">Laporan</h1>
            <p class="mt-0.5 text-sm text-gray-500">Rekap presensi, shift, dan total jam kerja pegawai.</p>
        </div>
    </div>

    <form method="GET" data-auto-filter class="grid w-full min-w-0 grid-cols-1 items-end gap-3 rounded-md border border-gray-200 bg-white p-4 shadow-sm sm:grid-cols-2 xl:grid-cols-[minmax(12rem,20rem)_minmax(12rem,24rem)]">
        <div>
            <label class="mb-2 block text-xs font-semibold text-gray-500">Bulan</label>
            <input type="month" name="bulan" value="{{ request('bulan', $selectedMonth) }}" class="w-full min-w-0 rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-700 transition focus:border-gray-500 focus:outline-none">
        </div>
        <div>
            <label class="mb-2 block text-xs font-semibold text-gray-500">Unit Kerja/Bagian</label>
            <select name="unit_id" class="w-full min-w-0 rounded-md border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 transition focus:border-gray-500 focus:outline-none" required>
                <option value="" disabled @selected(blank(request('unit_id')))>Pilih Unit Kerja/Bagian</option>
                @foreach($units as $unit)
                    <option value="{{ $unit->id }}" @selected((string) request('unit_id') === (string) $unit->id)>{{ $unit->nama_departemen }}</option>
                @endforeach
            </select>
        </div>
    </form>

    <div class="flex w-full min-w-0 flex-wrap gap-3">
        @if(request('unit_id'))
            <a href="{{ route('admin.reports.excel', ['bulan' => request('bulan', $selectedMonth), 'unit_id' => request('unit_id')]) }}" class="inline-flex items-center gap-2 rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-emerald-700">
                <i class="fas fa-file-excel text-xs"></i>
                Export Excel
            </a>
            <a href="{{ route('admin.reports.pdf', ['bulan' => request('bulan', $selectedMonth), 'unit_id' => request('unit_id')]) }}" target="_blank" class="inline-flex items-center gap-2 rounded-md bg-red-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-red-700">
                <i class="fas fa-file-pdf text-xs"></i>
                Export PDF
            </a>
        @else
            <span class="inline-flex cursor-not-allowed items-center gap-2 rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-400">
                <i class="fas fa-file-excel text-xs"></i>
                Export Excel
            </span>
            <span class="inline-flex cursor-not-allowed items-center gap-2 rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-400">
                <i class="fas fa-file-pdf text-xs"></i>
                Export PDF
            </span>
        @endif
    </div>

    <div class="w-full min-w-0 overflow-hidden rounded-md border border-gray-200 bg-white p-4 shadow-sm">
        <div class="flex flex-wrap gap-2 text-xs">
            @foreach($matrix['legend'] as $legend)
                <span class="inline-flex items-center gap-2 rounded-full border border-gray-100 px-3 py-1">
                    <span class="inline-flex w-8 h-6 items-center justify-center rounded font-bold {{ $cellClasses[$legend['class']] ?? 'bg-slate-100' }}">{{ $legend['label'] }}</span>
                    <span class="text-slate-600">{{ $legend['text'] }}</span>
                </span>
            @endforeach
        </div>
    </div>

    @forelse($matrix['unit_groups'] as $unitGroup)
        <div class="report-table-card w-full min-w-0 max-w-full overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm">
            <div class="flex flex-wrap items-center justify-between gap-2 border-b border-gray-100 bg-gray-50 px-5 py-3.5">
                <div>
                    <h2 class="text-sm font-semibold leading-tight text-gray-700">Unit Kerja/Bagian {{ $unitGroup['unit'] }}</h2>
                    <p class="text-[10px] text-slate-500 leading-tight">{{ count($unitGroup['employees']) }} pegawai · {{ $unitGroup['total_hours'] }} total jam kerja</p>
                </div>
                <div class="text-xs leading-tight text-gray-500">
                    Pagi {{ $unitGroup['shift_totals']['pagi'] }} · Sore {{ $unitGroup['shift_totals']['sore'] }} · Malam {{ $unitGroup['shift_totals']['malam'] }}
                </div>
            </div>
            <div class="report-table-scroll pb-1">
                <table class="report-table text-xs border-collapse table-fixed" style="width: {{ $reportTableMinWidth }}px; min-width: {{ $reportTableMinWidth }}px;">
                    <thead>
                        <tr class="bg-teal-100 text-slate-900">
                            <th class="p-2 border border-slate-300 text-left align-middle w-64 sticky left-0 z-10 bg-teal-100" rowspan="2">Nama / Tanggal</th>
                            @foreach($matrix['dates'] as $date)
                                <th class="p-2 border border-slate-300 text-center w-12">{{ $date->translatedFormat('D') }}</th>
                            @endforeach
                            <th class="p-2 border border-slate-300 text-center bg-teal-600 text-white" colspan="3">Jumlah Grup Karyawan / Shift</th>
                            <th class="p-2 border border-slate-300 text-center bg-teal-600 text-white w-20" rowspan="2">Total Jam Kerja</th>
                        </tr>
                        <tr class="bg-teal-100 text-slate-900">
                            @foreach($matrix['dates'] as $date)
                                <th class="p-2 border border-slate-300 text-center w-12">{{ $date->format('j') }}</th>
                            @endforeach
                            <th class="p-2 border border-slate-300 text-center bg-teal-600 text-white w-20">Shift Pagi</th>
                            <th class="p-2 border border-slate-300 text-center bg-teal-600 text-white w-20">Shift Sore</th>
                            <th class="p-2 border border-slate-300 text-center bg-teal-600 text-white w-20">Shift Malam</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($unitGroup['employees'] as $employee)
                            <tr>
                                <td class="p-2 border border-slate-300 font-semibold whitespace-nowrap sticky left-0 z-10 bg-white">{{ $employee['name'] }}</td>
                                @foreach($matrix['dates'] as $date)
                                    @php($cell = $employee['cells'][$date->toDateString()])
                                    <td class="p-1 border border-slate-300 text-center w-12">
                                        <span title="{{ $cell['title'] }}" class="inline-flex w-9 h-7 items-center justify-center rounded font-bold {{ $cellClasses[$cell['class']] ?? 'bg-slate-100' }}">
                                            {{ $cell['label'] }}
                                        </span>
                                    </td>
                                @endforeach
                                <td class="p-2 border border-slate-300 text-center font-semibold w-20">{{ $employee['shift_totals']['pagi'] }}</td>
                                <td class="p-2 border border-slate-300 text-center font-semibold w-20">{{ $employee['shift_totals']['sore'] }}</td>
                                <td class="p-2 border border-slate-300 text-center font-semibold w-20">{{ $employee['shift_totals']['malam'] }}</td>
                                <td class="p-2 border border-slate-300 text-center font-semibold w-20">{{ $employee['total_hours'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-teal-600 text-white">
                            <td class="p-2 border border-slate-300 font-bold sticky left-0 z-10 bg-teal-600">Jumlah Grup Karyawan / Shift (hari)</td>
                            @foreach($matrix['dates'] as $date)
                                <td class="p-2 border border-slate-300 text-center font-bold w-12">{{ $unitGroup['daily_totals'][$date->toDateString()]['pagi'] + $unitGroup['daily_totals'][$date->toDateString()]['sore'] + $unitGroup['daily_totals'][$date->toDateString()]['malam'] }}</td>
                            @endforeach
                            <td class="p-2 border border-slate-300" colspan="4"></td>
                        </tr>
                        @foreach(['pagi' => 'Shift Pagi', 'sore' => 'Shift Sore', 'malam' => 'Shift Malam'] as $key => $label)
                            <tr>
                                <td class="p-2 border border-slate-300 bg-teal-50 font-semibold sticky left-0 z-10">{{ $label }}</td>
                                @foreach($matrix['dates'] as $date)
                                    <td class="p-2 border border-slate-300 text-center w-12">{{ $unitGroup['daily_totals'][$date->toDateString()][$key] }}</td>
                                @endforeach
                                <td class="p-2 border border-slate-300" colspan="4"></td>
                            </tr>
                        @endforeach
                    </tfoot>
                </table>
            </div>
        </div>
    @empty
        <div class="rounded-md border border-gray-200 bg-white py-14 text-center shadow-sm">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-md bg-blue-50 text-blue-500">
                <i class="fas fa-chart-column text-xl"></i>
            </div>
            <p class="mt-3 text-sm font-semibold text-gray-700">
                {{ request('unit_id') ? 'Belum ada data laporan' : 'Pilih unit kerja/bagian terlebih dahulu' }}
            </p>
            <p class="text-xs text-gray-500">Data laporan akan muncul sesuai filter yang dipilih.</p>
        </div>
    @endforelse
</div>
@endsection
