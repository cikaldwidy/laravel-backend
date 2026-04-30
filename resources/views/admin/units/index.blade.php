@extends('layouts.admin')

@section('title', 'Master Unit')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="bg-green-100 text-green-800 px-4 py-3 rounded">{{ session('success') }}</div>
    @endif

    <div class="bg-white p-6 rounded-xl shadow max-w-xl">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Tambah Unit</h2>
        <form method="POST" action="{{ route('admin.units.store') }}" class="flex gap-3">
            @csrf
            <input name="nama_unit" value="{{ old('nama_unit') }}" placeholder="IGD / ICU / RAWAT INAP" class="flex-1 border rounded px-3 py-2">
            <button class="bg-blue-600 text-white px-4 py-2 rounded font-semibold">Simpan</button>
        </form>
        @error('nama_unit') <p class="text-xs text-red-600 mt-2">{{ $message }}</p> @enderror
    </div>

    <div class="bg-white p-6 rounded-xl shadow">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="p-3 text-left">Nama Unit</th>
                    <th class="p-3 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($units as $unit)
                    <tr>
                        <td class="p-3">
                            <form method="POST" action="{{ route('admin.units.update', $unit) }}" class="flex gap-2">
                                @csrf
                                @method('PUT')
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
                        <td colspan="2" class="p-6 text-center text-gray-500">Belum ada unit.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
