@extends('layouts.app')

@section('title', 'Status Tukar Shift')

@section('content')
<div class="user-page">
    <div class="user-phone">
        @include('user.partials.header', [
            'title' => 'Tukar Shift',
            'subtitle' => 'Pantau status request tukar shift.',
            'action' => new \Illuminate\Support\HtmlString('<a href="' . route('shift-swaps.create') . '" class="user-header-icon text-blue-700 bg-white/80 border border-white/60 shadow-sm"><i class="fa-solid fa-plus"></i></a>'),
        ])

        <main class="px-4 pt-4 space-y-4">
            @if(session('success'))
                <div class="bg-blue-50 border border-blue-200 text-blue-800 rounded-2xl p-4 text-sm shadow-sm">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 rounded-2xl p-4 text-sm shadow-sm">{{ session('error') }}</div>
            @endif

            <form method="GET" data-auto-filter class="user-card p-4 space-y-3">
                <div>
                    <label class="text-[11px] font-semibold text-slate-500">Cari</label>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari nama pegawai atau catatan..." class="user-field mt-1">
                </div>
                <div>
                    <label class="text-[11px] font-semibold text-slate-500">Status</label>
                    <select name="status" class="user-field mt-1">
                        <option value="">Semua</option>
                        <option value="pending" @selected($status === 'pending')>Pending</option>
                        <option value="approved" @selected($status === 'approved')>Approved</option>
                        <option value="rejected" @selected($status === 'rejected')>Rejected</option>
                    </select>
                </div>
                @if(request()->hasAny(['search', 'status']))
                    <a href="{{ route('shift-swaps.index') }}" class="user-btn-secondary w-full">Reset</a>
                @endif
            </form>

            <section class="space-y-3">
                @forelse($swaps as $swap)
                    @php
                        $statusClass = [
                            'pending' => 'bg-amber-100 text-amber-700',
                            'approved' => 'bg-blue-100 text-blue-700',
                            'rejected' => 'bg-red-100 text-red-700',
                        ][$swap->status] ?? 'bg-slate-100 text-slate-700';
                    @endphp
                    <article class="user-card p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-bold text-slate-800">{{ $swap->requester->name ?? '-' }} <span class="text-red-600">ke</span> {{ $swap->targetUser->name ?? '-' }}</p>
                                <p class="text-xs text-slate-500 mt-1">{{ $swap->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $statusClass }}">{{ strtoupper($swap->status) }}</span>
                        </div>

                        <div class="mt-3 grid grid-cols-2 gap-3 text-xs text-slate-600">
                            <div class="user-soft-card">
                                <p class="text-[11px] text-slate-500">Shift Saya</p>
                                @if($swap->shift)
                                    <p class="font-bold text-slate-800">{{ $swap->shift->tanggal->format('d/m/Y') }}</p>
                                    <p>{{ $swap->shift->jam_masuk?->format('H:i') }} - {{ $swap->shift->jam_pulang?->format('H:i') }}</p>
                                @else
                                    <p>-</p>
                                @endif
                            </div>
                            <div class="user-soft-card">
                                <p class="text-[11px] text-slate-500">Shift Target</p>
                                @if($swap->targetShift)
                                    <p class="font-bold text-slate-800">{{ $swap->targetShift->tanggal->format('d/m/Y') }}</p>
                                    <p>{{ $swap->targetShift->jam_masuk?->format('H:i') }} - {{ $swap->targetShift->jam_pulang?->format('H:i') }}</p>
                                @else
                                    <p>-</p>
                                @endif
                            </div>
                        </div>

                        @if($swap->note)
                            <div class="mt-3 user-soft-card text-xs text-slate-600 whitespace-pre-line">{{ $swap->note }}</div>
                        @endif

                        @if($swap->status === 'pending' && (int) $swap->target_user_id === (int) auth()->id())
                            <div class="mt-3 grid grid-cols-2 gap-2">
                                <form action="{{ route('shift-swaps.accept', $swap) }}" method="POST">
                                    @csrf
                                    <button class="w-full user-btn-primary py-2">Accept</button>
                                </form>
                                <form action="{{ route('shift-swaps.reject', $swap) }}" method="POST" onsubmit="return confirm('Tolak request ini?')">
                                    @csrf
                                    <button class="w-full rounded-xl bg-red-50 text-red-700 px-4 py-2 text-sm font-bold">Reject</button>
                                </form>
                            </div>
                        @endif
                    </article>
                @empty
                    <section class="user-card p-6 text-center">
                        <div class="w-12 h-12 mx-auto rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center">
                            <i class="fa-solid fa-right-left"></i>
                        </div>
                        <p class="mt-3 text-sm font-semibold text-slate-700">Belum ada request tukar shift.</p>
                        <a href="{{ route('shift-swaps.create') }}" class="user-btn-primary mt-4">Ajukan Tukar Shift</a>
                    </section>
                @endforelse
            </section>

            <div class="text-sm">
                {{ $swaps->links() }}
            </div>
        </main>

        @include('user.partials.bottom-nav', ['active' => ''])
    </div>
</div>
@endsection
