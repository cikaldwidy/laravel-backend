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

    <div class="bg-white rounded-lg shadow overflow-x-auto">
        <table class="w-full text-sm">
            <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="p-3 text-left">Nama Shift</th>
                    <th class="p-3 text-left">Jam Masuk</th>
                    <th class="p-3 text-left">Jam Pulang</th>
                    <th class="p-3 text-left">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($shifts as $shift)
                    <tr class="hover:bg-gray-50">
                        <td class="p-3 font-medium text-gray-800">{{ $shift->nama_shift }}</td>
                        <td class="p-3 text-gray-600">{{ \Illuminate\Support\Str::of($shift->jam_masuk)->substr(0,5) }}</td>
                        <td class="p-3 text-gray-600">{{ \Illuminate\Support\Str::of($shift->jam_pulang)->substr(0,5) }}</td>
                        <td class="p-3">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('admin.shifts.edit', $shift) }}" class="px-3 py-1 rounded bg-amber-50 text-amber-700 hover:bg-amber-100 text-xs font-semibold">
                                    Edit
                                </a>
                                <form method="POST" action="{{ route('admin.shifts.destroy', $shift) }}" data-confirm-form data-confirm-title="Hapus shift?" data-confirm-message="Shift ini akan dihapus dari master shift." data-confirm-button="Hapus">
                                    @csrf
                                    @method('DELETE')
                                    <button class="px-3 py-1 rounded bg-red-50 text-red-700 hover:bg-red-100 text-xs font-semibold">
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
