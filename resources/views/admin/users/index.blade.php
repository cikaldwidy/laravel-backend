@extends('layouts.admin')

@section('title', 'Akun Pegawai')

@section('content')
<<<<<<< HEAD
<div class="space-y-6">
    @if(session('success'))
        <div class="bg-green-100 text-green-800 px-4 py-3 rounded">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 text-red-800 px-4 py-3 rounded">{{ session('error') }}</div>
    @endif

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
=======

<div class="bg-white p-6 rounded-xl shadow">

    <div class="flex justify-between mb-4">
        <h2 class="font-bold text-lg">Data User</h2>
        <a href="/admin/users/create" class="bg-blue-500 text-white px-4 py-2 rounded">
            + Tambah
        </a>
    </div>
    <table class="w-full border text-sm">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2">Nama</th>
                <th>Email</th>
                <th>Role</th>
                <th>Unit</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            @foreach($users as $u)
            <tr class="border-t text-center">
                <td>{{ $u->name }}</td>
                <td>{{ $u->email }}</td>
                <td>{{ $u->role }}</td>
                <td>{{ $u->employeeDetail?->unit?->nama_unit ?? '-' }}</td>
                <td class="space-x-2">
                    <a href="/admin/users/{{ $u->id }}/edit" class="text-blue-500">Edit</a>
                    <a href="{{ route('admin.biodata.edit', $u) }}" class="text-emerald-600">Biodata</a>

                    <form method="POST" action="/admin/users/{{ $u->id }}/delete" class="inline">
                        @csrf
                        <button class="text-red-500">Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>

    </table>
>>>>>>> 3eb0695bb80c78413bd0a2ed4851c35906a05df0

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
                            $hasProfile = (bool) $u->userProfile;
                            $hasDetail = (bool) $u->employeeDetail;
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
                            <td class="p-3">{{ $u->employeeDetail?->unit?->nama_unit ?? '-' }}</td>
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
                                        <a href="{{ route('admin.biodata.edit', $u) }}" class="text-emerald-600 font-semibold">Biodata</a>
                                    @endif
                                    <form method="POST" action="{{ route('admin.users.destroy', $u->id) }}" onsubmit="return confirm('Hapus akun ini?')">
                                        @csrf
                                        <button class="text-red-600 font-semibold" @disabled($u->id === auth()->id())>Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
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
@endsection
