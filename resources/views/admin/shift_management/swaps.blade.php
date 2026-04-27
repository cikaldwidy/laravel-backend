@extends('layouts.admin')

@section('title', 'Approval Tukar Shift')

@section('content')
<div class="space-y-5">
    @if(session('success'))
        <div class="rounded-md bg-emerald-100 border border-emerald-200 text-emerald-700 px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-md bg-red-100 border border-red-200 text-red-700 px-4 py-3 text-sm">{{ session('error') }}</div>
    @endif

    <div class="bg-white rounded-md shadow border border-gray-200 p-4">
        <form method="GET" class="flex flex-wrap gap-2 items-end">
            <div>
                <label class="text-xs font-semibold text-gray-600">Filter Status</label>
                <select name="status" class="border rounded-md px-3 py-2 text-sm">
                    <option value="">Semua</option>
                    <option value="pending" @selected($status === 'pending')>Pending</option>
                    <option value="approved" @selected($status === 'approved')>Approved</option>
                    <option value="rejected" @selected($status === 'rejected')>Rejected</option>
                </select>
            </div>
            <button class="bg-blue-600 text-white rounded-md px-4 py-2 text-sm font-semibold">Apply</button>
            <a href="{{ route('admin.shift_management.swaps') }}" class="bg-gray-200 text-gray-700 rounded-md px-4 py-2 text-sm font-semibold">Reset</a>
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
                        <td class="px-4 py-3">
                            @if($swap->status === 'pending')
                                <div class="flex flex-col gap-2">
                                    <form action="{{ route('admin.shift_management.swaps.approve', $swap) }}" method="POST" onsubmit="return confirm('Approve swap ini?')">
                                        @csrf
                                        <button class="bg-emerald-600 text-white rounded-md px-3 py-1.5 text-xs font-semibold w-full">Approve</button>
                                    </form>
                                    <form action="{{ route('admin.shift_management.swaps.reject', $swap) }}" method="POST">
                                        @csrf
                                        <input type="text" name="note" placeholder="Alasan (opsional)" class="border rounded-md px-2 py-1 text-xs w-full mb-1">
                                        <button class="bg-red-600 text-white rounded-md px-3 py-1.5 text-xs font-semibold w-full">Reject</button>
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
@endsection
