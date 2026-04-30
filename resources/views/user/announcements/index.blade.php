@extends('layouts.app')

@section('title', 'Pengumuman')

@section('content')
<div class="min-h-screen bg-gray-50 py-6">
    <div class="max-w-4xl mx-auto px-4 space-y-4">
        <div>
            <h1 class="text-xl font-bold text-gray-800">Pengumuman</h1>
            <p class="text-sm text-gray-500">Informasi aktif untuk user dan unit Anda.</p>
        </div>
        @forelse($announcements as $announcement)
            <div class="bg-white rounded-xl shadow p-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h2 class="font-bold text-gray-800">{{ $announcement->judul }}</h2>
                        <p class="text-xs text-gray-500 mt-1">{{ $announcement->tanggal_mulai->format('d/m/Y') }} - {{ $announcement->tanggal_berakhir->format('d/m/Y') }}</p>
                    </div>
                    <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded">{{ $announcement->target_type === 'unit' ? ($announcement->unit?->nama_unit ?? 'Unit') : 'Semua User' }}</span>
                </div>
                <p class="text-sm text-gray-700 mt-3 whitespace-pre-line">{{ $announcement->isi }}</p>
            </div>
        @empty
            <div class="bg-white rounded-xl shadow p-6 text-gray-500">Belum ada pengumuman aktif.</div>
        @endforelse
    </div>
</div>
@endsection
