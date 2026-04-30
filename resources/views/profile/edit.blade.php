@extends('layouts.app')

@section('title', 'Edit Profil')

@section('content')
<div class="user-page">
    <div class="user-phone">
        @include('user.partials.header', [
            'title' => 'Edit Biodata',
            'subtitle' => 'Lengkapi biodata dan detail kepegawaian.',
            'back' => route('profile.index'),
        ])

        <main class="px-4 pt-4">
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="user-card p-4 space-y-4">
                @csrf

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Nama</label>
                        <input value="{{ $user->name }}" readonly class="user-field mt-1 bg-slate-50">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Email</label>
                        <input value="{{ $user->email }}" readonly class="user-field mt-1 bg-slate-50">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-slate-600">No. HP</label>
                        <input name="no_hp" value="{{ old('no_hp', $profile?->no_hp) }}" class="user-field mt-1">
                        @error('no_hp') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', optional($profile?->tanggal_lahir)->toDateString()) }}" class="user-field mt-1">
                        @error('tanggal_lahir') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="user-field mt-1">
                            <option value="">-- pilih --</option>
                            <option value="L" @selected(old('jenis_kelamin', $profile?->jenis_kelamin) === 'L')>L</option>
                            <option value="P" @selected(old('jenis_kelamin', $profile?->jenis_kelamin) === 'P')>P</option>
                        </select>
                        @error('jenis_kelamin') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600">NIK (Opsional)</label>
                        <input name="nik" value="{{ old('nik', $profile?->nik) }}" class="user-field mt-1">
                        @error('nik') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-600">Alamat</label>
                    <textarea name="alamat" rows="3" class="user-field mt-1">{{ old('alamat', $profile?->alamat) }}</textarea>
                    @error('alamat') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-slate-600">NIP</label>
                        <input name="nip" value="{{ old('nip', $employeeDetail?->nip) }}" class="user-field mt-1">
                        @error('nip') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Status Kerja</label>
                        <select name="status_kerja" class="user-field mt-1">
                            <option value="">-- pilih --</option>
                            <option value="tetap" @selected(old('status_kerja', $employeeDetail?->status_kerja) === 'tetap')>tetap</option>
                            <option value="kontrak" @selected(old('status_kerja', $employeeDetail?->status_kerja) === 'kontrak')>kontrak</option>
                            <option value="magang" @selected(old('status_kerja', $employeeDetail?->status_kerja) === 'magang')>magang</option>
                        </select>
                        @error('status_kerja') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Departemen</label>
                        <input name="departemen" value="{{ old('departemen', $employeeDetail?->departemen) }}" class="user-field mt-1">
                        @error('departemen') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Jabatan</label>
                        <input name="jabatan" value="{{ old('jabatan', $employeeDetail?->jabatan) }}" class="user-field mt-1">
                        @error('jabatan') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-600">Upload Foto (jpg/png)</label>
                    <input type="file" name="foto" accept="image/png,image/jpeg" class="user-field mt-1">
                    @error('foto') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    @if($profile?->foto)
                        <p class="text-xs text-gray-500 mt-1">Foto saat ini tersimpan.</p>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-3 pt-2">
                    <a href="{{ route('profile.index') }}" class="user-btn-secondary">
                        Batal
                    </a>
                    <button class="user-btn-primary">
                        Simpan
                    </button>
                </div>
            </form>
        </main>

        @include('user.partials.bottom-nav', ['active' => ''])
    </div>
</div>
@endsection
