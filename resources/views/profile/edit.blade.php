@extends('layouts.app')

@section('title', 'Edit Profil')

@section('content')
<div class="min-h-[100dvh] bg-gray-50">
    <div class="max-w-2xl mx-auto px-4 py-6">
        <div class="bg-white rounded-xl shadow-sm border p-6 space-y-5">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-bold text-gray-800">Edit Biodata</h2>
                    <p class="text-sm text-gray-500">Lengkapi biodata dan detail kepegawaian.</p>
                </div>
                <a href="{{ route('profile.index') }}" class="px-4 py-2 rounded-md border text-sm font-semibold hover:bg-gray-50">
                    Kembali
                </a>
            </div>

            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-semibold text-gray-700">Nama</label>
                        <input value="{{ $user->name }}" readonly class="mt-1 w-full border rounded-md px-3 py-2 text-sm bg-gray-50">
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-700">Email</label>
                        <input value="{{ $user->email }}" readonly class="mt-1 w-full border rounded-md px-3 py-2 text-sm bg-gray-50">
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-semibold text-gray-700">No. HP</label>
                        <input name="no_hp" value="{{ old('no_hp', $profile?->no_hp) }}" class="mt-1 w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('no_hp') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-700">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', optional($profile?->tanggal_lahir)->toDateString()) }}" class="mt-1 w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('tanggal_lahir') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-semibold text-gray-700">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="mt-1 w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- pilih --</option>
                            <option value="L" @selected(old('jenis_kelamin', $profile?->jenis_kelamin) === 'L')>L</option>
                            <option value="P" @selected(old('jenis_kelamin', $profile?->jenis_kelamin) === 'P')>P</option>
                        </select>
                        @error('jenis_kelamin') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-700">NIK (Opsional)</label>
                        <input name="nik" value="{{ old('nik', $profile?->nik) }}" class="mt-1 w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('nik') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="text-sm font-semibold text-gray-700">Alamat</label>
                    <textarea name="alamat" rows="3" class="mt-1 w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('alamat', $profile?->alamat) }}</textarea>
                    @error('alamat') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-semibold text-gray-700">NIP</label>
                        <input name="nip" value="{{ old('nip', $employeeDetail?->nip) }}" class="mt-1 w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('nip') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-700">Status Kerja</label>
                        <select name="status_kerja" class="mt-1 w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">-- pilih --</option>
                            <option value="tetap" @selected(old('status_kerja', $employeeDetail?->status_kerja) === 'tetap')>tetap</option>
                            <option value="kontrak" @selected(old('status_kerja', $employeeDetail?->status_kerja) === 'kontrak')>kontrak</option>
                            <option value="magang" @selected(old('status_kerja', $employeeDetail?->status_kerja) === 'magang')>magang</option>
                        </select>
                        @error('status_kerja') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="text-sm font-semibold text-gray-700">Departemen</label>
                        <input name="departemen" value="{{ old('departemen', $employeeDetail?->departemen) }}" class="mt-1 w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('departemen') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-700">Jabatan</label>
                        <input name="jabatan" value="{{ old('jabatan', $employeeDetail?->jabatan) }}" class="mt-1 w-full border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        @error('jabatan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="text-sm font-semibold text-gray-700">Upload Foto (jpg/png)</label>
                    <input type="file" name="foto" accept="image/png,image/jpeg" class="mt-1 w-full border rounded-md px-3 py-2 text-sm bg-white">
                    @error('foto') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    @if($profile?->foto)
                        <p class="text-xs text-gray-500 mt-1">Foto saat ini tersimpan.</p>
                    @endif
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <button class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-md text-sm font-semibold">
                        Simpan
                    </button>
                    <a href="{{ route('profile.index') }}" class="px-5 py-2 rounded-md border text-sm font-semibold hover:bg-gray-50">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

