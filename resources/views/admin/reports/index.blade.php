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
<div class="report-page space-y-6 w-full min-w-0 max-w-full overflow-x-hidden">
    <form method="GET" class="bg-white p-4 rounded-xl shadow grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-3 w-full min-w-0">
        <input type="month" name="bulan" value="{{ request('bulan') }}" class="border rounded px-3 py-2 w-full min-w-0">
        <input type="date" name="date_from" value="{{ request('date_from', $tanggalMulai->toDateString()) }}" class="border rounded px-3 py-2 w-full min-w-0">
        <input type="date" name="date_to" value="{{ request('date_to', $tanggalSelesai->toDateString()) }}" class="border rounded px-3 py-2 w-full min-w-0">
        <select name="user_id" class="border rounded px-3 py-2 w-full min-w-0">
            <option value="">Semua User</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" @selected((string) request('user_id') === (string) $user->id)>{{ $user->name }}</option>
            @endforeach
        </select>
        <select name="unit_id" class="border rounded px-3 py-2 w-full min-w-0">
            <option value="">Semua Unit</option>
            @foreach($units as $unit)
                <option value="{{ $unit->id }}" @selected((string) request('unit_id') === (string) $unit->id)>{{ $unit->nama_unit }}</option>
            @endforeach
        </select>
        <button class="bg-blue-600 text-white rounded px-4 py-2 font-semibold sm:col-span-2 xl:col-span-5 w-full min-w-0">Generate Laporan</button>
    </form>

    <div class="flex flex-wrap gap-3 w-full min-w-0">
        <a href="{{ route('admin.reports.excel', request()->query()) }}" class="bg-emerald-600 text-white px-4 py-2 rounded font-semibold">Export Excel</a>
        <a href="{{ route('admin.reports.pdf', request()->query()) }}" target="_blank" class="bg-red-600 text-white px-4 py-2 rounded font-semibold">Export PDF</a>
    </div>

    <div class="bg-white rounded-xl shadow p-4 w-full min-w-0 overflow-hidden">
        <div class="flex flex-wrap gap-2 text-xs">
            @foreach($matrix['legend'] as $legend)
                <span class="inline-flex items-center gap-2 border border-slate-100 rounded-full px-3 py-1">
                    <span class="inline-flex w-8 h-6 items-center justify-center rounded font-bold {{ $cellClasses[$legend['class']] ?? 'bg-slate-100' }}">{{ $legend['label'] }}</span>
                    <span class="text-slate-600">{{ $legend['text'] }}</span>
                </span>
            @endforeach
        </div>
    </div>

    @forelse($matrix['unit_groups'] as $unitGroup)
        <div class="report-table-card bg-white rounded-xl shadow w-full min-w-0 max-w-full overflow-hidden">
            <div class="px-3 py-2 border-b border-slate-100 flex flex-wrap items-center justify-between gap-2 bg-slate-50">
                <div>
                    <h2 class="font-bold text-xs text-slate-900 leading-tight">Unit {{ $unitGroup['unit'] }}</h2>
                    <p class="text-[10px] text-slate-500 leading-tight">{{ count($unitGroup['employees']) }} pegawai · {{ $unitGroup['total_hours'] }} total jam kerja</p>
                </div>
                <div class="text-[10px] text-slate-500 leading-tight">
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
        <div class="bg-white rounded-xl shadow p-6 text-center text-gray-500">Belum ada data laporan.</div>
    @endforelse
</div>
@endsection
