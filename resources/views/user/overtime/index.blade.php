@extends('layouts.app')

@section('title', 'Lembur')

@section('content')
@php
    $compensationLabels = [
        'uang' => 'Uang Lembur',
        'libur_pengganti' => 'Libur Pengganti',
    ];

    $statusLabels = [
        'approved' => 'Disetujui',
        'cancelled' => 'Dibatalkan',
        'done' => 'Selesai',
    ];
@endphp

<div class="user-page">
    <div class="user-phone bg-gradient-to-b from-amber-50 via-white to-emerald-50">
        <header class="bg-amber-600 px-4 text-white shadow-md shadow-amber-900/10" style="padding-top: calc(0.85rem + env(safe-area-inset-top)); padding-bottom: 0.85rem;">
            <div class="relative flex items-center justify-center">
                <a href="{{ route('dashboard') }}" class="absolute left-0 flex h-9 w-9 items-center justify-center rounded-lg bg-white/10 text-white">
                    <i class="fa-solid fa-chevron-left text-sm"></i>
                </a>
                <h1 class="text-sm font-extrabold tracking-wide">Lembur</h1>
            </div>
        </header>

        <main class="space-y-3 px-4 pt-4">
            @forelse($items as $item)
                @php
                    $days = $item->tanggal_mulai->diffInDays($item->tanggal_selesai) + 1;
                    $compensation = $compensationLabels[$item->compensation_type] ?? ucfirst(str_replace('_', ' ', $item->compensation_type));
                    $status = $statusLabels[$item->status] ?? ucfirst($item->status);
                @endphp
                <article class="rounded-xl border border-amber-100 bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-sm font-extrabold text-slate-800">{{ $compensation }}</p>
                            <p class="mt-1 text-xs font-semibold text-slate-500">
                                {{ $item->tanggal_mulai->format('d M Y') }} - {{ $item->tanggal_selesai->format('d M Y') }}
                                <span class="text-slate-700">{{ $days }} hari</span>
                            </p>
                        </div>
                        <span class="rounded-full bg-amber-50 px-2.5 py-1 text-[10px] font-extrabold text-amber-700">{{ $status }}</span>
                    </div>

                    <div class="mt-3 rounded-xl bg-slate-50 p-3 text-xs text-slate-600">
                        <p class="font-bold text-slate-700">Sumber</p>
                        <p class="mt-1">{{ $item->keterangan ?: 'Lembur pengganti' }}</p>
                        @if($item->sourceUser)
                            <p class="mt-1">Menggantikan: <span class="font-bold">{{ $item->sourceUser->name }}</span></p>
                        @endif
                        @if($item->jam_mulai && $item->jam_selesai)
                            <p class="mt-1">Jam: {{ $item->jam_mulai->format('H:i') }} - {{ $item->jam_selesai->format('H:i') }}</p>
                        @endif
                    </div>
                </article>
            @empty
                <section class="rounded-xl border border-white/80 bg-white/85 p-6 text-center shadow-sm">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-amber-50 text-amber-700">
                        <i class="fa-solid fa-clock"></i>
                    </div>
                    <p class="mt-3 text-sm font-semibold text-slate-700">Belum ada data lembur.</p>
                    <p class="mt-1 text-xs text-slate-500">Lembur pengganti sakit akan muncul setelah disetujui admin.</p>
                </section>
            @endforelse
        </main>

        @include('user.partials.bottom-nav', ['active' => ''])
    </div>
</div>
@endsection
