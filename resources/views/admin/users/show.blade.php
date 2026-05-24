@extends('layouts.admin')

@section('title', 'Detail Pegawai')

@section('content')
@php
    $profile = $user->userProfile;
    $detail = $user->employeeDetail;
    $hasProfile = (bool) $profile;
    $hasDetail = (bool) $detail;
    $biodataComplete = $hasProfile && $hasDetail;
    $gender = $profile?->jenis_kelamin === 'L' ? 'Laki-laki' : ($profile?->jenis_kelamin === 'P' ? 'Perempuan' : '-');
@endphp

<style>
    .profile-field {
        min-height: 2.75rem;
        width: 100%;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        background: #f9fafb;
        padding: 0.75rem 0.875rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: #1f2937;
    }

    .profile-label {
        margin-bottom: 0.4rem;
        display: block;
        font-size: 0.75rem;
        font-weight: 700;
        color: #6b7280;
    }
</style>

<div class="space-y-4">
    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-500 transition hover:text-blue-700">
        <i class="fas fa-arrow-left text-xs"></i>
        Kembali ke Akun Pegawai
    </a>

    <section class="overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-md bg-blue-50 text-blue-700">
                    <i class="fas fa-user"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-gray-800">Profil Pegawai</h1>
                    <p class="text-xs text-gray-500">Akun dan biodata dalam satu halaman.</p>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.users.edit', $user->id) }}" class="inline-flex items-center gap-2 rounded-md border border-blue-200 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700 transition hover:bg-blue-100">
                    <i class="fas fa-pen text-[10px]"></i> Edit Akun
                </a>
                <a href="{{ route('admin.biodata.edit', $user) }}" class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-700">
                    <i class="fas fa-id-card text-[10px]"></i> Edit Biodata
                </a>
            </div>
        </div>

        <div class="grid xl:grid-cols-[17rem_1fr]">
            <aside class="border-b border-gray-100 bg-gray-50/60 p-5 xl:border-b-0 xl:border-r">
                <div class="rounded-md border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="aspect-square overflow-hidden rounded-md bg-blue-50">
                        @if($profile?->foto)
                            <img src="{{ asset('storage/' . $profile->foto) }}" alt="{{ $user->name }}" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full w-full items-center justify-center text-5xl font-bold text-blue-700">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <div class="mt-4 text-center">
                        <h2 class="text-base font-bold text-gray-800">{{ $user->name }}</h2>
                        <p class="mt-1 text-sm text-gray-500">{{ $user->email }}</p>
                    </div>
                </div>

                <div class="mt-4 space-y-3">
                    <div class="rounded-md border border-gray-200 bg-white p-4 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Status</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $biodataComplete ? 'bg-green-50 text-green-700' : (($hasProfile || $hasDetail) ? 'bg-yellow-50 text-yellow-700' : 'bg-gray-100 text-gray-500') }}">
                                Biodata {{ $biodataComplete ? 'Lengkap' : (($hasProfile || $hasDetail) ? 'Sebagian' : 'Belum') }}
                            </span>
                            <span class="inline-flex rounded-full px-3 py-1 text-xs font-semibold {{ $user->faceEmbedding ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                Wajah {{ $user->faceEmbedding ? 'Terdaftar' : 'Belum' }}
                            </span>
                        </div>
                    </div>

                    <div class="rounded-md border border-gray-200 bg-white p-4 shadow-sm">
                        <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Pekerjaan</p>
                        <p class="mt-3 text-sm font-bold text-gray-800">{{ $detail?->position?->nama_jabatan ?? $detail?->jabatan ?? '-' }}</p>
                        <p class="mt-1 text-xs text-gray-500">{{ $detail?->department?->nama_departemen ?? $detail?->departemen ?? '-' }}</p>
                    </div>
                </div>
            </aside>

            <main class="p-5 lg:p-6">
                <div class="grid gap-8 2xl:grid-cols-2">
                    <section>
                        <h3 class="border-b border-gray-100 pb-3 text-sm font-bold text-gray-700">Informasi Akun</h3>
                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="profile-label">Nama</label>
                                <div class="profile-field">{{ $user->name }}</div>
                            </div>
                            <div>
                                <label class="profile-label">Username</label>
                                <div class="profile-field">{{ $user->username ?: '-' }}</div>
                            </div>
                            <div>
                                <label class="profile-label">Email</label>
                                <div class="profile-field">{{ $user->email }}</div>
                            </div>
                            <div>
                                <label class="profile-label">Role</label>
                                <div class="profile-field">Pegawai</div>
                            </div>
                        </div>
                    </section>

                    <section>
                        <h3 class="border-b border-gray-100 pb-3 text-sm font-bold text-gray-700">Kontak & Biodata</h3>
                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="profile-label">No. HP</label>
                                <div class="profile-field">{{ $profile?->no_hp ?? '-' }}</div>
                            </div>
                            <div>
                                <label class="profile-label">NIK</label>
                                <div class="profile-field">{{ $profile?->nik ?? '-' }}</div>
                            </div>
                            <div>
                                <label class="profile-label">Jenis Kelamin</label>
                                <div class="profile-field">{{ $gender }}</div>
                            </div>
                            <div>
                                <label class="profile-label">Tanggal Lahir</label>
                                <div class="profile-field">{{ $profile?->tanggal_lahir?->format('d/m/Y') ?? '-' }}</div>
                            </div>
                            <div>
                                <label class="profile-label">Agama</label>
                                <div class="profile-field">{{ $profile?->agama ?? '-' }}</div>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="mt-8 grid gap-8 2xl:grid-cols-2">
                    <section>
                        <h3 class="border-b border-gray-100 pb-3 text-sm font-bold text-gray-700">Detail Pekerjaan</h3>
                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="profile-label">NIP</label>
                                <div class="profile-field">{{ $detail?->nip ?? '-' }}</div>
                            </div>
                            <div>
                                <label class="profile-label">Status Kerja</label>
                                <div class="profile-field">{{ $detail?->status_kerja ? ucfirst($detail->status_kerja) : '-' }}</div>
                            </div>
                            <div>
                                <label class="profile-label">Unit Kerja/Bagian</label>
                                <div class="profile-field">{{ $detail?->department?->nama_departemen ?? $detail?->departemen ?? '-' }}</div>
                            </div>
                            <div>
                                <label class="profile-label">Jabatan</label>
                                <div class="profile-field">{{ $detail?->position?->nama_jabatan ?? $detail?->jabatan ?? '-' }}</div>
                            </div>
                        </div>
                    </section>

                    <section>
                        <h3 class="border-b border-gray-100 pb-3 text-sm font-bold text-gray-700">Alamat</h3>
                        <div class="profile-field mt-4 min-h-[8.25rem] leading-6">
                            {{ $profile?->alamat ?? '-' }}
                        </div>
                    </section>
                </div>
            </main>
        </div>
    </section>
</div>
@endsection
