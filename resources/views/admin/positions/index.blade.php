@extends('layouts.admin')

@section('title', 'Master Jabatan')

@section('content')
<div class="space-y-6">
    <div class="bg-white p-6 rounded-xl shadow max-w-2xl">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Tambah Jabatan</h2>
        <form method="POST" action="{{ route('admin.positions.store') }}" class="grid md:grid-cols-[0.85fr_1.15fr_auto] gap-3">
            @csrf
            <select name="department_id" class="border rounded px-3 py-2">
                <option value="">Pilih departemen</option>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}" @selected((string) old('department_id') === (string) $department->id)>{{ $department->nama_departemen }}</option>
                @endforeach
            </select>
            <input name="nama_jabatan" value="{{ old('nama_jabatan') }}" placeholder="Perawat / Dokter / Kepala Ruangan" class="border rounded px-3 py-2">
            <button class="bg-blue-600 text-white px-4 py-2 rounded font-semibold">Simpan</button>
        </form>
        @error('department_id') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror
        @error('nama_jabatan') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror
    </div>

    <div class="bg-white p-6 rounded-xl shadow">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="p-3 text-left">Departemen</th>
                    <th class="p-3 text-left">Jabatan</th>
                    <th class="p-3 text-left">Edit Cepat</th>
                    <th class="p-3 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($positions as $position)
                    <tr>
                        <td class="p-3 font-medium text-gray-700">{{ $position->department?->nama_departemen ?? '-' }}</td>
                        <td class="p-3 font-medium text-gray-700">{{ $position->nama_jabatan }}</td>
                        <td class="p-3">
                            <form method="POST" action="{{ route('admin.positions.update', $position) }}" class="grid md:grid-cols-[0.85fr_1.15fr_auto] gap-2">
                                @csrf
                                @method('PUT')
                                <select name="department_id" class="border rounded px-3 py-2">
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" @selected($position->department_id === $department->id)>{{ $department->nama_departemen }}</option>
                                    @endforeach
                                </select>
                                <input name="nama_jabatan" value="{{ $position->nama_jabatan }}" class="border rounded px-3 py-2">
                                <button class="bg-amber-500 text-white px-3 py-2 rounded">Update</button>
                            </form>
                        </td>
                        <td class="p-3 w-28">
                            <form method="POST" action="{{ route('admin.positions.destroy', $position) }}" onsubmit="return confirm('Hapus jabatan ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 font-semibold">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-6 text-center text-gray-500">Belum ada jabatan.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
