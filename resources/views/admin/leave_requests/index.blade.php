@extends('layouts.admin')

@section('title', 'Perizinan')

@section('content')
<div class="space-y-6">
    <form method="GET" data-auto-filter class="bg-white p-4 rounded-xl shadow grid md:grid-cols-4 gap-3">
        <select name="status" class="border rounded px-3 py-2">
            <option value="">Semua Status</option>
            @foreach(['pending','approved','rejected'] as $status)
                <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
            @endforeach
        </select>
        <select name="user_id" class="border rounded px-3 py-2">
            <option value="">Semua User</option>
            @foreach($users as $user)
                <option value="{{ $user->id }}" @selected((string) request('user_id') === (string) $user->id)>{{ $user->name }}</option>
            @endforeach
        </select>
        <select name="unit_id" class="border rounded px-3 py-2">
            <option value="">Semua Unit Kerja/Bagian</option>
            @foreach($units as $unit)
                <option value="{{ $unit->id }}" @selected((string) request('unit_id') === (string) $unit->id)>{{ $unit->nama_departemen }}</option>
            @endforeach
        </select>
        <input type="date" name="tanggal" value="{{ request('tanggal') }}" class="border rounded px-3 py-2">
    </form>

    <div class="bg-white rounded-xl shadow overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50">
                <tr>
                    <th class="p-3 text-left">User</th>
                    <th class="p-3 text-left">Unit Kerja/Bagian</th>
                    <th class="p-3 text-left">Jenis</th>
                    <th class="p-3 text-left">Periode</th>
                    <th class="p-3 text-left">Status</th>
                    <th class="p-3 text-left">Lampiran</th>
                    <th class="p-3 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($requests as $item)
                    <tr>
                        <td class="p-3">{{ $item->user?->name }}</td>
                        <td class="p-3">{{ $item->user?->employeeDetail?->department?->nama_departemen ?? $item->user?->employeeDetail?->departemen ?? '-' }}</td>
                        <td class="p-3">{{ ucfirst($item->jenis_izin) }}</td>
                        <td class="p-3">{{ $item->tanggal_mulai->format('d/m/Y') }} - {{ $item->tanggal_selesai->format('d/m/Y') }}</td>
                        <td class="p-3">{{ ucfirst($item->status) }}</td>
                        <td class="p-3">
                            @if($item->lampiran)
                                <a href="{{ asset('storage/' . $item->lampiran) }}" class="text-blue-600 hover:underline" target="_blank">Lihat</a>
                            @else
                                -
                            @endif
                        </td>
                        <td class="p-3">
                            <form method="POST" action="{{ route('admin.leave_requests.update', $item) }}" class="space-y-2">
                                @csrf
                                <select name="status" class="border rounded px-2 py-1 w-full">
                                    <option value="approved">Approve</option>
                                    <option value="rejected">Reject</option>
                                </select>
                                <input name="catatan_admin" value="{{ $item->catatan_admin }}" placeholder="Catatan admin" class="border rounded px-2 py-1 w-full">
                                <button class="bg-slate-800 text-white px-3 py-1 rounded">Simpan</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="p-6 text-center text-gray-500">Belum ada data izin.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
