@extends('layouts.app')

@section('title', 'Profil')

@section('content')
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

            @php($foto = $profile?->foto ? asset('storage/' . $profile->foto) : null)

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
                            {{ $employeeDetail?->unit?->nama_unit ?? $employeeDetail?->department?->nama_departemen ?? 'User' }}
                        </p>
                    </div>
                    @if(auth()->user()?->role === 'admin')
                        <a href="{{ route('profile.edit') }}" class="user-header-icon text-blue-700 bg-white/80 border border-white shadow-sm">
                            <i class="fa-solid fa-pen"></i>
                        </a>
                    @endif
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
                        <p class="text-[11px] text-slate-500">Departemen</p>
                        <p class="font-bold text-slate-800">{{ $employeeDetail?->department?->nama_departemen ?? $employeeDetail?->departemen ?? '-' }}</p>
                    </div>
                    <div class="user-soft-card">
                        <p class="text-[11px] text-slate-500">Unit</p>
                        <p class="font-bold text-slate-800">{{ $employeeDetail?->unit?->nama_unit ?? '-' }}</p>
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
@endsection
