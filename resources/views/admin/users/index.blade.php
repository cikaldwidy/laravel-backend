@extends('layouts.admin')

@section('title', 'Akun Pegawai')

@section('content')
<div class="space-y-6">
    <div class="bg-white p-6 rounded-xl shadow">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-5">
            <div>
                <h2 class="font-bold text-lg text-gray-800">Akun Pegawai</h2>
                <p class="text-sm text-gray-500">Kelola akses login, role, dan status kelengkapan data pegawai.</p>
            </div>
            <a href="{{ route('admin.users.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-semibold text-sm inline-flex items-center gap-2">
                <i class="fa-solid fa-plus"></i>
                Tambah Akun
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="p-3 text-left">Pegawai</th>
                        <th class="p-3 text-left">Role</th>
                        <th class="p-3 text-left">Unit</th>
                        <th class="p-3 text-left">Biodata</th>
                        <th class="p-3 text-left">Wajah</th>
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
                            $biodataComplete = $hasProfile && $hasDetail;
                        @endphp
                        <tr>
                            <td class="p-3">
                                <div class="font-semibold text-gray-800">{{ $u->name }}</div>
                                <div class="text-xs text-gray-500">{{ $u->email }}</div>
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-1 rounded text-xs font-semibold {{ $u->role === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }}">
                                    {{ ucfirst($u->role) }}
                                </span>
                            </td>
                            <td class="p-3">{{ $u->employeeDetail?->unit?->nama_unit ?? $u->employeeDetail?->department?->nama_departemen ?? '-' }}</td>
                            <td class="p-3">
                                <span class="px-2 py-1 rounded text-xs font-semibold {{ $biodataComplete ? 'bg-green-100 text-green-700' : (($hasProfile || $hasDetail) ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600') }}">
                                    {{ $biodataComplete ? 'Lengkap' : (($hasProfile || $hasDetail) ? 'Sebagian' : 'Belum ada') }}
                                </span>
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-1 rounded text-xs font-semibold {{ $u->faceEmbedding ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                    {{ $u->faceEmbedding ? 'Terdaftar' : 'Belum' }}
                                </span>
                            </td>
                            <td class="p-3">
                                <div class="flex flex-wrap gap-3">
                                    <a href="{{ route('admin.users.edit', $u->id) }}" class="text-blue-600 font-semibold">Edit Akun</a>
                                    @if($u->role === 'user')
                                        <a href="{{ route('admin.biodata.edit', $u) }}" class="text-emerald-600 font-semibold">
                                            {{ $biodataComplete ? 'Edit Biodata' : 'Lengkapi Biodata' }}
                                        </a>
                                        <button type="button" class="text-slate-700 font-semibold" data-toggle-detail="user-detail-{{ $u->id }}">Detail</button>
                                    @endif
                                    <form method="POST" action="{{ route('admin.users.destroy', $u->id) }}" onsubmit="return confirm('Hapus akun ini?')">
                                        @csrf
                                        <button class="text-red-600 font-semibold" @disabled($u->id === auth()->id())>Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @if($u->role === 'user')
                            <tr id="user-detail-{{ $u->id }}" class="hidden bg-gray-50">
                                <td colspan="6" class="p-4">
                                    <div class="grid md:grid-cols-3 gap-4 text-sm">
                                        <div class="bg-white border rounded-lg p-4">
                                            <p class="text-xs font-semibold text-gray-500 uppercase">Profil</p>
                                            <div class="mt-3 space-y-2">
                                                <p><span class="text-gray-500">No. HP:</span> {{ $profile?->no_hp ?? '-' }}</p>
                                                <p><span class="text-gray-500">NIK:</span> {{ $profile?->nik ?? '-' }}</p>
                                                <p><span class="text-gray-500">Gender:</span> {{ $profile?->jenis_kelamin === 'L' ? 'Laki-laki' : ($profile?->jenis_kelamin === 'P' ? 'Perempuan' : '-') }}</p>
                                                <p><span class="text-gray-500">Tanggal lahir:</span> {{ $profile?->tanggal_lahir?->format('d/m/Y') ?? '-' }}</p>
                                            </div>
                                        </div>
                                        <div class="bg-white border rounded-lg p-4">
                                            <p class="text-xs font-semibold text-gray-500 uppercase">Pekerjaan</p>
                                            <div class="mt-3 space-y-2">
                                                <p><span class="text-gray-500">NIP:</span> {{ $detail?->nip ?? '-' }}</p>
                                                <p><span class="text-gray-500">Unit:</span> {{ $detail?->unit?->nama_unit ?? '-' }}</p>
                                                <p><span class="text-gray-500">Departemen:</span> {{ $detail?->department?->nama_departemen ?? $detail?->departemen ?? '-' }}</p>
                                                <p><span class="text-gray-500">Jabatan:</span> {{ $detail?->position?->nama_jabatan ?? $detail?->jabatan ?? '-' }}</p>
                                                <p><span class="text-gray-500">Status:</span> {{ $detail?->status_kerja ? ucfirst($detail->status_kerja) : '-' }}</p>
                                            </div>
                                        </div>
                                        <div class="bg-white border rounded-lg p-4">
                                            <p class="text-xs font-semibold text-gray-500 uppercase">Alamat & Kelengkapan</p>
                                            <p class="mt-3 text-gray-700">{{ $profile?->alamat ?? '-' }}</p>
                                            <div class="mt-4 flex flex-wrap gap-2">
                                                <span class="px-2 py-1 rounded text-xs font-semibold {{ $biodataComplete ? 'bg-green-100 text-green-700' : 'bg-yellow-100 text-yellow-700' }}">
                                                    Biodata {{ $biodataComplete ? 'lengkap' : 'belum lengkap' }}
                                                </span>
                                                <span class="px-2 py-1 rounded text-xs font-semibold {{ $u->faceEmbedding ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                                                    Wajah {{ $u->faceEmbedding ? 'terdaftar' : 'belum terdaftar' }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endif
                    @empty
                        <tr>
                            <td colspan="6" class="p-6 text-center text-gray-500">Belum ada akun.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.querySelectorAll('[data-toggle-detail]').forEach((button) => {
    button.addEventListener('click', () => {
        const target = document.getElementById(button.dataset.toggleDetail);
        target?.classList.toggle('hidden');
    });
});
</script>
@endsection
