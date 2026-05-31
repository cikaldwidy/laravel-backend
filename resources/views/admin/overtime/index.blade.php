@extends('layouts.admin')

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

<div class="space-y-5">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-gray-700">Lembur</h1>
            <p class="mt-0.5 text-sm text-gray-500">Rekap lembur manual dan lembur pengganti sakit.</p>
        </div>
        <span class="inline-flex w-fit items-center gap-2 rounded-md bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700">
            <i class="fa-solid fa-clock text-xs"></i>
            {{ $items->total() }} data
        </span>
    </div>

    <div class="rounded-md border border-gray-200 bg-white p-4 shadow-sm">
        <form method="GET" action="{{ route('admin.overtime.index') }}" data-auto-filter class="grid gap-3 md:grid-cols-3">
            <div>
                <label class="mb-2 block text-xs font-semibold text-gray-500">Status</label>
                <select name="status" class="w-full rounded-md border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 transition focus:border-gray-500 focus:outline-none">
                    <option value="">Semua Status</option>
                    @foreach($statusLabels as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-2 block text-xs font-semibold text-gray-500">Kompensasi</label>
                <select name="compensation_type" class="w-full rounded-md border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 transition focus:border-gray-500 focus:outline-none">
                    <option value="">Semua Kompensasi</option>
                    @foreach($compensationLabels as $value => $label)
                        <option value="{{ $value }}" @selected(request('compensation_type') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            @if(request()->hasAny(['status', 'compensation_type']))
                <div class="flex items-end">
                    <a href="{{ route('admin.overtime.index') }}" class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-500 transition hover:bg-gray-100 hover:text-gray-700">
                        <i class="fas fa-xmark text-xs"></i> Reset
                    </a>
                </div>
            @endif
        </form>
    </div>

    <div class="overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[920px] text-sm">
                <thead class="bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                    <tr>
                        <th class="px-5 py-3 text-left">Pegawai Lembur</th>
                        <th class="px-5 py-3 text-left">Sumber</th>
                        <th class="px-5 py-3 text-left">Periode</th>
                        <th class="px-5 py-3 text-left">Jam</th>
                        <th class="px-5 py-3 text-left">Kompensasi</th>
                        <th class="px-5 py-3 text-left">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($items as $item)
                        @php
                            $compensation = $compensationLabels[$item->compensation_type] ?? ucfirst(str_replace('_', ' ', $item->compensation_type));
                            $status = $statusLabels[$item->status] ?? ucfirst($item->status);
                        @endphp
                        <tr class="transition hover:bg-gray-50/70">
                            <td class="px-5 py-4">
                                <p class="font-semibold text-gray-800">{{ $item->user?->name ?? '-' }}</p>
                                <p class="text-xs text-gray-500">{{ $item->user?->employeeDetail?->department?->nama_departemen ?? '-' }}</p>
                            </td>
                            <td class="px-5 py-4 text-gray-600">
                                <p class="font-medium">{{ $item->source_type === 'sakit_pengganti' ? 'Pengganti Sakit' : ucfirst($item->source_type) }}</p>
                                <p class="text-xs text-gray-500">{{ $item->sourceUser?->name ? 'Menggantikan ' . $item->sourceUser->name : ($item->keterangan ?: '-') }}</p>
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-gray-600">
                                {{ $item->tanggal_mulai->format('d/m/Y') }} - {{ $item->tanggal_selesai->format('d/m/Y') }}
                            </td>
                            <td class="whitespace-nowrap px-5 py-4 text-gray-600">
                                {{ $item->jam_mulai && $item->jam_selesai ? $item->jam_mulai->format('H:i') . ' - ' . $item->jam_selesai->format('H:i') : '-' }}
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full bg-amber-50 px-2.5 py-1 text-xs font-semibold text-amber-700">{{ $compensation }}</span>
                            </td>
                            <td class="px-5 py-4">
                                <span class="inline-flex rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">{{ $status }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-14 text-center text-sm text-gray-500">Belum ada data lembur.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($items->hasPages())
            <div class="border-t border-gray-100 px-5 py-3.5">
                {{ $items->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
