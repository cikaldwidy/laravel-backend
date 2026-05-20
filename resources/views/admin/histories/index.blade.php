@extends('layouts.admin')

@section('title', 'Riwayat Presensi')

@section('content')
<div class="space-y-6">
    <form method="GET" data-auto-filter class="bg-white p-4 rounded-xl shadow grid grid-cols-1 md:grid-cols-[minmax(12rem,24rem)_minmax(12rem,20rem)] gap-3">
        <div>
            <label class="text-xs font-semibold text-gray-600">Unit Kerja/Bagian</label>
            <select name="unit_id" class="border rounded px-3 py-2 w-full" required>
                <option value="" disabled @selected(blank(request('unit_id')))>Pilih Unit Kerja/Bagian</option>
                @foreach($units as $unit)
                    <option value="{{ $unit->id }}" @selected((string) request('unit_id') === (string) $unit->id)>{{ $unit->nama_departemen }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="text-xs font-semibold text-gray-600">Tanggal</label>
            <input type="date" name="tanggal" value="{{ request('tanggal') }}" class="border rounded px-3 py-2 w-full">
        </div>
    </form>

    <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="p-3 text-left">Nama</th>
                    <th class="p-3 text-left">Unit Kerja/Bagian</th>
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
                        <td class="p-3">{{ $item->user?->employeeDetail?->department?->nama_departemen ?? $item->user?->employeeDetail?->departemen ?? '-' }}</td>
                        <td class="p-3">{{ $item->tanggal->format('d/m/Y') }}</td>
                        <td class="p-3">{{ $item->jam_masuk?->format('H:i') ?? '-' }}</td>
                        <td class="p-3">{{ $item->jam_keluar?->format('H:i') ?? '-' }}</td>
                        <td class="p-3">{{ ucfirst($item->status ?? '-') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="p-6 text-center text-gray-500">
                            {{ request('unit_id') ? 'Belum ada data riwayat untuk unit kerja/bagian ini.' : 'Pilih unit kerja/bagian terlebih dahulu untuk menampilkan riwayat.' }}
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
