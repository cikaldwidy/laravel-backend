@extends('layouts.app')

@section('title', 'Jadwal Shift')

@section('content')
<div class="min-h-dvh bg-slate-50 py-4">
    <div class="max-w-5xl mx-auto px-4 space-y-4">
        @if(session('success'))
            <div class="rounded-md bg-emerald-100 border border-emerald-200 text-emerald-700 px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif

        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-lg font-bold text-slate-800">Jadwal Shift Saya</h1>
                    <p class="text-sm text-slate-500">Tampilan kalender/list untuk jadwal pribadi.</p>
                </div>
                <form method="GET" class="flex items-center gap-2">
                    <input type="month" name="month" value="{{ $month }}" class="border rounded-md px-3 py-2 text-sm">
                    <button class="bg-emerald-600 text-white rounded-md px-4 py-2 text-sm font-semibold">Filter</button>
                </form>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3">
            @foreach($calendar as $day)
                @php
                    $item = $day['items']->first();
                    $isActive = $item && $item->status === 'aktif';
                @endphp
                <div class="rounded-xl border {{ $day['is_today'] ? 'border-emerald-400 bg-emerald-50' : 'border-slate-200 bg-white' }} p-3 min-h-[112px]">
                    <p class="text-xs font-semibold {{ $day['is_today'] ? 'text-emerald-700' : 'text-slate-700' }}">{{ $day['label'] }}</p>
                    @if($item)
                        <p class="mt-2 text-xs font-semibold {{ $isActive ? 'text-emerald-700' : 'text-slate-700' }}">{{ strtoupper($item->status) }}</p>
                        <p class="text-xs text-slate-500 mt-1">{{ $item->jam_masuk?->format('H:i') ?? '00:00' }} - {{ $item->jam_pulang?->format('H:i') ?? '00:00' }}</p>
                    @else
                        <p class="mt-2 text-xs text-slate-400">Belum ada jadwal</p>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm overflow-x-auto">
            <h2 class="text-sm font-bold text-slate-700 mb-3">List Jadwal Bulan Ini</h2>
            <table class="min-w-full text-sm">
                <thead>
                    <tr class="text-left text-xs uppercase text-slate-500 border-b">
                        <th class="py-2 pr-3">Tanggal</th>
                        <th class="py-2 pr-3">Jam</th>
                        <th class="py-2 pr-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($schedules as $schedule)
                        <tr class="border-b border-slate-100">
                            <td class="py-2 pr-3 text-slate-700">{{ $schedule->tanggal->translatedFormat('l, d M Y') }}</td>
                            <td class="py-2 pr-3 text-slate-600">{{ $schedule->jam_masuk?->format('H:i') ?? '00:00' }} - {{ $schedule->jam_pulang?->format('H:i') ?? '00:00' }}</td>
                            <td class="py-2 pr-3">
                                <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $schedule->status === 'aktif' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">{{ ucfirst($schedule->status) }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-6 text-center text-slate-400">Belum ada jadwal di bulan ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
