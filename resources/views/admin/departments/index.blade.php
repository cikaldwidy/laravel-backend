@extends('layouts.admin')

@section('title', 'Master Departemen')

@section('content')

{{-- Modal Tambah Departemen --}}
<div id="modal-tambah" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-md shadow-md w-full max-w-md mx-4 p-6 animate-modal">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-base font-bold text-gray-700 tracking-tight">Tambah Departemen</h2>
            <button onclick="closeModal('modal-tambah')" class="text-gray-400 hover:text-gray-600 transition">
                <i class="fas fa-xmark text-lg"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.departments.store') }}">
            @csrf
            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Nama Departemen</label>
                <input
                    name="nama_departemen"
                    value="{{ old('nama_departemen') }}"
                    placeholder="Contoh: Keperawatan, Keuangan..."
                    autofocus
                    class="w-full border border-gray-200 rounded-md px-3.5 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                >
                @error('nama_departemen')
                    <p class="text-xs text-red-500 mt-1.5">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex gap-2 justify-end mt-6">
                <button type="button" onclick="closeModal('modal-tambah')"
                    class="px-4 py-2 text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                    Batal
                </button>
                <button type="submit"
                    class="px-5 py-2 text-sm font-semibold text-white bg-blue-600 hover:bg-blue-700 rounded-lg transition shadow-sm">
                    Simpan
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Edit Departemen --}}
<div id="modal-edit" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-md shadow-md w-full max-w-md mx-4 p-6 animate-modal">
        <div class="flex items-center justify-between mb-5">
            <h2 class="text-base font-bold text-gray-700 tracking-tight">Edit Departemen</h2>
            <button onclick="closeModal('modal-edit')" class="text-gray-400 hover:text-gray-600 transition">
                <i class="fas fa-xmark text-lg"></i>
            </button>
        </div>
        <form method="POST" id="form-edit">
            @csrf
            @method('PUT')
            <div class="mb-4">
                <label class="block text-xs font-semibold text-gray-500 uppercase tracking-wider mb-1.5">Nama Departemen</label>
                <input
                    id="edit-nama"
                    name="nama_departemen"
                    placeholder="Nama departemen..."
                    class="w-full border border-gray-200 rounded-md px-3.5 py-2.5 text-sm text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
                >
            </div>
            <div class="flex gap-2 justify-end mt-6">
                <button type="button" onclick="closeModal('modal-edit')"
                    class="px-4 py-2 text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-md transition">
                    Batal
                </button>
                <button type="submit"
                    class="px-5 py-2 text-sm font-semibold text-white bg-amber-500 hover:bg-amber-600 rounded-md transition shadow-sm">
                    Update
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal Konfirmasi Hapus --}}
<div id="modal-hapus" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="bg-white rounded-md shadow-md w-full max-w-sm mx-4 p-6 animate-modal">
        <div class="flex flex-col items-center text-center gap-3 mb-6">
            <div class="w-12 h-12 rounded-full bg-red-100 flex items-center justify-center">
                <i class="fas fa-trash-can text-red-500 text-lg"></i>
            </div>
            <h2 class="text-base font-bold text-gray-700">Hapus Departemen?</h2>
            <p class="text-sm text-gray-500">Departemen <span id="hapus-nama" class="font-semibold text-gray-700"></span> akan dihapus secara permanen.</p>
        </div>
        <form method="POST" id="form-hapus">
            @csrf
            @method('DELETE')
            <div class="flex gap-2">
                <button type="button" onclick="closeModal('modal-hapus')"
                    class="flex-1 px-4 py-2 text-sm font-semibold text-gray-600 bg-gray-100 hover:bg-gray-200 rounded-md transition">
                    Batal
                </button>
                <button type="submit"
                    class="flex-1 px-4 py-2 text-sm font-semibold text-white bg-red-500 hover:bg-red-600 rounded-md transition shadow-sm">
                    Hapus
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Main Content --}}
<div class="space-y-5">

    {{-- Header Row --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-700 tracking-tight">Master Departemen</h1>
            <p class="text-sm text-gray-500 mt-0.5">Kelola data departemen organisasi</p>
        </div>
        <button onclick="openModal('modal-tambah')"
            class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white text-sm font-semibold px-4 py-2.5 rounded-md shadow transition">
            <i class="fas fa-plus text-xs"></i>
            Tambah Departemen
        </button>
    </div>

    {{-- Filter Bar --}}
    <div class="bg-white rounded-md border border-gray-200 shadow-sm p-4">
        <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">Filter & Pencarian</p>
        <form method="GET" action="{{ route('admin.departments.index') }}" data-auto-filter class="flex flex-wrap gap-3 items-end">
            {{-- Search --}}
            <div class="flex-1 min-w-48">
                <label class="block text-xs text-gray-500 mb-2">Cari departemen</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Nama departemen..."
                        class="w-full border border-gray-200 rounded-md pl-8 pr-3 py-2 text-sm focus:outline-none focus:border focus:border-gray-500 transition text-gray-700"
                    >
                </div>
            </div>

            {{-- Filter Relasi --}}
            <div class="min-w-36">
                <label class="block text-xs text-gray-500 mb-2">Relasi Unit</label>
                <select name="relasi" class="w-full border border-gray-200 rounded-md px-3 py-2 text-sm focus:outline-none focus:border focus:border-gray-500 transition bg-white text-gray-700">
                    <option value="">Semua</option>
                    <option value="with_unit" {{ request('relasi') === 'with_unit' ? 'selected' : '' }}>Punya Unit</option>
                    <option value="no_unit"   {{ request('relasi') === 'no_unit'   ? 'selected' : '' }}>Tanpa Unit</option>
                </select>
            </div>

            @if(request('search') || request('relasi'))
                <a href="{{ route('admin.departments.index') }}"
                    class="inline-flex items-center gap-1.5 text-sm text-gray-500 hover:text-gray-700 px-3 py-2 rounded-md hover:bg-gray-100 transition border border-gray-300">
                    <i class="fas fa-xmark text-xs"></i> Reset
                </a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-md border border-gray-200 shadow-sm overflow-hidden">

        {{-- Table Toolbar --}}
        <div class="flex items-center justify-between px-5 py-3.5 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <span class="text-sm font-semibold text-gray-700">Daftar Departemen</span>
                <span id="selected-badge" class="hidden text-xs font-semibold bg-blue-50 text-blue-700 px-2.5 py-1 rounded-full"></span>
            </div>
            <div class="flex items-center gap-2">
                <button id="btn-bulk-delete"
                    onclick="bulkDelete()"
                    class="hidden items-center gap-1.5 text-xs font-semibold text-red-600 border border-red-200 bg-red-50 hover:bg-red-100 px-3 py-1.5 rounded-md transition">
                    <i class="fas fa-trash-can text-xs"></i> Hapus Terpilih
                </button>
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b border-gray-100 text-xs font-semibold text-gray-500 uppercase tracking-wider">
                        <th class="px-5 py-3 text-left w-10">
                            <input type="checkbox" id="check-all" onchange="toggleAll(this)"
                                class="w-4 h-4 rounded border-gray-300 text-blue-600 cursor-pointer focus:ring-blue-500">
                        </th>
                        <th class="px-5 py-3 text-left">Nama Departemen</th>
                        <th class="px-5 py-3 text-left">Unit Kerja</th>
                        <th class="px-5 py-3 text-left">Jabatan</th>
                        <th class="px-5 py-3 text-left">Status</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($departments as $department)
                        <tr class="hover:bg-gray-50/70 transition group" data-id="{{ $department->id }}">
                            <td class="px-5 py-3.5">
                                <input type="checkbox" name="selected[]" value="{{ $department->id }}"
                                    onchange="updateBulkBar()"
                                    class="row-check w-4 h-4 rounded border-gray-300 text-blue-600 cursor-pointer focus:ring-blue-500">
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="font-medium text-gray-500">{{ $department->nama_departemen }}</span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center gap-1 rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">
                                    <i class="fas fa-building text-[10px]"></i>
                                    {{ $department->units_count }} unit
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex items-center gap-1 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                                    <i class="fas fa-briefcase text-[10px]"></i>
                                    {{ $department->positions_count }} jabatan
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                @if($department->units_count > 0)
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-green-600 bg-green-50 px-2.5 py-1 rounded-full">
                                        <span class="w-1.5 h-1.5 rounded-full bg-green-500 inline-block"></span> Aktif
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1 text-xs font-semibold text-gray-400 bg-gray-100 px-2.5 py-1 rounded-full">
                                        <span class="w-1.5 h-1.5 rounded-full bg-gray-400 inline-block"></span> Kosong
                                    </span>
                                @endif
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-1.5">
                                    {{-- Edit --}}
                                    <button
                                        onclick="openEdit('{{ $department->id }}', '{{ addslashes($department->nama_departemen) }}', '{{ route('admin.departments.update', $department) }}')"
                                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-amber-600 bg-amber-50 hover:bg-amber-100 border border-amber-200 px-3 py-1.5 rounded-md transition">
                                        <i class="fas fa-pen text-[10px]"></i> Edit
                                    </button>
                                    {{-- Hapus --}}
                                    <button
                                        onclick="openHapus('{{ $department->id }}', '{{ addslashes($department->nama_departemen) }}', '{{ route('admin.departments.destroy', $department) }}')"
                                        class="inline-flex items-center gap-1.5 text-xs font-semibold text-red-600 bg-red-50 hover:bg-red-100 border border-red-200 px-3 py-1.5 rounded-md transition">
                                        <i class="fas fa-trash-can text-[10px]"></i> Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="py-14 text-center">
                                <div class="flex flex-col items-center gap-2 text-gray-500">
                                    <i class="fas fa-folder-open text-3xl"></i>
                                    <p class="text-sm font-medium">Belum ada departemen</p>
                                    <p class="text-xs">Klik "Tambah Departemen" untuk memulai</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if(method_exists($departments, 'links'))
        <div class="px-5 py-3.5 border-t border-gray-100 flex items-center justify-between">
            <p class="text-xs text-gray-500">
                Menampilkan {{ $departments->firstItem() }}–{{ $departments->lastItem() }} dari {{ $departments->total() }} data
            </p>
            {{ $departments->withQueryString()->links() }}
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
        to   { opacity: 1; transform: scale(1) translateY(0); }
    }
</style>

<script>
    // ── Modal helpers ──────────────────────────────────────────────────────
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
    // Close on backdrop click
    ['modal-tambah','modal-edit','modal-hapus'].forEach(id => {
        document.getElementById(id).addEventListener('click', function(e) {
            if (e.target === this) closeModal(id);
        });
    });

    // ── Edit modal ─────────────────────────────────────────────────────────
    function openEdit(id, nama, actionUrl) {
        document.getElementById('edit-nama').value = nama;
        document.getElementById('form-edit').action = actionUrl;
        openModal('modal-edit');
        setTimeout(() => document.getElementById('edit-nama').focus(), 100);
    }

    // ── Hapus modal ────────────────────────────────────────────────────────
    function openHapus(id, nama, actionUrl) {
        document.getElementById('hapus-nama').textContent = nama;
        document.getElementById('form-hapus').action = actionUrl;
        openModal('modal-hapus');
    }

    // ── Checkbox bulk select ───────────────────────────────────────────────
    function toggleAll(master) {
        document.querySelectorAll('.row-check').forEach(cb => cb.checked = master.checked);
        updateBulkBar();
    }
    function updateBulkBar() {
        const checked = document.querySelectorAll('.row-check:checked');
        const badge   = document.getElementById('selected-badge');
        const btn     = document.getElementById('btn-bulk-delete');
        const master  = document.getElementById('check-all');

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

    // ── Bulk delete (placeholder – sesuaikan dengan endpoint Anda) ─────────
    function bulkDelete() {
        const ids = [...document.querySelectorAll('.row-check:checked')].map(cb => cb.value);
        if (!ids.length) return;
        if (!confirm('Hapus ' + ids.length + ' departemen yang dipilih?')) return;

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.departments.bulk-delete") }}';
        form.innerHTML = `@csrf @method('DELETE')`;
        ids.forEach(id => {
            const inp = document.createElement('input');
            inp.type = 'hidden'; inp.name = 'ids[]'; inp.value = id;
            form.appendChild(inp);
        });
        document.body.appendChild(form);
        form.submit();
    }

    // ── Auto-open modal jika ada error validasi ────────────────────────────
    @if($errors->any())
        openModal('modal-tambah');
    @endif
</script>

@endsection
