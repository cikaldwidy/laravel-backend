@extends('layouts.app')

@section('title', 'Riwayat Presensi')

@section('content')
<div class="user-page">
    <div class="user-phone">
        @include('user.partials.header', [
            'title' => 'Riwayat Presensi',
            'subtitle' => 'Filter dan pantau catatan presensi pribadi.',
        ])

        <main class="px-4 pt-4 space-y-4">
            <form method="GET" class="user-card p-4 space-y-3">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-[11px] font-semibold text-slate-500">Dari</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="user-field mt-1">
                    </div>
                    <div>
                        <label class="text-[11px] font-semibold text-slate-500">Sampai</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="user-field mt-1">
                    </div>
                </div>
                <button class="user-btn-primary w-full">
                    <i class="fa-solid fa-filter"></i>
                    Filter
                </button>
            </form>

            <section class="space-y-3">
                @forelse($histories as $item)
                    @php
                        $isLate = in_array($item->status, ['telat', 'terlambat'], true);
                        $badgeClass = $isLate ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700';
                    @endphp
                    <article class="user-card p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
                                    <i class="fa-solid fa-fingerprint"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-800">{{ $item->tanggal->translatedFormat('d F Y') }}</p>
                                    <p class="text-xs text-slate-500">{{ $item->jam_masuk?->format('H:i') ?? '-' }} - {{ $item->jam_keluar?->format('H:i') ?? '-' }}</p>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $badgeClass }}">
                                {{ ucfirst($item->status ?? '-') }}
                            </span>
                        </div>
                    </article>
                @empty
                    <section class="user-card p-6 text-center">
                        <div class="w-12 h-12 mx-auto rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
                            <i class="fa-solid fa-file-lines"></i>
                        </div>
                        <p class="mt-3 text-sm font-semibold text-slate-700">Belum ada riwayat.</p>
                        <p class="text-xs text-slate-500">Data presensi akan muncul setelah kamu absen.</p>
                    </section>
                @endforelse
            </section>
        </main>

        @include('user.partials.bottom-nav', ['active' => 'history'])
    </div>
</div>
@endsection
