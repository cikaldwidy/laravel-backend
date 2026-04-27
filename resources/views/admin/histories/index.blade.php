@extends('layouts.admin')

@section('title', 'Riwayat Presensi')

@section('content')
<div class="space-y-6">
    <form method="GET" class="bg-white p-4 rounded-xl shadow grid md:grid-cols-4 gap-3">
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
        <input type="date" name="tanggal" value="{{ request('tanggal') }}" class="border rounded px-3 py-2">
        <button class="bg-blue-600 text-white rounded px-4 py-2 font-semibold">Filter</button>
    </form>

    <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="p-3 text-left">Nama</th>
                    <th class="p-3 text-left">Unit</th>
                    <th class="p-3 text-left">Tanggal</th>
                    <th class="p-3 text-left">Masuk</th>
                    <th class="p-3 text-left">Pulang</th>
                    <th class="p-3 text-left">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($histories as $item)
                    <tr>
                        <td class="p-3">{{ $item->user?->name ?? '-' }}</td>
                        <td class="p-3">{{ $item->user?->employeeDetail?->unit?->nama_unit ?? $item->user?->employeeDetail?->departemen ?? '-' }}</td>
                        <td class="p-3">{{ $item->tanggal->format('d/m/Y') }}</td>
                        <td class="p-3">{{ $item->jam_masuk?->format('H:i') ?? '-' }}</td>
                        <td class="p-3">{{ $item->jam_keluar?->format('H:i') ?? '-' }}</td>
                        <td class="p-3">{{ ucfirst($item->status ?? '-') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-6 text-center text-gray-500">Belum ada data riwayat.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
