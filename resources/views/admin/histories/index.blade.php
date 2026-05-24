@extends('layouts.admin')

@section('title', 'Riwayat Absensi')

@section('content')
<div class="space-y-5">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-gray-700">Riwayat Absensi</h1>
            <p class="mt-0.5 text-sm text-gray-500">Pantau riwayat masuk dan pulang pegawai berdasarkan unit kerja.</p>
        </div>
    </div>

    <div class="rounded-md border border-gray-200 bg-white p-4 shadow-sm">
        <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-500">Filter Data</p>
        <form method="GET" data-auto-filter class="flex flex-wrap items-end gap-3">
            <div class="min-w-64 flex-1">
                <label class="mb-2 block text-xs text-gray-500">Unit Kerja/Bagian</label>
                <select name="unit_id" class="w-full rounded-md border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 transition focus:border-gray-500 focus:outline-none" required>
                    <option value="" disabled @selected(blank(request('unit_id')))>Pilih unit kerja/bagian</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" @selected((string) request('unit_id') === (string) $unit->id)>{{ $unit->nama_departemen }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-48">
                <label class="mb-2 block text-xs text-gray-500">Tanggal</label>
                <input type="date" name="tanggal" value="{{ request('tanggal') }}" class="w-full rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-700 transition focus:border-gray-500 focus:outline-none">
            </div>
            @if(request('unit_id') || request('tanggal'))
                <a href="{{ route('admin.histories.index') }}" class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-500 transition hover:bg-gray-100 hover:text-gray-700">
                    <i class="fas fa-xmark text-xs"></i> Reset
                </a>
            @endif
        </form>
    </div>

    <div class="overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3.5">
            <div class="flex items-center gap-3">
                <span class="text-sm font-semibold text-gray-700">Daftar Riwayat Absensi</span>
                <span id="selected-badge" class="hidden rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700"></span>
            </div>
            <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-500">{{ $histories->total() }} data</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <th class="w-10 px-5 py-3 text-left">
                            <input type="checkbox" id="check-all" onchange="toggleAll(this)" class="h-4 w-4 cursor-pointer rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        </th>
                        <th class="px-5 py-3 text-left">Pegawai</th>
                        <th class="px-5 py-3 text-left">Unit Kerja/Bagian</th>
                        <th class="px-5 py-3 text-left">Tanggal</th>
                        <th class="px-5 py-3 text-left">Masuk</th>
                        <th class="px-5 py-3 text-left">Pulang</th>
                        <th class="px-5 py-3 text-left">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($histories as $item)
                        @php
                            $status = strtolower((string) ($item->status ?? '-'));
                            $statusClass = match ($status) {
                                'hadir' => 'bg-green-50 text-green-700',
                                'telat', 'terlambat' => 'bg-orange-50 text-orange-700',
                                'izin', 'sakit' => 'bg-blue-50 text-blue-700',
                                'alpha' => 'bg-red-50 text-red-700',
                                default => 'bg-gray-100 text-gray-500',
                            };
                        @endphp
                        <tr class="transition hover:bg-gray-50/70">
                            <td class="px-5 py-3.5">
                                <input type="checkbox" name="selected[]" value="{{ $item->id }}" onchange="updateSelectBar()" class="row-check h-4 w-4 cursor-pointer rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="font-medium text-gray-700">{{ $item->user?->name ?? '-' }}</div>
                                <div class="text-xs text-gray-500">{{ $item->user?->email ?? '-' }}</div>
                            </td>
                            <td class="px-5 py-3.5 font-medium text-gray-500">{{ $item->user?->employeeDetail?->department?->nama_departemen ?? $item->user?->employeeDetail?->departemen ?? '-' }}</td>
                            <td class="px-5 py-3.5 text-gray-600">{{ $item->tanggal->format('d/m/Y') }}</td>
                            <td class="px-5 py-3.5 font-semibold text-gray-700">{{ $item->jam_masuk?->format('H:i') ?? '-' }}</td>
                            <td class="px-5 py-3.5 font-semibold text-gray-700">{{ $item->jam_keluar?->format('H:i') ?? '-' }}</td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">{{ ucfirst($item->status ?? '-') }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-14 text-center">
                                <div class="flex flex-col items-center gap-2 text-gray-500">
                                    <i class="fas fa-clipboard-list text-3xl"></i>
                                    <p class="text-sm font-medium">{{ request('unit_id') ? 'Belum ada data riwayat' : 'Pilih unit kerja/bagian terlebih dahulu' }}</p>
                                    <p class="text-xs">Data absensi akan muncul sesuai filter yang dipilih.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($histories->hasPages())
            <div class="flex items-center justify-between border-t border-gray-100 px-5 py-3.5">
                <p class="text-xs text-gray-500">
                    Menampilkan {{ $histories->firstItem() }}-{{ $histories->lastItem() }} dari {{ $histories->total() }} data
                </p>
                {{ $histories->links() }}
            </div>
        @endif
    </div>
</div>

<script>
    function toggleAll(master) {
        document.querySelectorAll('.row-check').forEach(cb => cb.checked = master.checked);
        updateSelectBar();
    }

    function updateSelectBar() {
        const checked = document.querySelectorAll('.row-check:checked');
        const badge = document.getElementById('selected-badge');
        const master = document.getElementById('check-all');
        const all = document.querySelectorAll('.row-check');

        if (master) {
            master.indeterminate = checked.length > 0 && checked.length < all.length;
            master.checked = checked.length === all.length && all.length > 0;
        }

        if (checked.length > 0) {
            badge.textContent = checked.length + ' dipilih';
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }
</script>
@endsection
