@extends('layouts.app')

@section('title', 'Riwayat Presensi')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-4xl mx-auto px-4 space-y-4">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Riwayat Presensi</h1>
            <p class="text-sm text-gray-500">Filter riwayat pribadi berdasarkan tanggal.</p>
        </div>

        <form method="GET" class="bg-white rounded-xl shadow p-4 grid md:grid-cols-3 gap-3">
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="border rounded px-3 py-2">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="border rounded px-3 py-2">
            <button class="bg-slate-800 text-white rounded px-4 py-2">Filter</button>
        </form>

        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3 text-left">Tanggal</th>
                        <th class="p-3 text-left">Check In</th>
                        <th class="p-3 text-left">Check Out</th>
                        <th class="p-3 text-left">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($histories as $item)
                        <tr>
                            <td class="p-3">{{ $item->tanggal->format('d/m/Y') }}</td>
                            <td class="p-3">{{ $item->jam_masuk?->format('H:i') ?? '-' }}</td>
                            <td class="p-3">{{ $item->jam_keluar?->format('H:i') ?? '-' }}</td>
                            <td class="p-3">{{ ucfirst($item->status ?? '-') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-6 text-center text-gray-500">Belum ada riwayat.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
