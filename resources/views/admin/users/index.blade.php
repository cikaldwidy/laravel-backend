@extends('layouts.admin')

@section('title', 'Akun Pegawai')

@section('content')
<div id="modal-hapus" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="animate-modal mx-4 w-full max-w-sm rounded-md bg-white p-6 shadow-md">
        <div class="mb-6 flex flex-col items-center gap-3 text-center">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100">
                <i class="fas fa-trash-can text-lg text-red-500"></i>
            </div>
            <h2 class="text-base font-bold text-gray-700">Hapus Akun Pegawai?</h2>
            <p class="text-sm text-gray-500">Akun <span id="hapus-nama" class="font-semibold text-gray-700"></span> akan dihapus secara permanen.</p>
        </div>
        <form method="POST" id="form-hapus">
            @csrf
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
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-gray-700">Akun Pegawai</h1>
            <p class="mt-0.5 text-sm text-gray-500">Kelola akses login dan kelengkapan data pegawai.</p>
        </div>
        <a href="{{ route('admin.users.create') }}" class="inline-flex items-center justify-center gap-2 rounded-md bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow transition hover:bg-blue-700">
            <i class="fa-solid fa-plus text-xs"></i>
            Tambah Akun
        </a>
    </div>

    <div class="rounded-md border border-gray-200 bg-white p-4 shadow-sm">
        <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-gray-500">Filter & Pencarian</p>
        <form method="GET" data-auto-filter class="grid gap-3 md:grid-cols-4">
            <div>
                <label class="mb-2 block text-xs font-semibold text-gray-500">Cari Pegawai</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-xs text-gray-400"></i>
                    <input
                        type="search"
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Nama, email, NIP, unit, jabatan..."
                        class="w-full rounded-md border border-gray-200 py-2 pl-8 pr-3 text-sm text-gray-700 transition focus:border-gray-500 focus:outline-none"
                    >
                </div>
            </div>
            <div>
                <label class="mb-2 block text-xs font-semibold text-gray-500">Unit Kerja/Bagian</label>
                <select name="unit" class="w-full rounded-md border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 transition focus:border-gray-500 focus:outline-none">
                    <option value="">Semua Unit Kerja/Bagian</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit }}" @selected(request('unit') === $unit)>{{ $unit }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="mb-2 block text-xs font-semibold text-gray-500">Biodata</label>
                <select name="biodata" class="w-full rounded-md border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 transition focus:border-gray-500 focus:outline-none">
                    <option value="">Semua</option>
                    <option value="lengkap" @selected(request('biodata') === 'lengkap')>Lengkap</option>
                    <option value="sebagian" @selected(request('biodata') === 'sebagian')>Sebagian</option>
                    <option value="belum" @selected(request('biodata') === 'belum')>Belum ada</option>
                </select>
            </div>
            <div>
                <label class="mb-2 block text-xs font-semibold text-gray-500">Wajah</label>
                <select name="wajah" class="w-full rounded-md border border-gray-200 bg-white px-3 py-2 text-sm text-gray-700 transition focus:border-gray-500 focus:outline-none">
                    <option value="">Semua</option>
                    <option value="terdaftar" @selected(request('wajah') === 'terdaftar')>Terdaftar</option>
                    <option value="belum" @selected(request('wajah') === 'belum')>Belum</option>
                </select>
            </div>
            @if(request()->hasAny(['search', 'unit', 'biodata', 'wajah']))
                <div class="md:col-span-4">
                    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-1.5 rounded-md border border-gray-300 px-3 py-2 text-sm text-gray-500 transition hover:bg-gray-100 hover:text-gray-700">
                        <i class="fas fa-xmark text-xs"></i> Reset
                    </a>
                </div>
            @endif
        </form>
    </div>

    <div class="overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3.5">
            <div class="flex items-center gap-3">
                <span class="text-sm font-semibold text-gray-700">Daftar Akun Pegawai</span>
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
                        <th class="px-5 py-3 text-left">Pegawai</th>
                        <th class="px-5 py-3 text-left">Unit Kerja/Bagian</th>
                        <th class="px-5 py-3 text-left">Jabatan</th>
                        <th class="px-5 py-3 text-left">Biodata</th>
                        <th class="px-5 py-3 text-left">Wajah</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($users as $u)
                        @php
                            $profile = $u->userProfile;
                            $detail = $u->employeeDetail;
                            $hasProfile = (bool) $profile;
                            $hasDetail = (bool) $detail;
                            $biodataComplete = $hasProfile && $hasDetail;
                        @endphp
                        <tr class="transition hover:bg-gray-50/70">
                            <td class="px-5 py-3.5">
                                <input type="checkbox" name="selected[]" value="{{ $u->id }}" onchange="updateBulkBar()" class="row-check h-4 w-4 cursor-pointer rounded border-gray-300 text-blue-600 focus:ring-blue-500" @disabled($u->id === auth()->id())>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-sm font-bold text-blue-700">
                                        {{ strtoupper(substr($u->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-gray-800">{{ $u->name }}</div>
                                        <div class="text-xs text-gray-500">{{ $u->email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-3.5 font-medium text-gray-600">{{ $detail?->department?->nama_departemen ?? $detail?->departemen ?? '-' }}</td>
                            <td class="px-5 py-3.5 font-medium text-gray-600">{{ $detail?->position?->nama_jabatan ?? $detail?->jabatan ?? '-' }}</td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $biodataComplete ? 'bg-green-50 text-green-700' : (($hasProfile || $hasDetail) ? 'bg-yellow-50 text-yellow-700' : 'bg-gray-100 text-gray-500') }}">
                                    {{ $biodataComplete ? 'Lengkap' : (($hasProfile || $hasDetail) ? 'Sebagian' : 'Belum ada') }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex rounded-full px-2.5 py-1 text-xs font-semibold {{ $u->faceEmbedding ? 'bg-green-50 text-green-700' : 'bg-gray-100 text-gray-500' }}">
                                    {{ $u->faceEmbedding ? 'Terdaftar' : 'Belum' }}
                                </span>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="flex items-center justify-end gap-1.5">
                                    <a href="{{ route('admin.users.show', $u->id) }}" class="inline-flex items-center gap-1.5 rounded-md border border-gray-200 bg-white px-3 py-1.5 text-xs font-semibold text-gray-600 transition hover:bg-gray-50">
                                        <i class="fas fa-eye text-[10px]"></i> Detail
                                    </a>
                                    <a href="{{ route('admin.users.edit', $u->id) }}" class="inline-flex items-center gap-1.5 rounded-md border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-600 transition hover:bg-blue-100">
                                        <i class="fas fa-pen text-[10px]"></i> Akun
                                    </a>
                                    <a href="{{ route('admin.biodata.edit', $u) }}" class="inline-flex items-center gap-1.5 rounded-md border border-emerald-200 bg-emerald-50 px-3 py-1.5 text-xs font-semibold text-emerald-600 transition hover:bg-emerald-100">
                                        <i class="fas fa-id-card text-[10px]"></i> Biodata
                                    </a>
                                    <button
                                        type="button"
                                        onclick="openHapus('{{ $u->id }}', '{{ addslashes($u->name) }}', '{{ route('admin.users.destroy', $u->id) }}')"
                                        class="inline-flex items-center gap-1.5 rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-100"
                                        @disabled($u->id === auth()->id())
                                    >
                                        <i class="fas fa-trash-can text-[10px]"></i> Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="py-14 text-center">
                                <div class="flex flex-col items-center gap-2 text-gray-500">
                                    <i class="fas fa-users text-3xl"></i>
                                    <p class="text-sm font-medium">Belum ada akun pegawai</p>
                                    <p class="text-xs">Klik "Tambah Akun" untuk memulai</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($users, 'links'))
            <div class="flex items-center justify-between border-t border-gray-100 px-5 py-3.5">
                <p class="text-xs text-gray-500">
                    Menampilkan {{ $users->firstItem() }}-{{ $users->lastItem() }} dari {{ $users->total() }} data
                </p>
                {{ $users->withQueryString()->links() }}
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

    document.getElementById('modal-hapus').addEventListener('click', function (event) {
        if (event.target === this) closeModal('modal-hapus');
    });

    function openHapus(id, nama, actionUrl) {
        document.getElementById('hapus-nama').textContent = nama;
        document.getElementById('form-hapus').action = actionUrl;
        openModal('modal-hapus');
    }

    function toggleAll(master) {
        document.querySelectorAll('.row-check:not(:disabled)').forEach(cb => cb.checked = master.checked);
        updateBulkBar();
    }

    function updateBulkBar() {
        const checked = document.querySelectorAll('.row-check:checked');
        const badge = document.getElementById('selected-badge');
        const btn = document.getElementById('btn-bulk-delete');
        const master = document.getElementById('check-all');
        const all = document.querySelectorAll('.row-check:not(:disabled)');

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
        if (!confirm('Hapus ' + ids.length + ' akun pegawai yang dipilih?')) return;

        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.users.bulk-delete") }}';
        form.innerHTML = `@csrf`;

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
</script>
@endsection
