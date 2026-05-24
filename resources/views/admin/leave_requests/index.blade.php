@extends('layouts.admin')

@php
    $jenisIzin = request('jenis_izin');
    $pageTitle = $jenisIzin ? ucfirst($jenisIzin) : 'Perizinan';
@endphp

@section('title', $pageTitle)

@section('content')
<div id="modal-status" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="animate-modal mx-4 w-full max-w-md rounded-md bg-white p-6 shadow-md">
        <div class="mb-5 flex items-center justify-between">
            <h2 class="text-base font-bold tracking-tight text-gray-700">Update Status Izin</h2>
            <button type="button" onclick="closeModal('modal-status')" class="text-gray-400 transition hover:text-gray-600">
                <i class="fas fa-xmark text-lg"></i>
            </button>
        </div>
        <form method="POST" id="form-status" class="space-y-4">
            @csrf
            <div class="rounded-md bg-blue-50 p-4">
                <p class="text-xs font-semibold uppercase tracking-wider text-blue-600">Pengajuan</p>
                <p id="status-user" class="mt-1 text-sm font-semibold text-gray-800"></p>
                <p id="status-period" class="text-xs text-gray-500"></p>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500">Status</label>
                <select id="status-value" name="status" class="w-full rounded-md border border-gray-200 px-3.5 py-2.5 text-sm text-gray-800 transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="approved">Approve</option>
                    <option value="rejected">Reject</option>
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500">Catatan Admin</label>
                <textarea id="status-note" name="catatan_admin" rows="3" class="w-full rounded-md border border-gray-200 px-3.5 py-2.5 text-sm text-gray-800 transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Tambahkan catatan jika diperlukan"></textarea>
            </div>
            <div class="mt-6 flex justify-end gap-2">
                <button type="button" onclick="closeModal('modal-status')" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-200">
                    Batal
                </button>
                <button type="submit" class="rounded-md bg-blue-600 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<div class="space-y-5">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-gray-700">{{ $pageTitle }}</h1>
            <p class="mt-0.5 text-sm text-gray-500">Kelola pengajuan izin, sakit, dan cuti pegawai.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-md border border-blue-100 bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-md border border-gray-200 bg-white p-4 shadow-sm">
        <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-500">Filter & Pencarian</p>
        <form method="GET" action="{{ route('admin.leave_requests.index') }}" data-auto-filter class="grid gap-3 md:grid-cols-4">
            @if($jenisIzin)
                <input type="hidden" name="jenis_izin" value="{{ $jenisIzin }}">
            @else
                <div>
                    <label class="mb-2 block text-xs font-semibold text-gray-500">Jenis</label>
                    <select name="jenis_izin" class="w-full rounded-md border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 transition focus:border-gray-500 focus:outline-none">
                        <option value="">Semua Jenis</option>
                        @foreach(['izin', 'sakit', 'cuti'] as $jenis)
                            <option value="{{ $jenis }}" @selected(request('jenis_izin') === $jenis)>{{ ucfirst($jenis) }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div>
                <label class="mb-2 block text-xs font-semibold text-gray-500">Status</label>
                <select name="status" class="w-full rounded-md border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 transition focus:border-gray-500 focus:outline-none">
                    <option value="">Semua Status</option>
                    @foreach(['pending','approved','rejected'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>{{ ucfirst($status) }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-2 block text-xs font-semibold text-gray-500">Pegawai</label>
                <select name="user_id" class="w-full rounded-md border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 transition focus:border-gray-500 focus:outline-none">
                    <option value="">Semua Pegawai</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected((string) request('user_id') === (string) $user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-2 block text-xs font-semibold text-gray-500">Tanggal</label>
                <input type="date" name="tanggal" value="{{ request('tanggal') }}" class="w-full rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-700 transition focus:border-gray-500 focus:outline-none">
            </div>
            <div class="md:col-span-4">
                <label class="mb-2 block text-xs font-semibold text-gray-500">Unit Kerja/Bagian</label>
                <select name="unit_id" class="w-full rounded-md border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 transition focus:border-gray-500 focus:outline-none">
                    <option value="">Semua Unit Kerja/Bagian</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" @selected((string) request('unit_id') === (string) $unit->id)>{{ $unit->nama_departemen }}</option>
                    @endforeach
                </select>
            </div>
            @if(request()->hasAny(['jenis_izin', 'status', 'user_id', 'unit_id', 'tanggal']))
                <div class="md:col-span-4">
                    <a href="{{ route('admin.leave_requests.index', $jenisIzin ? ['jenis_izin' => $jenisIzin] : []) }}" class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-500 transition hover:bg-gray-100 hover:text-gray-700">
                        <i class="fas fa-xmark text-xs"></i> Reset
                    </a>
                </div>
            @endif
        </form>
    </div>

    <div class="overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3.5">
            <div class="flex items-center gap-3">
                <span class="text-sm font-semibold text-gray-700">Daftar {{ $pageTitle }}</span>
                <span id="selected-badge" class="hidden rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700"></span>
            </div>
            <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-500">{{ $requests->total() }} data</span>
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
                        <th class="px-5 py-3 text-left">Jenis</th>
                        <th class="px-5 py-3 text-left">Periode</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-left">Lampiran</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($requests as $item)
                        @php
                            $statusClass = match ($item->status) {
                                'approved' => 'bg-green-50 text-green-700',
                                'rejected' => 'bg-red-50 text-red-700',
                                default => 'bg-yellow-50 text-yellow-700',
                            };
                            $jenisClass = match ($item->jenis_izin) {
                                'sakit' => 'bg-red-50 text-red-700',
                                'cuti' => 'bg-sky-50 text-sky-700',
                                default => 'bg-blue-50 text-blue-700',
                            };
                            $periode = $item->tanggal_mulai->format('d/m/Y') . ' - ' . $item->tanggal_selesai->format('d/m/Y');
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
                            <td class="px-5 py-3.5">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $jenisClass }}">{{ ucfirst($item->jenis_izin) }}</span>
                            </td>
                            <td class="px-5 py-3.5 text-gray-600">{{ $periode }}</td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusClass }}">{{ ucfirst($item->status) }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                @if($item->lampiran)
                                    <a href="{{ asset('storage/' . $item->lampiran) }}" class="inline-flex items-center gap-1.5 rounded-md border border-blue-100 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-600 transition hover:bg-blue-100" target="_blank">
                                        <i class="fas fa-paperclip text-[10px]"></i> Lihat
                                    </a>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex justify-end">
                                    <button
                                        type="button"
                                        onclick="openStatus(@js(route('admin.leave_requests.update', $item)), @js($item->user?->name ?? '-'), @js($periode), @js($item->status === 'rejected' ? 'rejected' : 'approved'), @js($item->catatan_admin ?? ''))"
                                        class="inline-flex items-center gap-1.5 rounded-md border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-600 transition hover:bg-blue-100">
                                        <i class="fas fa-pen text-[10px]"></i> Update
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-14 text-center">
                                <div class="flex flex-col items-center gap-2 text-gray-500">
                                    <i class="fas fa-file-circle-check text-3xl"></i>
                                    <p class="text-sm font-medium">Belum ada data {{ strtolower($pageTitle) }}</p>
                                    <p class="text-xs">Data pengajuan akan muncul sesuai filter yang dipilih.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($requests->hasPages())
            <div class="flex items-center justify-between border-t border-gray-100 px-5 py-3.5">
                <p class="text-xs text-gray-500">
                    Menampilkan {{ $requests->firstItem() }}-{{ $requests->lastItem() }} dari {{ $requests->total() }} data
                </p>
                {{ $requests->links() }}
            </div>
        @endif
    </div>
</div>

<style>
    .animate-modal {
        animation: modalIn .2s ease;
    }
    @keyframes modalIn {
        from { opacity: 0; transform: scale(.96) translateY(8px); }
        to { opacity: 1; transform: scale(1) translateY(0); }
    }
</style>

<script>
    function openModal(id) {
        const el = document.getElementById(id);
        el.classList.remove('hidden');
        el.classList.add('flex');
    }

    function closeModal(id) {
        const el = document.getElementById(id);
        el.classList.add('hidden');
        el.classList.remove('flex');
    }

    document.getElementById('modal-status').addEventListener('click', function (event) {
        if (event.target === this) closeModal('modal-status');
    });

    function openStatus(actionUrl, userName, period, status, note) {
        document.getElementById('form-status').action = actionUrl;
        document.getElementById('status-user').textContent = userName;
        document.getElementById('status-period').textContent = period;
        document.getElementById('status-value').value = status;
        document.getElementById('status-note').value = note;
        openModal('modal-status');
    }

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
