@extends('layouts.app')

@section('title', 'Status Tukar Shift')

@section('content')
<div class="min-h-dvh bg-slate-50 py-4">
    <div class="max-w-5xl mx-auto px-4 space-y-4">
        @if(session('success'))
            <div class="rounded-md bg-emerald-100 border border-emerald-200 text-emerald-700 px-4 py-3 text-sm">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="rounded-md bg-red-100 border border-red-200 text-red-700 px-4 py-3 text-sm">{{ session('error') }}</div>
        @endif

        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div>
                    <h1 class="text-lg font-bold text-slate-800">Status Request Tukar Shift</h1>
                    <p class="text-sm text-slate-500">Pantau status pending, approved, atau rejected.</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('shift-swaps.create') }}" class="bg-emerald-600 text-white rounded-md px-4 py-2 text-sm font-semibold">Ajukan Tukar Shift</a>
                    <a href="{{ route('user.shifts.index') }}" class="bg-slate-200 text-slate-700 rounded-md px-4 py-2 text-sm font-semibold">Jadwal Saya</a>
                </div>
            </div>
        </div>

        <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
            <form method="GET" class="flex gap-2 items-end">
                <div>
                    <label class="text-xs font-semibold text-slate-600">Filter Status</label>
                    <select name="status" class="border rounded-md px-3 py-2 text-sm">
                        <option value="">Semua</option>
                        <option value="pending" @selected($status === 'pending')>Pending</option>
                        <option value="approved" @selected($status === 'approved')>Approved</option>
                        <option value="rejected" @selected($status === 'rejected')>Rejected</option>
                    </select>
                </div>
                <button class="bg-blue-600 text-white rounded-md px-4 py-2 text-sm font-semibold">Filter</button>
            </form>
        </div>

        <div class="space-y-3">
            @forelse($swaps as $swap)
                @php
                    $statusClass = [
                        'pending' => 'bg-amber-100 text-amber-700',
                        'approved' => 'bg-emerald-100 text-emerald-700',
                        'rejected' => 'bg-red-100 text-red-700',
                    ][$swap->status] ?? 'bg-slate-100 text-slate-700';
                @endphp
                <div class="bg-white border border-slate-200 rounded-2xl p-4 shadow-sm">
                    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                        <div>
                            <p class="text-sm font-bold text-slate-800">{{ $swap->requester->name ?? '-' }} ? {{ $swap->targetUser->name ?? '-' }}</p>
                            <p class="text-xs text-slate-500 mt-1">Dibuat: {{ $swap->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">{{ strtoupper($swap->status) }}</span>
                    </div>

                    <div class="mt-3 grid sm:grid-cols-2 gap-3 text-sm text-slate-600">
                        <div class="rounded-xl bg-slate-50 p-3">
                            <p class="text-xs text-slate-500">Shift Requester</p>
                            @if($swap->shift)
                                <p class="font-semibold">{{ $swap->shift->tanggal->format('d/m/Y') }}</p>
                                <p>{{ $swap->shift->jam_masuk?->format('H:i') }} - {{ $swap->shift->jam_pulang?->format('H:i') }}</p>
                            @else
                                <p>-</p>
                            @endif
                        </div>
                        <div class="rounded-xl bg-slate-50 p-3">
                            <p class="text-xs text-slate-500">Shift Target</p>
                            @if($swap->targetShift)
                                <p class="font-semibold">{{ $swap->targetShift->tanggal->format('d/m/Y') }}</p>
                                <p>{{ $swap->targetShift->jam_masuk?->format('H:i') }} - {{ $swap->targetShift->jam_pulang?->format('H:i') }}</p>
                            @else
                                <p>-</p>
                            @endif
                        </div>
                    </div>

                    @if($swap->note)
                        <div class="mt-3 text-xs text-slate-600 bg-slate-50 border border-slate-200 rounded-xl p-3 whitespace-pre-line">{{ $swap->note }}</div>
                    @endif

                    @if($swap->status === 'pending' && (int) $swap->target_user_id === (int) auth()->id())
                        <div class="mt-3 flex gap-2">
                            <form action="{{ route('shift-swaps.accept', $swap) }}" method="POST">
                                @csrf
                                <button class="bg-emerald-600 text-white rounded-md px-4 py-2 text-xs font-semibold">Accept</button>
                            </form>
                            <form action="{{ route('shift-swaps.reject', $swap) }}" method="POST" onsubmit="return confirm('Tolak request ini?')">
                                @csrf
                                <button class="bg-red-600 text-white rounded-md px-4 py-2 text-xs font-semibold">Reject</button>
                            </form>
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white border border-slate-200 rounded-2xl p-6 text-sm text-slate-400 text-center">
                    Belum ada request tukar shift.
                </div>
            @endforelse
        </div>

        <div>
            {{ $swaps->links() }}
        </div>
    </div>
</div>
@endsection
