@extends('layouts.app')

@section('title', 'Pengumuman')

@section('content')
<div class="user-page">
    <div class="user-phone">
        @include('user.partials.header', [
            'title' => 'Pengumuman',
            'subtitle' => 'Informasi aktif untuk user dan unit Anda.',
        ])

        <main class="px-4 pt-4 space-y-3">
            @forelse($announcements as $announcement)
                <article class="user-card p-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="text-sm font-bold text-slate-800 leading-snug">{{ $announcement->judul }}</h2>
                            <p class="mt-1 text-[11px] text-slate-500">
                                {{ $announcement->tanggal_mulai->format('d/m/Y') }} - {{ $announcement->tanggal_berakhir->format('d/m/Y') }}
                            </p>
                        </div>
                        <span class="shrink-0 px-2.5 py-1 rounded-full bg-blue-100 text-blue-700 text-[10px] font-bold">
                            @if($announcement->target_type === 'users')
                                Khusus Anda
                            @elseif($announcement->target_type === 'unit')
                                {{ $announcement->unit?->nama_unit ?? 'Unit' }}
                            @else
                                Semua
                            @endif
                        </span>
                    </div>
                    <p class="mt-3 text-sm text-slate-600 whitespace-pre-line leading-relaxed">{{ $announcement->isi }}</p>
                </article>
            @empty
                <section class="user-card p-6 text-center">
                    <div class="w-12 h-12 mx-auto rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center">
                        <i class="fa-solid fa-bullhorn"></i>
                    </div>
                    <p class="mt-3 text-sm font-semibold text-slate-700">Belum ada pengumuman aktif.</p>
                    <p class="text-xs text-slate-500">Info terbaru akan muncul di sini.</p>
                </section>
            @endforelse
        </main>

        @include('user.partials.bottom-nav', ['active' => ''])
    </div>
</div>
@endsection
