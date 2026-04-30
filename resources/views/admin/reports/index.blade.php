@extends('layouts.admin')

@section('title', 'Laporan')

@section('content')
<div class="space-y-6">
    <form method="GET" class="bg-white p-4 rounded-xl shadow grid md:grid-cols-5 gap-3">
        <input type="month" name="bulan" value="{{ request('bulan') }}" class="border rounded px-3 py-2">
        <input type="date" name="date_from" value="{{ request('date_from', $tanggalMulai->toDateString()) }}" class="border rounded px-3 py-2">
        <input type="date" name="date_to" value="{{ request('date_to', $tanggalSelesai->toDateString()) }}" class="border rounded px-3 py-2">
        <select name="user_id" class="border rounded px-3 py-2">
            <option value="">Semua User</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" @selected((string) request('user_id') === (string) $user->id)>{{ $user->name }}</option>
            @endforeach
        </select>
        <select name="unit_id" class="border rounded px-3 py-2">
            <option value="">Semua Unit</option>
            @foreach($units as $unit)
                <option value="{{ $unit->id }}" @selected((string) request('unit_id') === (string) $unit->id)>{{ $unit->nama_unit }}</option>
            @endforeach
        </select>
        <button class="bg-blue-600 text-white rounded px-4 py-2 font-semibold md:col-span-5">Generate Laporan</button>
    </form>

    <div class="flex gap-3">
        <a href="{{ route('admin.reports.excel', request()->query()) }}" class="bg-emerald-600 text-white px-4 py-2 rounded font-semibold">Export Excel</a>
        <a href="{{ route('admin.reports.pdf', request()->query()) }}" target="_blank" class="bg-red-600 text-white px-4 py-2 rounded font-semibold">Export PDF</a>
    </div>

    <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="p-3 text-left">Tanggal</th>
                    <th class="p-3 text-left">Nama</th>
                    <th class="p-3 text-left">Unit</th>
                    <th class="p-3 text-left">Shift</th>
                    <th class="p-3 text-left">Check In</th>
                    <th class="p-3 text-left">Check Out</th>
                    <th class="p-3 text-left">Status</th>
                    <th class="p-3 text-left">Keterangan</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($rows as $row)
                    <tr>
                        <td class="p-3">{{ $row['tanggal'] }}</td>
                        <td class="p-3">{{ $row['nama'] }}</td>
                        <td class="p-3">{{ $row['unit'] }}</td>
                        <td class="p-3">{{ $row['shift'] }}</td>
                        <td class="p-3">{{ $row['check_in'] }}</td>
                        <td class="p-3">{{ $row['check_out'] }}</td>
                        <td class="p-3">{{ ucfirst($row['status']) }}</td>
                        <td class="p-3">{{ $row['keterangan'] }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="p-6 text-center text-gray-500">Belum ada data laporan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
