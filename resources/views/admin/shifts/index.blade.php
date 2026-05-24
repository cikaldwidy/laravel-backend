@extends('layouts.admin')

@section('title', 'Master Shift')

@section('content')
<div id="modal-hapus" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="animate-modal mx-4 w-full max-w-sm rounded-md bg-white p-6 shadow-md">
        <div class="mb-6 flex flex-col items-center gap-3 text-center">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100">
                <i class="fas fa-trash-can text-lg text-red-500"></i>
            </div>
            <h2 class="text-base font-bold text-gray-700">Hapus Shift?</h2>
            <p class="text-sm text-gray-500">Shift <span id="hapus-nama" class="font-semibold text-gray-700"></span> akan dihapus secara permanen.</p>
        </div>
        <form method="POST" id="form-hapus">
            @csrf
            @method('DELETE')
            <div class="flex gap-2">
                <button type="button" onclick="closeModal('modal-hapus')" class="flex-1 rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-200">
                    Batal
                </button>
                <button type="submit" class="flex-1 rounded-md bg-red-500 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-red-600">
                    Hapus
                </button>
            </div>
        </form>
    </div>
</div>

<div class="space-y-6">
    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
        <div class="flex items-center gap-3">
            <span class="flex h-12 w-12 items-center justify-center rounded-md bg-gradient-to-br from-blue-600 to-sky-500 text-white shadow-lg shadow-blue-500/20">
                <i class="fa-solid fa-clock text-lg"></i>
            </span>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Master Shift</h1>
                <p class="mt-1 text-sm text-gray-500">Kelola template shift, jam masuk, dan jam pulang pegawai.</p>
            </div>
        </div>

        <a href="{{ route('admin.shifts.create') }}" class="inline-flex h-11 items-center justify-center gap-2 rounded-md bg-gradient-to-r from-blue-600 to-sky-500 px-5 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:from-blue-700 hover:to-sky-600">
            <i class="fa-solid fa-plus text-xs"></i>
            Tambah Shift
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-md border border-blue-100 bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-700">
            {{ session('success') }}
        </div>
    @endif

    <section class="overflow-hidden rounded-md border border-blue-100 bg-white shadow-sm">
        <div class="border-b border-blue-50 px-6 py-5">
            <div>
                <h2 class="text-lg font-bold text-gray-900">Daftar Shift</h2>
                <p class="mt-1 text-sm text-gray-500">{{ $shifts->total() }} template shift tersimpan.</p>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-sm">
                <thead class="bg-blue-50/70 text-xs font-semibold uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-6 py-3 text-left">Nama Shift</th>
                        <th class="px-6 py-3 text-left">Jam Masuk</th>
                        <th class="px-6 py-3 text-left">Jam Pulang</th>
                        <th class="px-6 py-3 text-left">Durasi</th>
                        <th class="px-6 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-blue-50">
                    @forelse($shifts as $shift)
                        @php
                            $jamMasuk = \Illuminate\Support\Str::of($shift->jam_masuk)->substr(0, 5);
                            $jamPulang = \Illuminate\Support\Str::of($shift->jam_pulang)->substr(0, 5);
                            $start = \Carbon\Carbon::createFromFormat('H:i', (string) $jamMasuk);
                            $end = \Carbon\Carbon::createFromFormat('H:i', (string) $jamPulang);
                            if ($end->lessThanOrEqualTo($start)) {
                                $end->addDay();
                            }
                            $duration = $start->diffInHours($end) . ' jam';
                        @endphp
                        <tr class="hover:bg-blue-50/40">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-10 w-10 items-center justify-center rounded-md bg-blue-50 text-blue-600">
                                        <i class="fa-solid fa-business-time"></i>
                                    </span>
                                    <div>
                                        <p class="font-semibold text-gray-900">{{ $shift->nama_shift }}</p>
                                        <p class="text-xs text-gray-500">Template shift</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 font-semibold text-gray-700">{{ $jamMasuk }}</td>
                            <td class="px-6 py-4 font-semibold text-gray-700">{{ $jamPulang }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-md bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">{{ $duration }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.shifts.edit', $shift) }}" class="inline-flex items-center justify-center gap-2 rounded-md border border-blue-100 bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-600 transition hover:bg-blue-100">
                                        <i class="fa-solid fa-pen"></i>
                                        Edit
                                    </a>
                                    <button
                                        type="button"
                                        onclick="openHapus('{{ $shift->id }}', '{{ addslashes($shift->nama_shift) }}', '{{ route('admin.shifts.destroy', $shift) }}')"
                                        class="inline-flex items-center justify-center gap-2 rounded-md border border-red-100 bg-red-50 px-3 py-2 text-xs font-semibold text-red-600 transition hover:bg-red-100">
                                        <i class="fa-solid fa-trash"></i>
                                        Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-md bg-blue-50 text-blue-400">
                                    <i class="fa-regular fa-clock text-xl"></i>
                                </div>
                                <p class="mt-3 font-semibold text-gray-800">Belum ada data shift</p>
                                <p class="text-sm text-gray-500">Tambahkan shift untuk mulai mengatur jadwal kerja.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($shifts->hasPages())
            <div class="border-t border-blue-50 px-6 py-4">
                {{ $shifts->links() }}
            </div>
        @endif
    </section>
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

    document.getElementById('modal-hapus').addEventListener('click', function (event) {
        if (event.target === this) closeModal('modal-hapus');
    });

    function openHapus(id, nama, actionUrl) {
        document.getElementById('hapus-nama').textContent = nama;
        document.getElementById('form-hapus').action = actionUrl;
        openModal('modal-hapus');
    }
</script>
@endsection
