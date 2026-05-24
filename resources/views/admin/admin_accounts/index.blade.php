@extends('layouts.admin')

@section('title', 'Akun Admin')

@section('content')
<div id="modal-hapus" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="animate-modal mx-4 w-full max-w-sm rounded-md bg-white p-6 shadow-md">
        <div class="mb-6 flex flex-col items-center gap-3 text-center">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100">
                <i class="fas fa-trash-can text-lg text-red-500"></i>
            </div>
            <h2 class="text-base font-bold text-gray-700">Hapus Akun Admin?</h2>
            <p class="text-sm text-gray-500">Akun <span id="hapus-nama" class="font-semibold text-gray-700"></span> akan dihapus secara permanen.</p>
        </div>
        <form method="POST" id="form-hapus">
            @csrf
            @method('DELETE')
            <div class="flex gap-2">
                <button type="button" onclick="closeModal('modal-hapus')" class="flex-1 rounded-md bg-gray-100 px-4 py-2 text-sm font-semibold text-gray-600 transition hover:bg-gray-200">Batal</button>
                <button type="submit" class="flex-1 rounded-md bg-red-500 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-red-600">Hapus</button>
            </div>
        </form>
    </div>
</div>

<div class="space-y-5">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-gray-700">Akun Admin</h1>
            <p class="mt-0.5 text-sm text-gray-500">Kelola akun administrator terpisah dari akun pegawai.</p>
        </div>
        <a href="{{ route('admin.settings.admin_accounts.create') }}" class="inline-flex items-center justify-center gap-2 rounded-md bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow transition hover:bg-blue-700">
            <i class="fa-solid fa-plus text-xs"></i>
            Tambah Admin
        </a>
    </div>

    @if(session('success'))
        <div class="rounded-md border border-blue-100 bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3.5">
            <div class="flex items-center gap-3">
                <span class="text-sm font-semibold text-gray-700">Daftar Akun Admin</span>
                <span id="selected-badge" class="hidden rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700"></span>
            </div>
            <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-500">{{ method_exists($admins, 'total') ? $admins->total() : $admins->count() }} data</span>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-gray-100 bg-gray-50 text-xs font-semibold uppercase tracking-wider text-gray-500">
                        <th class="w-10 px-5 py-3 text-left">
                            <input type="checkbox" id="check-all" onchange="toggleAll(this)" class="h-4 w-4 cursor-pointer rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        </th>
                        <th class="px-5 py-3 text-left">Admin</th>
                        <th class="px-5 py-3 text-left">Role</th>
                        <th class="px-5 py-3 text-left">Dibuat</th>
                        <th class="px-5 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50">
                    @forelse($admins as $admin)
                        <tr class="transition hover:bg-gray-50/70">
                            <td class="px-5 py-3.5">
                                <input type="checkbox" name="selected[]" value="{{ $admin->id }}" onchange="updateSelectBar()" class="row-check h-4 w-4 cursor-pointer rounded border-gray-300 text-blue-600 focus:ring-blue-500" @disabled($admin->id === auth()->id())>
                            </td>
                            <td class="px-5 py-3.5">
                                <div class="font-medium text-gray-700">{{ $admin->name }}</div>
                                <div class="text-xs text-gray-500">{{ $admin->email }}</div>
                            </td>
                            <td class="px-5 py-3.5">
                                <span class="inline-flex rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">Admin</span>
                            </td>
                            <td class="px-5 py-3.5 text-gray-500">{{ $admin->created_at?->format('d/m/Y') ?? '-' }}</td>
                            <td class="px-5 py-3.5">
                                <div class="flex justify-end gap-1.5">
                                    <a href="{{ route('admin.settings.admin_accounts.edit', $admin) }}" class="inline-flex items-center gap-1.5 rounded-md border border-blue-200 bg-blue-50 px-3 py-1.5 text-xs font-semibold text-blue-600 transition hover:bg-blue-100">
                                        <i class="fas fa-pen text-[10px]"></i> Edit
                                    </a>
                                    <button
                                        type="button"
                                        onclick="openHapus(@js($admin->name), @js(route('admin.settings.admin_accounts.destroy', $admin)))"
                                        class="inline-flex items-center gap-1.5 rounded-md border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-100"
                                        @disabled($admin->id === auth()->id())>
                                        <i class="fas fa-trash-can text-[10px]"></i> Hapus
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-14 text-center">
                                <div class="flex flex-col items-center gap-2 text-gray-500">
                                    <i class="fas fa-user-shield text-3xl"></i>
                                    <p class="text-sm font-medium">Belum ada akun admin</p>
                                    <p class="text-xs">Klik "Tambah Admin" untuk memulai.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(method_exists($admins, 'links'))
            <div class="border-t border-gray-100 px-5 py-3.5">
                {{ $admins->withQueryString()->links() }}
            </div>
        @endif
    </div>
</div>

<style>
    .animate-modal { animation: modalIn .2s ease; }
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
function openHapus(nama, actionUrl) {
    document.getElementById('hapus-nama').textContent = nama;
    document.getElementById('form-hapus').action = actionUrl;
    openModal('modal-hapus');
}
function toggleAll(master) {
    document.querySelectorAll('.row-check:not(:disabled)').forEach(cb => cb.checked = master.checked);
    updateSelectBar();
}
function updateSelectBar() {
    const checked = document.querySelectorAll('.row-check:checked');
    const badge = document.getElementById('selected-badge');
    const master = document.getElementById('check-all');
    const all = document.querySelectorAll('.row-check:not(:disabled)');
    master.indeterminate = checked.length > 0 && checked.length < all.length;
    master.checked = checked.length === all.length && all.length > 0;
    if (checked.length > 0) {
        badge.textContent = checked.length + ' dipilih';
        badge.classList.remove('hidden');
    } else {
        badge.classList.add('hidden');
    }
}
</script>
@endsection
