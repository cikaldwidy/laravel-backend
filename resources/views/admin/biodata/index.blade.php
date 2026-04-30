@extends('layouts.admin')

@section('title', 'Biodata Pegawai')

@section('content')
<div class="bg-white p-6 rounded-xl shadow space-y-5">
    <div>
        <h2 class="font-bold text-lg text-gray-800">Biodata Pegawai</h2>
        <p class="text-sm text-gray-500">Lengkapi profil personal dan detail pekerjaan pegawai.</p>
    </div>
<<<<<<< HEAD

    @if(session('success'))
        <div class="bg-green-100 text-green-800 px-4 py-3 rounded">{{ session('success') }}</div>
    @endif

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="p-3 text-left">Pegawai</th>
                    <th class="p-3 text-left">Kontak</th>
                    <th class="p-3 text-left">Unit / Jabatan</th>
                    <th class="p-3 text-left">Status Data</th>
                    <th class="p-3 text-left">Aksi</th>
=======
    <table class="w-full border text-sm">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2 text-left">Nama</th>
                <th class="p-2 text-left">Email</th>
                <th class="p-2 text-left">Role</th>
                <th class="p-2 text-left">Status</th>
                <th class="p-2 text-left">Aksi</th>
            </tr>
        </thead>

        <tbody>
            @foreach($users as $u)
                @php
                    $hasProfile = isset($profiles[$u->id]);
                    $hasDetail = isset($details[$u->id]);
                    $status = ($hasProfile && $hasDetail) ? 'Lengkap' : (($hasProfile || $hasDetail) ? 'Sebagian' : 'Belum ada');
                    $badge = ($hasProfile && $hasDetail) ? 'bg-green-100 text-green-700' : (($hasProfile || $hasDetail) ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600');
                @endphp
                <tr class="border-t">
                    <td class="p-2">{{ $u->name }}</td>
                    <td class="p-2">{{ $u->email }}</td>
                    <td class="p-2">{{ $u->role }}</td>
                    <td class="p-2">
                        <span class="px-2 py-1 rounded text-xs font-semibold {{ $badge }}">{{ $status }}</span>
                    </td>
                    <td class="p-2 space-x-2">
                        <a href="{{ route('admin.biodata.edit', $u) }}" class="text-blue-600 hover:underline">
                            {{ ($hasProfile || $hasDetail) ? 'Edit' : 'Buat' }}
                        </a>

                        <form method="POST" action="{{ route('admin.biodata.destroy', $u) }}" class="inline" onsubmit="return confirm('Hapus biodata user ini?')">
                            @csrf
                            <button class="text-red-600 hover:underline" {{ (!$hasProfile && !$hasDetail) ? 'disabled' : '' }}>
                                Hapus
                            </button>
                        </form>
                    </td>
>>>>>>> 3eb0695bb80c78413bd0a2ed4851c35906a05df0
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($users as $u)
                    @php
                        $profile = $u->userProfile;
                        $detail = $u->employeeDetail;
                        $hasProfile = (bool) $profile;
                        $hasDetail = (bool) $detail;
                        $status = ($hasProfile && $hasDetail) ? 'Lengkap' : (($hasProfile || $hasDetail) ? 'Sebagian' : 'Belum ada');
                        $badge = ($hasProfile && $hasDetail) ? 'bg-green-100 text-green-700' : (($hasProfile || $hasDetail) ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600');
                    @endphp
                    <tr>
                        <td class="p-3">
                            <div class="font-semibold text-gray-800">{{ $u->name }}</div>
                            <div class="text-xs text-gray-500">{{ $u->email }}</div>
                        </td>
                        <td class="p-3">
                            <div>{{ $profile?->no_hp ?? '-' }}</div>
                            <div class="text-xs text-gray-500">NIK: {{ $profile?->nik ?? '-' }}</div>
                        </td>
                        <td class="p-3">
                            <div>{{ $detail?->unit?->nama_unit ?? '-' }}</div>
                            <div class="text-xs text-gray-500">{{ $detail?->jabatan ?? '-' }}</div>
                        </td>
                        <td class="p-3">
                            <span class="px-2 py-1 rounded text-xs font-semibold {{ $badge }}">{{ $status }}</span>
                        </td>
                        <td class="p-3">
                            <div class="flex flex-wrap gap-3">
                                <a href="{{ route('admin.biodata.edit', $u) }}" class="text-blue-600 font-semibold">
                                    {{ ($hasProfile || $hasDetail) ? 'Edit' : 'Lengkapi' }}
                                </a>
                                <form method="POST" action="{{ route('admin.biodata.destroy', $u) }}" onsubmit="return confirm('Hapus biodata pegawai ini?')">
                                    @csrf
                                    <button class="text-red-600 font-semibold disabled:text-gray-300" @disabled(!$hasProfile && !$hasDetail)>Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-6 text-center text-gray-500">Belum ada pegawai.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
