@extends('layouts.app')

@section('title', 'Jadwal Shift')

@section('content')
<div class="user-page">
    <div class="user-phone">
        @include('user.partials.header', [
            'title' => 'Jadwal Shift',
            'subtitle' => 'Jadwal pribadi bulan ini.',
        ])

        <main class="px-4 pt-4 space-y-4">
            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl p-4 text-sm shadow-sm">{{ session('success') }}</div>
            @endif

            <form method="GET" class="user-card p-4 flex items-end gap-3">
                <div class="flex-1">
                    <label class="text-[11px] font-semibold text-slate-500">Bulan</label>
                    <input type="month" name="month" value="{{ $month }}" class="user-field mt-1">
                </div>
                <button class="user-btn-primary px-3">
                    <i class="fa-solid fa-filter"></i>
                </button>
            </form>

            <section class="grid grid-cols-2 gap-3">
                @foreach($calendar as $day)
                    @php
                        $item = $day['items']->first();
                        $isActive = $item && $item->status === 'aktif';
                    @endphp
                    <div class="rounded-2xl border p-3 min-h-[112px] shadow-sm {{ $day['is_today'] ? 'border-emerald-300 bg-emerald-50' : 'border-white/70 bg-white/85' }}">
                        <p class="text-xs font-bold {{ $day['is_today'] ? 'text-emerald-700' : 'text-slate-700' }}">{{ $day['label'] }}</p>
                        @if($item)
                            <span class="inline-flex mt-2 px-2 py-1 rounded-full text-[10px] font-bold {{ $isActive ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                                {{ strtoupper($item->status) }}
                            </span>
                            <p class="text-xs text-slate-500 mt-2">{{ $item->jam_masuk?->format('H:i') ?? '00:00' }} - {{ $item->jam_pulang?->format('H:i') ?? '00:00' }}</p>
                        @else
                            <p class="mt-2 text-xs text-slate-400">Belum ada jadwal</p>
                        @endif
                    </div>
                @endforeach
            </section>

            <section class="user-card p-4">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-sm font-bold text-slate-800">List Bulan Ini</h2>
                        <p class="text-[11px] text-slate-500">Ringkasan jadwal aktif.</p>
                    </div>
                    <a href="{{ route('shift-swaps.index') }}" class="text-xs font-bold text-emerald-700">Swap</a>
                </div>

                <div class="mt-3 space-y-3">
                    @forelse($schedules as $schedule)
                        <div class="user-soft-card flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-bold text-slate-800">{{ $schedule->tanggal->translatedFormat('l, d M Y') }}</p>
                                <p class="text-[11px] text-slate-500">{{ $schedule->jam_masuk?->format('H:i') ?? '00:00' }} - {{ $schedule->jam_pulang?->format('H:i') ?? '00:00' }}</p>
                            </div>
                            <span class="px-2 py-1 rounded-full text-[10px] font-bold {{ $schedule->status === 'aktif' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">{{ ucfirst($schedule->status) }}</span>
                        </div>
                    @empty
                        <p class="py-4 text-center text-sm text-slate-500">Belum ada jadwal di bulan ini.</p>
                    @endforelse
                </div>
            </section>
        </main>

        @include('user.partials.bottom-nav', ['active' => ''])
    </div>
</div>
@endsection
