@extends('layouts.admin')

@section('title', 'Master Jabatan')

@section('content')
<div id="modal-tambah" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="animate-modal mx-4 w-full max-w-lg rounded-md bg-white p-6 shadow-md">
        <div class="mb-5 flex items-center justify-between">
            <h2 class="text-base font-bold tracking-tight text-gray-700">Tambah Jabatan</h2>
            <button type="button" onclick="closeModal('modal-tambah')" class="text-gray-400 transition hover:text-gray-600">
                <i class="fas fa-xmark text-lg"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.positions.store') }}" class="space-y-4">
            @csrf
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500">Unit Kerja/Bagian</label>
                <select name="department_id" class="w-full rounded-md border border-gray-200 px-3.5 py-2.5 text-sm text-gray-800 transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Pilih unit kerja/bagian</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" @selected((string) old('department_id') === (string) $department->id)>{{ $department->nama_departemen }}</option>
                    @endforeach
                </select>
                @error('department_id')
                    <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500">Nama Jabatan</label>
                <input
                    name="nama_jabatan"
                    value="{{ old('nama_jabatan') }}"
                    placeholder="Contoh: Perawat, Dokter, Kepala Ruangan..."
                    class="w-full rounded-md border border-gray-200 px-3.5 py-2.5 text-sm text-gray-800 transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                @error('nama_jabatan')
                    <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
                @enderror
            </div>
            <div class="mt-6 flex justify-end gap-2">
                <button type="button" onclick="closeModal('modal-tambah')" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-200">
                    Batal
                </button>
                <button type="submit" class="rounded-md bg-blue-600 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

<div id="modal-edit" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="animate-modal mx-4 w-full max-w-lg rounded-md bg-white p-6 shadow-md">
        <div class="mb-5 flex items-center justify-between">
            <h2 class="text-base font-bold tracking-tight text-gray-700">Edit Jabatan</h2>
            <button type="button" onclick="closeModal('modal-edit')" class="text-gray-400 transition hover:text-gray-600">
                <i class="fas fa-xmark text-lg"></i>
            </button>
        </div>
        <form method="POST" id="form-edit" class="space-y-4">
            @csrf
            @method('PUT')
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500">Unit Kerja/Bagian</label>
                <select id="edit-department" name="department_id" class="w-full rounded-md border border-gray-200 px-3.5 py-2.5 text-sm text-gray-800 transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}">{{ $department->nama_departemen }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500">Nama Jabatan</label>
                <input id="edit-nama" name="nama_jabatan" placeholder="Nama jabatan..." class="w-full rounded-md border border-gray-200 px-3.5 py-2.5 text-sm text-gray-800 transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500">
            </div>
            <div class="mt-6 flex justify-end gap-2">
                <button type="button" onclick="closeModal('modal-edit')" class="rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-200">
                    Batal
                </button>
                <button type="submit" class="rounded-md bg-amber-500 px-5 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-amber-600">
                    Update
                </button>
            </div>
        </form>
    </div>
</div>

<div id="modal-hapus" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="animate-modal mx-4 w-full max-w-sm rounded-md bg-white p-6 shadow-md">
        <div class="mb-6 flex flex-col items-center gap-3 text-center">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100">
                <i class="fas fa-trash-can text-lg text-red-500"></i>
            </div>
            <h2 class="text-base font-bold text-gray-700">Hapus Jabatan?</h2>
            <p class="text-sm text-gray-500">Jabatan <span id="hapus-nama" class="font-semibold text-gray-700"></span> akan dihapus secara permanen.</p>
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

<div class="space-y-5">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-gray-700">Kelola Data Jabatan</h1>
            <p class="mt-0.5 text-sm text-gray-500">Kelola jabatan berdasarkan unit kerja/bagian</p>
        </div>
        <button type="button" onclick="openModal('modal-tambah')" class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow transition hover:bg-blue-700">
            <i class="fas fa-plus text-xs"></i>
            Tambah Jabatan
        </button>
    </div>

    <div class="rounded-md border border-gray-200 bg-white p-4 shadow-sm">
        <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-500">Filter & Pencarian</p>
        <form method="GET" action="{{ route('admin.positions.index') }}" data-auto-filter class="flex flex-wrap items-end gap-3">
            <div class="min-w-48 flex-1">
                <label class="mb-2 block text-xs text-gray-500">Cari jabatan/unit kerja</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400"></i>
                    <input
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Nama jabatan atau unit kerja..."
                        class="w-full rounded-md border border-gray-200 py-2 pl-8 pr-3 text-sm text-gray-700 transition focus:border focus:border-gray-500 focus:outline-none"
                    >
                </div>
            </div>
            <div class="min-w-56">
                <label class="mb-2 block text-xs text-gray-500">Unit Kerja/Bagian</label>
                <select name="department_id" class="w-full rounded-md border border-gray-200 px-3 py-2 text-sm text-gray-700 transition focus:border-gray-500 focus:outline-none">
                    <option value="">Semua unit kerja/bagian</option>
                    @foreach($departments as $department)
                        <option value="{{ $department->id }}" @selected((string) request('department_id') === (string) $department->id)>{{ $department->nama_departemen }}</option>
                    @endforeach
                </select>
            </div>

            @if(request('search') || request('department_id'))
                <a href="{{ route('admin.positions.index') }}" class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-500 transition hover:bg-gray-100 hover:text-gray-700">
                    <i class="fas fa-xmark text-xs"></i> Reset
                </a>
            @endif
        </form>
    </div>

    <div class="overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3.5">
            <div class="flex items-center gap-3">
                <span class="text-sm font-semibold text-gray-700">Daftar Jabatan</span>
                <span id="selected-badge" class="hidden rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700"></span>
            </div>
            <button id="btn-bulk-delete" type="button" onclick="bulkDelete()" class="hidden items-center gap-1.5 rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-100">
                <i class="fas fa-trash-can text-xs"></i> Hapus Terpilih
            </button>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <th class="w-10 px-5 py-3 text-left">
                            <input type="checkbox" id="check-all" onchange="toggleAll(this)" class="h-4 w-4 cursor-pointer rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        </th>
                        <th class="px-5 py-3 text-left">Unit Kerja/Bagian</th>
                        <th class="px-5 py-3 text-left">Jabatan</th>
                        <th class="px-5 py-3 text-left">Pegawai</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($positions as $position)
                        <tr class="group transition hover:bg-gray-50/70" data-id="{{ $position->id }}">
                            <td class="px-5 py-3.5">
                                <input type="checkbox" name="selected[]" value="{{ $position->id }}" onchange="updateBulkBar()" class="row-check h-4 w-4 cursor-pointer rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="font-medium text-gray-500">{{ $position->department?->nama_departemen ?? '-' }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="font-medium text-gray-700">{{ $position->nama_jabatan }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center gap-1 rounded-full bg-violet-50 px-2.5 py-1 text-xs font-semibold text-violet-700">
                                    <i class="fas fa-users text-[10px]"></i>
                                    {{ $position->employee_details_count }} pegawai
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                @if($position->employee_details_count > 0)
                                    <span class="inline-flex items-center gap-1 rounded-full bg-green-50 px-2.5 py-1 text-xs font-semibold text-green-600">
                                        <span class="inline-block h-1.5 w-1.5 rounded-full bg-green-500"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-400">
                                        <span class="inline-block h-1.5 w-1.5 rounded-full bg-gray-400"></span> Kosong
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button
                                        type="button"
                                        onclick="openEdit('{{ $position->id }}', '{{ $position->department_id }}', '{{ addslashes($position->nama_jabatan) }}', '{{ route('admin.positions.update', $position) }}')"
                                        class="inline-flex items-center gap-1.5 rounded-md border border-amber-200 bg-amber-50 px-3 py-1.5 text-xs font-semibold text-amber-600 transition hover:bg-amber-100">
                                        <i class="fas fa-pen text-[10px]"></i> Edit
                                    </button>
                                    <button
                                        type="button"
                                        onclick="openHapus('{{ $position->id }}', '{{ addslashes($position->nama_jabatan) }}', '{{ route('admin.positions.destroy', $position) }}')"
                                        class="inline-flex items-center gap-1.5 rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-100">
                                        <i class="fas fa-trash-can text-[10px]"></i> Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-14 text-center">
                                <div class="flex flex-col items-center gap-2 text-gray-500">
                                    <i class="fas fa-briefcase text-3xl"></i>
                                    <p class="text-sm font-medium">Belum ada jabatan</p>
                                    <p class="text-xs">Klik "Tambah Jabatan" untuk memulai</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($positions, 'links'))
            <div class="flex items-center justify-between border-t border-gray-100 px-5 py-3.5">
                <p class="text-xs text-gray-500">
                    Menampilkan {{ $positions->firstItem() }}-{{ $positions->lastItem() }} dari {{ $positions->total() }} data
                </p>
                {{ $positions->withQueryString()->links() }}
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

    ['modal-tambah', 'modal-edit', 'modal-hapus'].forEach(id => {
        document.getElementById(id).addEventListener('click', function (event) {
            if (event.target === this) closeModal(id);
        });
    });

    function openEdit(id, departmentId, nama, actionUrl) {
        document.getElementById('edit-department').value = departmentId;
        document.getElementById('edit-nama').value = nama;
        document.getElementById('form-edit').action = actionUrl;
        openModal('modal-edit');
        setTimeout(() => document.getElementById('edit-nama').focus(), 100);
    }

    function openHapus(id, nama, actionUrl) {
        document.getElementById('hapus-nama').textContent = nama;
        document.getElementById('form-hapus').action = actionUrl;
        openModal('modal-hapus');
    }

    function toggleAll(master) {
        document.querySelectorAll('.row-check').forEach(cb => cb.checked = master.checked);
        updateBulkBar();
    }

    function updateBulkBar() {
        const checked = document.querySelectorAll('.row-check:checked');
        const badge = document.getElementById('selected-badge');
        const btn = document.getElementById('btn-bulk-delete');
        const master = document.getElementById('check-all');
        const all = document.querySelectorAll('.row-check');

        master.indeterminate = checked.length > 0 && checked.length < all.length;
        master.checked = checked.length === all.length && all.length > 0;

        if (checked.length > 0) {
            badge.textContent = checked.length + ' dipilih';
            badge.classList.remove('hidden');
            btn.classList.remove('hidden');
            btn.classList.add('inline-flex');
        } else {
            badge.classList.add('hidden');
            btn.classList.add('hidden');
            btn.classList.remove('inline-flex');
        }
    }

    function bulkDelete() {
        const ids = [...document.querySelectorAll('.row-check:checked')].map(cb => cb.value);
        if (!ids.length) return;
        if (!confirm('Hapus ' + ids.length + ' jabatan yang dipilih?')) return;

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.positions.bulk-delete") }}';
        form.innerHTML = `@csrf @method('DELETE')`;

        ids.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'ids[]';
            input.value = id;
            form.appendChild(input);
        });

        document.body.appendChild(form);
        form.submit();
    }

    @if($errors->any())
        openModal('modal-tambah');
    @endif
</script>
@endsection
