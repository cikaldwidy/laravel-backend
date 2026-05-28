@extends('layouts.admin')

@section('title', 'Approval Tukar Shift')

@section('content')
<div class="space-y-5">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Tukar Shift</h1>
            <p class="mt-1 text-sm text-gray-500">Kelola persetujuan penukaran shift antar pegawai.</p>
        </div>
    </div>

    <div class="bg-white rounded-md shadow border border-gray-200 p-4">
        <form method="GET" data-auto-filter class="flex flex-wrap gap-2 items-end">
            <div>
                <label class="text-xs font-semibold text-gray-600">Filter Status</label>
                <select name="status" class="border rounded-md px-3 py-2 text-sm">
                    <option value="">Semua</option>
                    <option value="pending" @selected($status === 'pending')>Pending</option>
                    <option value="approved" @selected($status === 'approved')>Approved</option>
                    <option value="rejected" @selected($status === 'rejected')>Rejected</option>
                </select>
            </div>
            <a href="{{ route('admin.shift_management.swaps') }}" class="swap-reset-button inline-flex h-10 items-center justify-center gap-2 rounded-md border border-gray-200 bg-white px-4 text-sm font-semibold text-gray-600 shadow-sm transition hover:bg-gray-50 hover:text-gray-700">
                <i class="fas fa-xmark text-xs"></i>
                Reset
            </a>
        </form>
    </div>

    <div class="bg-white rounded-md shadow border border-gray-200 overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs uppercase tracking-wide text-gray-500">
                    <th class="px-4 py-3">Requester</th>
                    <th class="px-4 py-3">Target User</th>
                    <th class="px-4 py-3">Shift Requester</th>
                    <th class="px-4 py-3">Shift Target</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Catatan</th>
                    <th class="px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($swaps as $swap)
                    @php
                        $statusClass = [
                            'pending' => 'bg-amber-100 text-amber-700',
                            'approved' => 'bg-emerald-100 text-emerald-700',
                            'rejected' => 'bg-red-100 text-red-700',
                        ][$swap->status] ?? 'bg-slate-100 text-slate-700';
                    @endphp
                    <tr class="border-t border-gray-100 align-top">
                        <td class="px-4 py-3">
                            <div class="font-semibold text-gray-700">{{ $swap->requester->name ?? '-' }}</div>
                            <div class="text-xs text-gray-500">{{ $swap->created_at->format('d/m/Y H:i') }}</div>
                        </td>
                        <td class="px-4 py-3 text-gray-700">{{ $swap->targetUser->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-600">
                            @if($swap->shift)
                                {{ $swap->shift->tanggal->format('d/m/Y') }}<br>
                                <span class="text-xs">{{ $swap->shift->jam_masuk?->format('H:i') }} - {{ $swap->shift->jam_pulang?->format('H:i') }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600">
                            @if($swap->targetShift)
                                {{ $swap->targetShift->tanggal->format('d/m/Y') }}<br>
                                <span class="text-xs">{{ $swap->targetShift->jam_masuk?->format('H:i') }} - {{ $swap->targetShift->jam_pulang?->format('H:i') }}</span>
                            @else
                                -
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">{{ strtoupper($swap->status) }}</span>
                        </td>
                        <td class="px-4 py-3 text-xs text-gray-600 whitespace-pre-line">{{ $swap->note ?: '-' }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            @if($swap->status === 'pending')
                                <div class="flex flex-wrap items-center justify-start gap-2">
                                    <form action="{{ route('admin.shift_management.swaps.approve', $swap) }}" method="POST" data-confirm-form data-confirm-title="Approve swap?" data-confirm-message="Jadwal shift akan ditukar sesuai request ini." data-confirm-button="Approve" data-confirm-tone="primary" data-confirm-icon="fa-solid fa-check">
                                        @csrf
                                        <button class="swap-action-approve inline-flex items-center justify-center rounded-md bg-white px-3 py-1.5 text-xs font-semibold text-emerald-700 shadow-sm transition hover:bg-emerald-50">Approve</button>
                                    </form>
                                    <form action="{{ route('admin.shift_management.swaps.reject', $swap) }}" method="POST" class="flex flex-wrap items-center gap-2">
                                        @csrf
                                        <input type="text" name="note" placeholder="Alasan (opsional)" class="swap-action-note w-40 rounded-md border border-gray-200 px-3 py-1.5 text-xs text-gray-700">
                                        <button class="swap-action-reject inline-flex items-center justify-center rounded-md bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-700 shadow-sm transition hover:bg-red-100">Reject</button>
                                    </form>
                                </div>
                            @else
                                <span class="text-xs text-gray-500">Diproses oleh: {{ $swap->approver->name ?? '-' }}</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-sm text-gray-400">Belum ada request tukar shift.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $swaps->links() }}
    </div>
</div>

<style>
    html[data-admin-theme="dark"] main .swap-reset-button {
        background: #0b1728 !important;
        border-color: var(--admin-border) !important;
        color: #cbd5e1 !important;
    }
    html[data-admin-theme="dark"] main .swap-reset-button:hover {
        background: rgba(96, 165, 250, .12) !important;
        color: var(--admin-ink) !important;
    }
    html[data-admin-theme="dark"] main .swap-action-approve {
        background: #fff7ed !important;
        color: #b45309 !important;
        border: 0 !important;
    }
    html[data-admin-theme="dark"] main .swap-action-approve:hover {
        background: #ffedd5 !important;
    }
    html[data-admin-theme="dark"] main .swap-action-reject {
        background: rgba(127, 29, 29, .38) !important;
        color: #ef4444 !important;
        border: 0 !important;
    }
    html[data-admin-theme="dark"] main .swap-action-reject:hover {
        background: rgba(127, 29, 29, .55) !important;
    }
    html[data-admin-theme="dark"] main .swap-action-note {
        background: #0b1728 !important;
        border-color: var(--admin-border) !important;
        color: var(--admin-ink) !important;
    }
</style>
@endsection
