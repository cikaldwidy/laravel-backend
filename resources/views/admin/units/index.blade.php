@extends('layouts.admin')

@section('title', 'Master Unit')

@section('content')
<div class="space-y-6">
    <div class="bg-white p-6 rounded-xl shadow max-w-2xl mx-auto">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Tambah Unit</h2>
        <form method="POST" action="{{ route('admin.units.store') }}" class="grid md:grid-cols-[0.85fr_1.15fr_auto] gap-3">
            @csrf
            <select name="department_id" class="border rounded px-3 py-2">
                <option value="">Pilih unit kerja/bagian</option>
                @foreach($departments as $department)
                    <option value="{{ $department->id }}" @selected((string) old('department_id') === (string) $department->id)>{{ $department->nama_departemen }}</option>
                @endforeach
            </select>
            <input name="nama_unit" value="{{ old('nama_unit') }}" placeholder="IGD / ICU / RAWAT INAP" class="border rounded px-3 py-2">
            <button class="bg-blue-600 text-white px-4 py-2 rounded font-semibold">Simpan</button>
        </form>
        @error('department_id') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror
        @error('nama_unit') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror
    </div>

    <div class="bg-white p-6 rounded-xl shadow">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="p-3 text-left">Unit Kerja/Bagian</th>
                    <th class="p-3 text-left">Nama Unit</th>
                    <th class="p-3 text-left">Edit Cepat</th>
                    <th class="p-3 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($units as $unit)
                    <tr>
                        <td class="p-3 font-medium text-gray-700">{{ $unit->department?->nama_departemen ?? '-' }}</td>
                        <td class="p-3 font-medium text-gray-700">{{ $unit->nama_unit }}</td>
                        <td class="p-3">
                            <form method="POST" action="{{ route('admin.units.update', $unit) }}" class="grid md:grid-cols-[0.85fr_1.15fr_auto] gap-2">
                                @csrf
                                @method('PUT')
                                <select name="department_id" class="border rounded px-3 py-2">
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" @selected($unit->department_id === $department->id)>{{ $department->nama_departemen }}</option>
                                    @endforeach
                                </select>
                                <input name="nama_unit" value="{{ $unit->nama_unit }}" class="w-full border rounded px-3 py-2">
                                <button class="bg-amber-500 text-white px-3 py-2 rounded">Update</button>
                            </form>
                        </td>
                        <td class="p-3 w-28">
                            <form method="POST" action="{{ route('admin.units.destroy', $unit) }}" onsubmit="return confirm('Hapus unit ini?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 font-semibold">Hapus</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-6 text-center text-gray-500">Belum ada unit.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
