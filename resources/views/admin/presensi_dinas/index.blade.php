@extends('layouts.admin')

@section('title', 'Presensi Dinas')

@section('content')
@php
    $statusOptions = ['hadir', 'terlambat', 'izin', 'sakit', 'alpha'];
@endphp

<div class="space-y-5">
    <div class="bg-white rounded-md shadow border border-gray-200 p-4 flex flex-wrap items-end justify-between gap-3">
        <form method="GET" action="{{ route('presensi-dinas.index') }}" class="flex flex-wrap items-end gap-3">
            <div>
                <label class="text-xs font-semibold text-gray-600">Tanggal</label>
                <input type="date" name="tanggal" value="{{ $tanggal }}" class="block border rounded-md px-3 py-2 text-sm">
            </div>
            <button class="bg-slate-900 text-white px-4 py-2 rounded-md text-sm font-semibold">Tampilkan</button>
        </form>
    </div>

    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 rounded-md text-sm">
            {{ session('success') }}
        </div>
    @endif

    <form method="POST" action="{{ route('presensi-dinas.store') }}" class="bg-white rounded-md shadow border border-gray-200 p-4 space-y-4">
        @csrf
        <input type="hidden" name="tanggal" value="{{ $tanggal }}">

        <div class="overflow-x-auto">
            <table class="min-w-full border-collapse">
                <thead>
                    <tr class="bg-slate-900 text-white">
                        <th class="border border-slate-300 px-3 py-2 text-xs text-center">No</th>
                        <th class="border border-slate-300 px-3 py-2 text-xs text-left">Nama</th>
                        <th class="border border-slate-300 px-3 py-2 text-xs text-center">Jadwal</th>
                        <th class="border border-slate-300 px-3 py-2 text-xs text-center">Jam Masuk</th>
                        <th class="border border-slate-300 px-3 py-2 text-xs text-center">Jam Pulang</th>
                        <th class="border border-slate-300 px-3 py-2 text-xs text-center">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $index => $user)
                        @php
                            $schedule = $schedules->get($user->id);
                            $presensi = $presensis->get($user->id);
                            $scheduleLabel = $schedule
                                ? (($schedule->shift_code ?: $schedule->nama_shift) . ' (' . $schedule->jam_masuk->format('H:i') . '-' . $schedule->jam_pulang->format('H:i') . ')')
                                : '-';
                        @endphp
                        <tr>
                            <td class="border border-slate-300 px-3 py-2 text-sm text-center">{{ $index + 1 }}</td>
                            <td class="border border-slate-300 px-3 py-2 text-sm font-semibold">{{ $user->name }}</td>
                            <td class="border border-slate-300 px-3 py-2 text-sm text-center">{{ $scheduleLabel }}</td>
                            <td class="border border-slate-300 px-3 py-2 text-sm text-center">
                                <input type="time" name="presensi[{{ $user->id }}][jam_masuk]" value="{{ old("presensi.{$user->id}.jam_masuk", $presensi?->jam_masuk?->format('H:i')) }}" class="border rounded px-2 py-1 text-sm">
                            </td>
                            <td class="border border-slate-300 px-3 py-2 text-sm text-center">
                                <input type="time" name="presensi[{{ $user->id }}][jam_pulang]" value="{{ old("presensi.{$user->id}.jam_pulang", $presensi?->jam_keluar?->format('H:i')) }}" class="border rounded px-2 py-1 text-sm">
                            </td>
                            <td class="border border-slate-300 px-3 py-2 text-sm text-center">
                                <select name="presensi[{{ $user->id }}][status]" class="border rounded px-2 py-1 text-sm">
                                    @foreach($statusOptions as $status)
                                        <option value="{{ $status }}" @selected(old("presensi.{$user->id}.status", $presensi?->status ?? 'hadir') === $status)>{{ ucfirst($status) }}</option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="border border-slate-300 px-3 py-6 text-center text-gray-500">Belum ada pegawai.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="flex justify-end">
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-md text-sm font-semibold">Simpan Presensi</button>
        </div>
    </form>
</div>
@endsection
