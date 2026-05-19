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
                <div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-2xl p-4 text-sm shadow-sm">{{ session('success') }}</div>
            @endif

            <form method="GET" data-auto-filter class="user-card p-4 space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-[11px] font-semibold text-slate-500">Bulan</label>
                        <input type="month" name="month" value="{{ $month }}" class="user-field mt-1">
                    </div>
                    <div>
                        <label class="text-[11px] font-semibold text-slate-500">Jenis Shift</label>
                        <select name="shift_type" class="user-field mt-1">
                            <option value="">Semua Shift</option>
                            <option value="P" @selected(request('shift_type') === 'P')>Pagi</option>
                            <option value="S" @selected(request('shift_type') === 'S')>Sore</option>
                            <option value="M" @selected(request('shift_type') === 'M')>Malam</option>
                            <option value="O" @selected(request('shift_type') === 'O')>Libur</option>
                        </select>
                    </div>
                </div>
                @if(request()->has('shift_type'))
                    <a href="{{ route('user.shifts.index', ['month' => $month]) }}" class="user-btn-secondary w-full">Reset</a>
                @endif
            </form>

            <section class="grid grid-cols-2 gap-3">
                @foreach($calendar as $day)
                    @php
                        $item = $day['items']->first();
                        $isActive = $item && $item->status === 'aktif';
                    @endphp
                    <div class="rounded-2xl border p-3 min-h-[112px] shadow-sm {{ $day['is_today'] ? 'border-blue-300 bg-blue-50' : 'border-white/70 bg-white/85' }}">
                        <p class="text-xs font-bold {{ $day['is_today'] ? 'text-blue-700' : 'text-slate-700' }}">{{ $day['label'] }}</p>
                        @if($item)
                            <div class="mt-2 flex flex-wrap gap-1.5">
                                <span class="inline-flex px-2 py-1 rounded-full text-[10px] font-bold {{ $item->shift_type_badge_class }}">
                                    {{ $item->shift_type_label }}
                                </span>
                                <span class="inline-flex px-2 py-1 rounded-full text-[10px] font-bold {{ $isActive ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-700' }}">
                                    {{ strtoupper($item->status) }}
                                </span>
                            </div>
                            <p class="text-xs text-slate-500 mt-2">
                                {{ $item->status === 'libur' ? 'Tidak ada jam kerja' : (($item->jam_masuk?->format('H:i') ?? '00:00') . ' - ' . ($item->jam_pulang?->format('H:i') ?? '00:00')) }}
                            </p>
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
                    <a href="{{ route('shift-swaps.index') }}" class="text-xs font-bold text-red-600">Swap</a>
                </div>

                <div class="mt-3 space-y-3">
                    @forelse($schedules as $schedule)
                        <div class="user-soft-card flex items-center justify-between gap-3">
                            <div>
                                <p class="text-xs font-bold text-slate-800">{{ $schedule->tanggal->translatedFormat('l, d M Y') }}</p>
                                <p class="text-[11px] text-slate-500">
                                    {{ $schedule->status === 'libur' ? 'Tidak ada jam kerja' : (($schedule->jam_masuk?->format('H:i') ?? '00:00') . ' - ' . ($schedule->jam_pulang?->format('H:i') ?? '00:00')) }}
                                </p>
                            </div>
                            <div class="flex flex-col items-end gap-1.5">
                                <span class="px-2 py-1 rounded-full text-[10px] font-bold {{ $schedule->shift_type_badge_class }}">{{ $schedule->shift_type_label }}</span>
                                <span class="px-2 py-1 rounded-full text-[10px] font-bold {{ $schedule->status === 'aktif' ? 'bg-blue-100 text-blue-700' : 'bg-slate-100 text-slate-700' }}">{{ ucfirst($schedule->status) }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="py-4 text-center text-sm text-slate-500">Belum ada jadwal di bulan ini.</p>
                    @endforelse
                </div>
            </section>
        </main>

        @include('user.partials.bottom-nav', ['active' => 'schedule'])
    </div>
</div>
@endsection
