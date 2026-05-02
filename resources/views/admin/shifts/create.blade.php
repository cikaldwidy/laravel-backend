@extends('layouts.admin')

@section('title', 'Tambah Shift')

@section('content')
<div class="max-w-xl mx-auto">
    <div class="bg-white rounded-lg shadow p-6 space-y-4">
        <div>
            <h2 class="text-lg font-bold text-gray-800">Tambah Shift</h2>
            <p class="text-sm text-gray-500">Isi nama shift dan jam masuk/pulang.</p>
        </div>

        <form method="POST" action="{{ route('admin.shifts.store') }}" class="space-y-4">
            @csrf

            <div>
                <label class="text-sm font-semibold text-gray-700">Nama Shift</label>
                <input name="nama_shift" value="{{ old('nama_shift') }}" class="mt-1 w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="SHIFT 1 / SHIFT MALAM">
                @error('nama_shift') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-semibold text-gray-700">Jam Masuk</label>
                    <input type="time" name="jam_masuk" value="{{ old('jam_masuk') }}" class="mt-1 w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('jam_masuk') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Jam Pulang</label>
                    <input type="time" name="jam_pulang" value="{{ old('jam_pulang') }}" class="mt-1 w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @error('jam_pulang') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex items-center gap-2">
                <a href="{{ route('admin.shifts.index') }}" class="px-4 py-2 rounded-md border text-sm font-semibold hover:bg-gray-50">Batal</a>
                <button class="px-4 py-2 rounded-md bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold">Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
