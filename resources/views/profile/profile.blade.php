@extends('layouts.app')

@section('title', 'Profil')

@section('content')
<div class="min-h-[100dvh] bg-gray-50">
    <div class="max-w-2xl mx-auto px-4 py-6 space-y-4">
        @if(session('success'))
            <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-xl shadow-sm border p-5 flex items-center justify-between gap-4">
            <div class="flex items-center gap-4">
                @php
                    $foto = $profile?->foto ? asset('storage/' . $profile->foto) : null;
                @endphp
                <div class="w-16 h-16 rounded-full bg-gray-100 overflow-hidden flex items-center justify-center border">
                    @if($foto)
                        <img src="{{ $foto }}" alt="Foto" class="w-full h-full object-cover">
                    @else
                        <i class="fa-solid fa-user text-gray-400 text-2xl"></i>
                    @endif
                </div>
                <div>
                    <p class="font-bold text-gray-800">{{ $user->name }}</p>
                    <p class="text-sm text-gray-500">{{ $user->email }}</p>
                </div>
            </div>
            @if(auth()->user()?->role === 'admin')
                <a href="{{ route('profile.edit') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-semibold">
                    Edit
                </a>
            @endif
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
            <h2 class="font-bold text-gray-800">Biodata</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-500">No. HP</p>
                    <p class="font-semibold text-gray-800">{{ $profile?->no_hp ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Jenis Kelamin</p>
                    <p class="font-semibold text-gray-800">{{ $profile?->jenis_kelamin ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Tanggal Lahir</p>
                    <p class="font-semibold text-gray-800">{{ $profile?->tanggal_lahir?->format('d/m/Y') ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">NIK</p>
                    <p class="font-semibold text-gray-800">{{ $profile?->nik ?? '-' }}</p>
                </div>
                <div class="sm:col-span-2">
                    <p class="text-gray-500">Alamat</p>
                    <p class="font-semibold text-gray-800 whitespace-pre-line">{{ $profile?->alamat ?? '-' }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border p-5 space-y-4">
            <h2 class="font-bold text-gray-800">Kepegawaian</h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-500">NIP</p>
                    <p class="font-semibold text-gray-800">{{ $employeeDetail?->nip ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Status Kerja</p>
                    <p class="font-semibold text-gray-800">{{ $employeeDetail?->status_kerja ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Departemen</p>
                    <p class="font-semibold text-gray-800">{{ $employeeDetail?->departemen ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Unit</p>
                    <p class="font-semibold text-gray-800">{{ $employeeDetail?->unit?->nama_unit ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Jabatan</p>
                    <p class="font-semibold text-gray-800">{{ $employeeDetail?->jabatan ?? '-' }}</p>
                </div>
            </div>
        </div>

        <div class="text-center text-xs text-gray-400 pt-2">
            RS Biodata Module
        </div>
    </div>
 </div>
@endsection
