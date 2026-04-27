@extends('layouts.app')

@section('title', 'Izin')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-4xl mx-auto px-4 space-y-4">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-800">Pengajuan Izin</h1>
                <p class="text-sm text-gray-500">Riwayat izin pribadi.</p>
            </div>
            <a href="{{ route('leave_requests.create') }}" class="bg-blue-600 text-white px-4 py-2 rounded-md font-semibold">Buat Izin</a>
        </div>

        @if(session('success'))
            <div class="bg-green-100 text-green-800 px-4 py-3 rounded">{{ session('success') }}</div>
        @endif

        <form method="GET" class="bg-white rounded-xl shadow p-4 grid md:grid-cols-3 gap-3">
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="border rounded px-3 py-2">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="border rounded px-3 py-2">
            <button class="bg-slate-800 text-white rounded px-4 py-2">Filter</button>
        </form>

        <div class="bg-white rounded-xl shadow overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="p-3 text-left">Jenis</th>
                        <th class="p-3 text-left">Tanggal</th>
                        <th class="p-3 text-left">Status</th>
                        <th class="p-3 text-left">Catatan</th>
                        <th class="p-3 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($requests as $requestItem)
                        <tr>
                            <td class="p-3">{{ ucfirst($requestItem->jenis_izin) }}</td>
                            <td class="p-3">{{ $requestItem->tanggal_mulai->format('d/m/Y') }} - {{ $requestItem->tanggal_selesai->format('d/m/Y') }}</td>
                            <td class="p-3">{{ ucfirst($requestItem->status) }}</td>
                            <td class="p-3">{{ $requestItem->catatan_admin ?: '-' }}</td>
                            <td class="p-3">
                                @if($requestItem->status === 'pending')
                                    <form method="POST" action="{{ route('leave_requests.destroy', $requestItem) }}" onsubmit="return confirm('Hapus pengajuan ini?')">
                                        @csrf
                                        <button class="text-red-600 font-semibold">Hapus</button>
                                    </form>
                                @else
                                    -
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="p-6 text-center text-gray-500">Belum ada pengajuan izin.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
