@extends('layouts.admin')

@section('title','Edit Biodata User')

@section('content')
<div class="bg-white p-6 rounded-xl shadow max-w-3xl space-y-4">
    <div class="flex items-start justify-between gap-3">
        <div>
            <h2 class="font-bold text-lg">Biodata: {{ $user->name }}</h2>
            <p class="text-sm text-gray-500">{{ $user->email }}</p>
        </div>
        <a href="{{ route('admin.biodata.index') }}" class="px-4 py-2 rounded border text-sm font-semibold hover:bg-gray-50">
            Kembali
        </a>
    </div>

    <form method="POST" action="{{ route('admin.biodata.update', $user) }}" enctype="multipart/form-data" class="space-y-4">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-semibold text-gray-700">Nama (readonly)</label>
                <input value="{{ $user->name }}" readonly class="w-full p-2 border rounded bg-gray-50 mt-1">
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-700">Email (readonly)</label>
                <input value="{{ $user->email }}" readonly class="w-full p-2 border rounded bg-gray-50 mt-1">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-semibold text-gray-700">No. HP</label>
                <input name="no_hp" value="{{ old('no_hp', $profile?->no_hp) }}" class="w-full p-2 border rounded mt-1">
                @error('no_hp') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-700">Tanggal Lahir</label>
                <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', optional($profile?->tanggal_lahir)->toDateString()) }}" class="w-full p-2 border rounded mt-1">
                @error('tanggal_lahir') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-semibold text-gray-700">Jenis Kelamin</label>
                <select name="jenis_kelamin" class="w-full p-2 border rounded mt-1">
                    <option value="">-- pilih --</option>
                    <option value="L" @selected(old('jenis_kelamin', $profile?->jenis_kelamin) === 'L')>L</option>
                    <option value="P" @selected(old('jenis_kelamin', $profile?->jenis_kelamin) === 'P')>P</option>
                </select>
                @error('jenis_kelamin') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-700">NIK (opsional)</label>
                <input name="nik" value="{{ old('nik', $profile?->nik) }}" class="w-full p-2 border rounded mt-1">
                @error('nik') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="text-sm font-semibold text-gray-700">Alamat</label>
            <textarea name="alamat" rows="3" class="w-full p-2 border rounded mt-1">{{ old('alamat', $profile?->alamat) }}</textarea>
            @error('alamat') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <hr>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-semibold text-gray-700">NIP</label>
                <input name="nip" value="{{ old('nip', $employeeDetail?->nip) }}" class="w-full p-2 border rounded mt-1">
                @error('nip') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-700">Status Kerja</label>
                <select name="status_kerja" class="w-full p-2 border rounded mt-1">
                    <option value="">-- pilih --</option>
                    <option value="tetap" @selected(old('status_kerja', $employeeDetail?->status_kerja) === 'tetap')>tetap</option>
                    <option value="kontrak" @selected(old('status_kerja', $employeeDetail?->status_kerja) === 'kontrak')>kontrak</option>
                    <option value="magang" @selected(old('status_kerja', $employeeDetail?->status_kerja) === 'magang')>magang</option>
                </select>
                @error('status_kerja') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="text-sm font-semibold text-gray-700">Departemen</label>
                <input name="departemen" value="{{ old('departemen', $employeeDetail?->departemen) }}" class="w-full p-2 border rounded mt-1">
                @error('departemen') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-700">Jabatan</label>
                <input name="jabatan" value="{{ old('jabatan', $employeeDetail?->jabatan) }}" class="w-full p-2 border rounded mt-1">
                @error('jabatan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="text-sm font-semibold text-gray-700">Foto (jpg/png)</label>
            <input type="file" name="foto" accept="image/png,image/jpeg" class="w-full p-2 border rounded mt-1 bg-white">
            @error('foto') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            @if($profile?->foto)
                <p class="text-xs text-gray-500 mt-1">Foto saat ini tersimpan.</p>
            @endif
        </div>

        <div class="flex items-center gap-2">
            <button class="bg-blue-500 text-white px-4 py-2 rounded font-semibold">Simpan</button>
            <a href="{{ route('admin.biodata.index') }}" class="px-4 py-2 rounded border font-semibold">Batal</a>
        </div>
    </form>
</div>
@endsection

