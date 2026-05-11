@extends('layouts.admin')

@section('title', 'Manajemen Shift')

@section('content')
<style>
    #main-content,
    #main-content > .flex-1,
    .schedule-page,
    .schedule-table-card {
        min-width: 0 !important;
        max-width: 100% !important;
        overflow-x: hidden !important;
    }

    .schedule-table-scroll {
        display: block;
        width: 100%;
        max-width: 100%;
        overflow-x: auto;
        overflow-y: hidden;
        overscroll-behavior-x: contain;
        -webkit-overflow-scrolling: touch;
    }

    .schedule-table {
        width: max-content;
    }
</style>
@php
    $scheduleTableMinWidth = 240 + (count($scheduleMatrix['dates']) * 44) + 160;
@endphp
<div class="schedule-page space-y-5 w-full min-w-0 max-w-full overflow-x-hidden">
    <div class="bg-white rounded-md shadow border border-gray-200 p-4 w-full min-w-0">
        <form method="GET" data-auto-filter class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-[minmax(12rem,22rem)_minmax(12rem,22rem)_auto] gap-3 items-end w-full min-w-0">
            <div>
                <label class="text-xs font-semibold text-gray-600">Tanggal</label>
                <input type="date" name="tanggal" value="{{ $tanggal }}" class="w-full min-w-0 border rounded-md px-3 py-2 text-sm">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600">Unit</label>
                <select name="unit_id" class="w-full min-w-0 border rounded-md px-3 py-2 text-sm" required>
                    <option value="" disabled @selected(blank(request('unit_id')))>Pilih Unit</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" @selected((string)request('unit_id') === (string)$unit->id)>{{ $unit->nama_unit }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('jadwal-dinas.index', array_filter(['bulan' => \Illuminate\Support\Carbon::parse($tanggal)->month, 'tahun' => \Illuminate\Support\Carbon::parse($tanggal)->year, 'unit_id' => request('unit_id')])) }}" class="bg-emerald-600 text-white px-4 py-2 rounded-md text-sm font-semibold">Kelola Jadwal Bulanan</a>
                <a href="{{ route('admin.shift_management.schedules') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm font-semibold">Reset</a>
            </div>
        </form>
    </div>

    <div class="bg-white rounded-md shadow border border-gray-200 p-4">
        <div class="flex flex-wrap items-center gap-3 text-xs">
            <div class="font-bold text-slate-800">Ringkasan Jadwal {{ $scheduleMatrix['period_label'] }}</div>
            <span class="inline-flex items-center gap-2 border border-slate-100 rounded-full px-3 py-1">
                <span class="inline-flex w-8 h-6 items-center justify-center rounded font-bold bg-emerald-600 text-white">M</span>
                <span class="text-slate-600">Masuk</span>
            </span>
            <span class="inline-flex items-center gap-2 border border-slate-100 rounded-full px-3 py-1">
                <span class="inline-flex w-8 h-6 items-center justify-center rounded font-bold bg-red-600 text-white">L</span>
                <span class="text-slate-600">Libur</span>
            </span>
            <span class="inline-flex items-center gap-2 border border-slate-100 rounded-full px-3 py-1">
                <span class="inline-flex w-8 h-6 items-center justify-center rounded font-bold bg-slate-100 text-slate-400">-</span>
                <span class="text-slate-600">Belum dijadwalkan</span>
            </span>
        </div>
    </div>

    @forelse($scheduleMatrix['unit_groups'] as $unitGroup)
        <div class="schedule-table-card bg-white rounded-md shadow border border-gray-200 overflow-hidden w-full min-w-0 max-w-full">
            <div class="px-3 py-2 border-b border-slate-100 bg-slate-50 flex flex-wrap items-center justify-between gap-2">
                <div>
                    <h2 class="font-bold text-xs text-slate-900 leading-tight">Unit {{ $unitGroup['unit'] }}</h2>
                    <p class="text-[10px] text-slate-500 leading-tight">{{ count($unitGroup['employees']) }} pegawai</p>
                </div>
                <div class="text-[10px] text-slate-500 leading-tight">
                    Masuk {{ $unitGroup['total_masuk'] }} &middot; Libur {{ $unitGroup['total_libur'] }}
                </div>
            </div>
            <div class="schedule-table-scroll pb-1">
                <table class="schedule-table text-xs border-collapse table-fixed" data-schedule-table-width="{{ $scheduleTableMinWidth }}">
                    <thead>
                        <tr class="bg-teal-100 text-slate-900">
                            <th class="p-2 border border-slate-300 text-left align-middle w-60 sticky left-0 z-10 bg-teal-100" rowspan="2">Nama / Tanggal</th>
                            @foreach($scheduleMatrix['dates'] as $date)
                                <th class="p-2 border border-slate-300 text-center w-11">{{ $date->translatedFormat('D') }}</th>
                            @endforeach
                            <th class="p-2 border border-slate-300 text-center bg-teal-600 text-white w-20" rowspan="2">Masuk</th>
                            <th class="p-2 border border-slate-300 text-center bg-teal-600 text-white w-20" rowspan="2">Libur</th>
                        </tr>
                        <tr class="bg-teal-100 text-slate-900">
                            @foreach($scheduleMatrix['dates'] as $date)
                                <th class="p-2 border border-slate-300 text-center w-11">{{ $date->format('j') }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($unitGroup['employees'] as $employee)
                            <tr>
                                <td class="p-2 border border-slate-300 font-semibold whitespace-nowrap sticky left-0 z-10 bg-white">{{ $employee['name'] }}</td>
                                @foreach($scheduleMatrix['dates'] as $date)
                                    @php($cell = $employee['cells'][$date->toDateString()])
                                    <td class="p-1 border border-slate-300 text-center w-11">
                                        <span title="{{ $cell['title'] }}" class="inline-flex w-8 h-7 items-center justify-center rounded font-bold {{ $cell['class'] }}">{{ $cell['label'] }}</span>
                                    </td>
                                @endforeach
                                <td class="p-2 border border-slate-300 text-center font-semibold w-20">{{ $employee['total_masuk'] }}</td>
                                <td class="p-2 border border-slate-300 text-center font-semibold w-20">{{ $employee['total_libur'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="bg-teal-600 text-white">
                            <td class="p-2 border border-slate-300 font-bold sticky left-0 z-10 bg-teal-600">Total Masuk</td>
                            @foreach($scheduleMatrix['dates'] as $date)
                                <td class="p-2 border border-slate-300 text-center font-bold">{{ $unitGroup['daily_totals'][$date->toDateString()]['masuk'] }}</td>
                            @endforeach
                            <td class="p-2 border border-slate-300 text-center font-bold">{{ $unitGroup['total_masuk'] }}</td>
                            <td class="p-2 border border-slate-300"></td>
                        </tr>
                        <tr>
                            <td class="p-2 border border-slate-300 bg-teal-50 font-semibold sticky left-0 z-10">Total Libur</td>
                            @foreach($scheduleMatrix['dates'] as $date)
                                <td class="p-2 border border-slate-300 text-center">{{ $unitGroup['daily_totals'][$date->toDateString()]['libur'] }}</td>
                            @endforeach
                            <td class="p-2 border border-slate-300"></td>
                            <td class="p-2 border border-slate-300 text-center font-semibold">{{ $unitGroup['total_libur'] }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    @empty
        <div class="bg-white rounded-md shadow border border-gray-200 p-6 text-center text-sm text-gray-500">
            {{ request('unit_id') ? 'Belum ada pegawai untuk unit ini.' : 'Pilih unit terlebih dahulu untuk menampilkan jadwal pegawai.' }}
        </div>
    @endforelse

</div>  
<script>
document.querySelectorAll('[data-schedule-table-width]').forEach((table) => {
    const width = `${table.dataset.scheduleTableWidth}px`;
    table.style.width = width;
    table.style.minWidth = width;
});
</script>
@endsection
