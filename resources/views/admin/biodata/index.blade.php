@extends('layouts.admin')

@section('title', 'Biodata Pegawai')

@section('content')
<div class="bg-white p-6 rounded-xl shadow space-y-5">
    <div>
        <h2 class="font-bold text-lg text-gray-800">Biodata Pegawai</h2>
        <p class="text-sm text-gray-500">Lengkapi profil personal dan detail pekerjaan pegawai.</p>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="p-3 text-left">Pegawai</th>
                    <th class="p-3 text-left">Kontak</th>
                    <th class="p-3 text-left">Unit Kerja/Bagian / Jabatan</th>
                    <th class="p-3 text-left">Status Data</th>
                    <th class="p-3 text-left">Aksi</th>
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
                            <div class="text-xs text-gray-500">Agama: {{ $profile?->agama ?? '-' }}</div>
                        </td>
                        <td class="p-3">
                            <div>{{ $detail?->department?->nama_departemen ?? $detail?->departemen ?? '-' }}</div>
                            <div class="text-xs text-gray-500">{{ $detail?->position?->nama_jabatan ?? $detail?->jabatan ?? '-' }}</div>
                        </td>
                        <td class="p-3">
                            <span class="px-2 py-1 rounded text-xs font-semibold {{ $badge }}">{{ $status }}</span>
                        </td>
                        <td class="p-3">
                            <div class="flex flex-wrap gap-3">
                                <a href="{{ route('admin.biodata.edit', $u) }}" class="text-blue-600 font-semibold">
                                    {{ ($hasProfile || $hasDetail) ? 'Edit' : 'Lengkapi' }}
                                </a>
                                <form method="POST" action="{{ route('admin.biodata.destroy', $u) }}" data-confirm-form data-confirm-title="Hapus biodata pegawai?" data-confirm-message="Data biodata pegawai ini akan dihapus." data-confirm-button="Hapus">
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
