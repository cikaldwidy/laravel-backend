@extends('layouts.app')

@section('title', 'Profil')

@section('content')
@php
    $foto = $profile?->foto ? asset('storage/' . $profile->foto) : null;
    $showEditModal = $errors->any() || request()->boolean('edit');
@endphp
<div class="user-page">
    <div class="user-phone">
        @include('user.partials.header', [
            'title' => 'ID Card',
            'subtitle' => 'Biodata dan detail kepegawaian.',
            'back' => route('dashboard'),
        ])

        <main class="px-4 pt-4 space-y-4">
            @if(session('success'))
                <div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-2xl p-4 text-sm shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            <section class="user-card p-4">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-blue-50 overflow-hidden flex items-center justify-center border border-white shadow-sm">
                        @if($foto)
                            <img src="{{ $foto }}" alt="Foto" class="w-full h-full object-cover">
                        @else
                            <i class="fa-solid fa-user text-blue-700 text-2xl"></i>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="font-extrabold text-slate-800 truncate">{{ $user->name }}</p>
                        <p class="text-xs text-slate-500 truncate">{{ $user->email }}</p>
                        <p class="mt-2 inline-flex px-2.5 py-1 rounded-full bg-blue-100 text-blue-700 text-[11px] font-bold">
                            {{ $employeeDetail?->department?->nama_departemen ?? $employeeDetail?->departemen ?? 'User' }}
                        </p>
                    </div>
                    <button
                        type="button"
                        data-modal-open="profile-edit-modal"
                        class="inline-flex items-center gap-2 rounded-xl bg-blue-600 px-3 py-2 text-xs font-bold text-white shadow-lg shadow-blue-700/20"
                    >
                        <i class="fa-solid fa-pen-to-square text-[11px]"></i>
                        <span>Edit</span>
                    </button>
                </div>
            </section>

            <section class="user-card p-4 space-y-3">
                <h2 class="text-sm font-bold text-slate-800">Biodata</h2>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div class="user-soft-card">
                        <p class="text-[11px] text-slate-500">No. HP</p>
                        <p class="font-bold text-slate-800">{{ $profile?->no_hp ?? '-' }}</p>
                    </div>
                    <div class="user-soft-card">
                        <p class="text-[11px] text-slate-500">Jenis Kelamin</p>
                        <p class="font-bold text-slate-800">{{ $profile?->jenis_kelamin ?? '-' }}</p>
                    </div>
                    <div class="user-soft-card">
                        <p class="text-[11px] text-slate-500">Tanggal Lahir</p>
                        <p class="font-bold text-slate-800">{{ $profile?->tanggal_lahir?->format('d/m/Y') ?? '-' }}</p>
                    </div>
                    <div class="user-soft-card">
                        <p class="text-[11px] text-slate-500">NIK</p>
                        <p class="font-bold text-slate-800">{{ $profile?->nik ?? '-' }}</p>
                    </div>
                    <div class="user-soft-card col-span-2">
                        <p class="text-[11px] text-slate-500">Alamat</p>
                        <p class="font-bold text-slate-800 whitespace-pre-line">{{ $profile?->alamat ?? '-' }}</p>
                    </div>
                </div>
            </section>

            <section class="user-card p-4 space-y-3">
                <h2 class="text-sm font-bold text-slate-800">Kepegawaian</h2>
                <div class="grid grid-cols-2 gap-3 text-sm">
                    <div class="user-soft-card">
                        <p class="text-[11px] text-slate-500">NIP</p>
                        <p class="font-bold text-slate-800">{{ $employeeDetail?->nip ?? '-' }}</p>
                    </div>
                    <div class="user-soft-card">
                        <p class="text-[11px] text-slate-500">Status Kerja</p>
                        <p class="font-bold text-slate-800">{{ $employeeDetail?->status_kerja ?? '-' }}</p>
                    </div>
                    <div class="user-soft-card">
                        <p class="text-[11px] text-slate-500">Unit Kerja/Bagian</p>
                        <p class="font-bold text-slate-800">{{ $employeeDetail?->department?->nama_departemen ?? $employeeDetail?->departemen ?? '-' }}</p>
                    </div>
                    <div class="user-soft-card">
                        <p class="text-[11px] text-slate-500">Jabatan</p>
                        <p class="font-bold text-slate-800">{{ $employeeDetail?->position?->nama_jabatan ?? $employeeDetail?->jabatan ?? '-' }}</p>
                    </div>
                </div>
            </section>
        </main>

        @include('user.partials.bottom-nav', ['active' => 'profile'])
    </div>
</div>

<div
    id="profile-edit-modal"
    class="{{ $showEditModal ? 'flex' : 'hidden' }} fixed inset-0 z-[60] items-end justify-center bg-slate-950/45 px-4 py-6 sm:items-center"
>
    <div class="absolute inset-0" data-modal-close="profile-edit-modal"></div>

    <div class="relative z-10 w-full max-w-2xl overflow-hidden rounded-[1.75rem] bg-white shadow-2xl">
        <div class="flex items-start justify-between border-b border-slate-100 px-5 py-4">
            <div>
                <p class="text-base font-extrabold text-slate-800">Edit Biodata</p>
                <p class="mt-1 text-sm text-slate-500">Perbarui data pribadi Anda di bawah ini.</p>
            </div>
            <button
                type="button"
                data-modal-close="profile-edit-modal"
                class="inline-flex h-10 w-10 items-center justify-center rounded-full bg-slate-100 text-slate-500"
                aria-label="Tutup popup edit biodata"
            >
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>

        <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="max-h-[85vh] overflow-y-auto px-5 py-5">
            @csrf

            <div class="space-y-4">
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Nama</label>
                        <input value="{{ $user->name }}" readonly class="user-field mt-1 bg-slate-50">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Email</label>
                        <input value="{{ $user->email }}" readonly class="user-field mt-1 bg-slate-50">
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="text-xs font-semibold text-slate-600">No. HP</label>
                        <input name="no_hp" value="{{ old('no_hp', $profile?->no_hp) }}" class="user-field mt-1">
                        @error('no_hp') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', optional($profile?->tanggal_lahir)->toDateString()) }}" class="user-field mt-1">
                        @error('tanggal_lahir') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="user-field mt-1">
                            <option value="">-- pilih --</option>
                            <option value="L" @selected(old('jenis_kelamin', $profile?->jenis_kelamin) === 'L')>Laki-laki</option>
                            <option value="P" @selected(old('jenis_kelamin', $profile?->jenis_kelamin) === 'P')>Perempuan</option>
                        </select>
                        @error('jenis_kelamin') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600">NIK</label>
                        <input name="nik" value="{{ old('nik', $profile?->nik) }}" class="user-field mt-1">
                        @error('nik') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-600">Alamat</label>
                    <textarea name="alamat" rows="3" class="user-field mt-1">{{ old('alamat', $profile?->alamat) }}</textarea>
                    @error('alamat') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div class="user-soft-card">
                        <p class="text-[11px] text-slate-500">NIP</p>
                        <p class="mt-1 font-bold text-slate-800">{{ $employeeDetail?->nip ?? '-' }}</p>
                    </div>
                    <div class="user-soft-card">
                        <p class="text-[11px] text-slate-500">Status Kerja</p>
                        <p class="mt-1 font-bold capitalize text-slate-800">{{ $employeeDetail?->status_kerja ?? '-' }}</p>
                    </div>
                    <div class="user-soft-card">
                        <p class="text-[11px] text-slate-500">Unit Kerja/Bagian</p>
                        <p class="mt-1 font-bold text-slate-800">{{ $employeeDetail?->department?->nama_departemen ?? $employeeDetail?->departemen ?? '-' }}</p>
                    </div>
                    <div class="user-soft-card">
                        <p class="text-[11px] text-slate-500">Jabatan</p>
                        <p class="mt-1 font-bold text-slate-800">{{ $employeeDetail?->position?->nama_jabatan ?? $employeeDetail?->jabatan ?? '-' }}</p>
                    </div>
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-600">Foto Profil (jpg/png)</label>
                    <input type="file" name="foto" accept="image/png,image/jpeg" class="user-field mt-1">
                    @error('foto') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    @if($profile?->foto)
                        <p class="mt-1 text-xs text-slate-500">Upload foto baru jika ingin mengganti foto saat ini.</p>
                    @endif
                </div>
            </div>

            <div class="mt-5 flex flex-col-reverse gap-3 border-t border-slate-100 pt-4 sm:flex-row sm:justify-end">
                <button
                    type="button"
                    data-modal-close="profile-edit-modal"
                    class="inline-flex items-center justify-center rounded-xl border border-slate-200 px-4 py-3 text-sm font-semibold text-slate-600"
                >
                    Batal
                </button>
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-blue-600 px-4 py-3 text-sm font-semibold text-white shadow-lg shadow-blue-700/20">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    const modalId = 'profile-edit-modal';
    const modal = document.getElementById(modalId);

    const toggleModal = (shouldOpen) => {
        if (!modal) return;

        modal.classList.toggle('hidden', !shouldOpen);
        modal.classList.toggle('flex', shouldOpen);
        document.body.classList.toggle('overflow-hidden', shouldOpen);
    };

    document.querySelectorAll('[data-modal-open="' + modalId + '"]').forEach((button) => {
        button.addEventListener('click', () => toggleModal(true));
    });

    document.querySelectorAll('[data-modal-close="' + modalId + '"]').forEach((button) => {
        button.addEventListener('click', () => toggleModal(false));
    });

    if (modal && !modal.classList.contains('hidden')) {
        document.body.classList.add('overflow-hidden');
    }
</script>
@endsection
