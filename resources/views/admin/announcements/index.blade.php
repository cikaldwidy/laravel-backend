@extends('layouts.admin')

@section('title', 'Pengumuman')

@section('content')
<div id="modal-hapus" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/40 backdrop-blur-sm">
    <div class="animate-modal mx-4 w-full max-w-sm rounded-md bg-white p-6 shadow-md">
        <div class="mb-6 flex flex-col items-center gap-3 text-center">
            <div class="flex h-12 w-12 items-center justify-center rounded-full bg-red-100">
                <i class="fas fa-trash-can text-lg text-red-500"></i>
            </div>
            <h2 class="text-base font-bold text-gray-700">Hapus Pengumuman?</h2>
            <p class="text-sm text-gray-500">Pengumuman <span id="hapus-nama" class="font-semibold text-gray-700"></span> akan dihapus secara permanen.</p>
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
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-gray-700">Pengumuman</h1>
            <p class="mt-0.5 text-sm text-gray-500">Buat dan kelola pengumuman untuk pegawai.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="rounded-md border border-blue-100 bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-3.5">
            <h2 class="text-sm font-semibold text-gray-700">Buat Pengumuman</h2>
        </div>
        <form method="POST" action="{{ route('admin.announcements.store') }}" class="announcement-form grid gap-4 p-5 md:grid-cols-2">
            @csrf
            <label class="md:col-span-2">
                <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500">Judul</span>
                <input name="judul" value="{{ old('judul') }}" placeholder="Judul pengumuman" class="w-full rounded-md border border-gray-200 px-3.5 py-2.5 text-sm text-gray-800 transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('judul') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
            </label>
            <label class="md:col-span-2">
                <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500">Isi Pengumuman</span>
                <textarea name="isi" rows="4" placeholder="Isi pengumuman" class="w-full rounded-md border border-gray-200 px-3.5 py-2.5 text-sm text-gray-800 transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('isi') }}</textarea>
                @error('isi') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
            </label>
            <label>
                <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500">Tanggal Mulai</span>
                <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}" class="w-full rounded-md border border-gray-200 px-3.5 py-2.5 text-sm text-gray-800 transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('tanggal_mulai') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
            </label>
            <label>
                <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500">Tanggal Berakhir</span>
                <input type="date" name="tanggal_berakhir" value="{{ old('tanggal_berakhir') }}" class="w-full rounded-md border border-gray-200 px-3.5 py-2.5 text-sm text-gray-800 transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500">
                @error('tanggal_berakhir') <span class="mt-1 block text-xs text-red-500">{{ $message }}</span> @enderror
            </label>
            <label>
                <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500">Target</span>
                <select name="target_type" class="js-target-type w-full rounded-md border border-gray-200 px-3.5 py-2.5 text-sm text-gray-800 transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="all">Semua User</option>
                    <option value="unit">Per Unit Kerja/Bagian</option>
                    <option value="users">Khusus User</option>
                </select>
            </label>
            <label class="js-unit-field hidden">
                <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500">Unit Kerja/Bagian</span>
                <select name="unit_id" class="w-full rounded-md border border-gray-200 px-3.5 py-2.5 text-sm text-gray-800 transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Pilih Unit Kerja/Bagian</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->nama_departemen }}</option>
                    @endforeach
                </select>
            </label>
            <label class="js-users-field hidden md:col-span-2">
                <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500">User Khusus</span>
                <select name="user_ids[]" multiple class="min-h-32 w-full rounded-md border border-gray-200 px-3.5 py-2.5 text-sm text-gray-800 transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
                <span class="mt-1 block text-xs text-gray-500">Tahan Ctrl untuk memilih lebih dari satu user.</span>
            </label>
            <div class="flex justify-end border-t border-gray-100 pt-4 md:col-span-2">
                <button class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                    <i class="fas fa-paper-plane text-xs"></i>
                    Publish
                </button>
            </div>
        </form>
    </div>

    <div class="overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3.5">
            <span class="text-sm font-semibold text-gray-700">Daftar Pengumuman</span>
            <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-500">{{ $announcements->total() }} data</span>
        </div>

        <div class="divide-y divide-gray-50">
            @forelse($announcements as $announcement)
                <div class="p-5">
                    <form method="POST" action="{{ route('admin.announcements.update', $announcement) }}" class="announcement-form grid gap-4 md:grid-cols-2">
                        @csrf
                        @method('PUT')
                        <input name="judul" value="{{ $announcement->judul }}" class="rounded-md border border-gray-200 px-3.5 py-2.5 text-sm font-semibold text-gray-800 transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500 md:col-span-2">
                        <textarea name="isi" rows="3" class="rounded-md border border-gray-200 px-3.5 py-2.5 text-sm text-gray-800 transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500 md:col-span-2">{{ $announcement->isi }}</textarea>
                        <input type="date" name="tanggal_mulai" value="{{ $announcement->tanggal_mulai->toDateString() }}" class="rounded-md border border-gray-200 px-3.5 py-2.5 text-sm text-gray-800 transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <input type="date" name="tanggal_berakhir" value="{{ $announcement->tanggal_berakhir->toDateString() }}" class="rounded-md border border-gray-200 px-3.5 py-2.5 text-sm text-gray-800 transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <select name="target_type" class="js-target-type rounded-md border border-gray-200 px-3.5 py-2.5 text-sm text-gray-800 transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="all" @selected($announcement->target_type === 'all')>Semua User</option>
                            <option value="unit" @selected($announcement->target_type === 'unit')>Per Unit Kerja/Bagian</option>
                            <option value="users" @selected($announcement->target_type === 'users')>Khusus User</option>
                        </select>
                        <select name="unit_id" class="js-unit-field rounded-md border border-gray-200 px-3.5 py-2.5 text-sm text-gray-800 transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="">Pilih Unit Kerja/Bagian</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" @selected((string) $announcement->unit_id === (string) $unit->id)>{{ $unit->nama_departemen }}</option>
                            @endforeach
                        </select>
                        <select name="user_ids[]" multiple class="js-users-field min-h-28 rounded-md border border-gray-200 px-3.5 py-2.5 text-sm text-gray-800 transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500 md:col-span-2">
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" @selected($announcement->users->contains('id', $user->id))>{{ $user->name }}</option>
                            @endforeach
                        </select>
                        <label class="inline-flex items-center gap-2 text-sm font-semibold text-gray-600 md:col-span-2">
                            <input type="checkbox" name="is_published" value="1" @checked($announcement->is_published) class="h-4 w-4 rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                            Published
                        </label>
                        <div class="flex flex-wrap justify-end gap-2 border-t border-gray-100 pt-4 md:col-span-2">
                            <button class="inline-flex items-center gap-1.5 rounded-md border border-amber-200 bg-amber-50 px-4 py-2 text-sm font-semibold text-amber-600 transition hover:bg-amber-100">
                                <i class="fas fa-pen text-xs"></i>
                                Update
                            </button>
                            <button
                                type="button"
                                onclick="openHapus(@js($announcement->judul), @js(route('admin.announcements.destroy', $announcement)))"
                                class="inline-flex items-center gap-1.5 rounded-md border border-red-200 bg-red-50 px-4 py-2 text-sm font-semibold text-red-600 transition hover:bg-red-100">
                                <i class="fas fa-trash-can text-xs"></i>
                                Hapus
                            </button>
                        </div>
                    </form>
                </div>
            @empty
                <div class="py-14 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-md bg-blue-50 text-blue-500">
                        <i class="fas fa-bullhorn text-xl"></i>
                    </div>
                    <p class="mt-3 text-sm font-semibold text-gray-700">Belum ada pengumuman</p>
                    <p class="text-xs text-gray-500">Buat pengumuman baru untuk mulai memberi informasi.</p>
                </div>
            @endforelse
        </div>

        @if($announcements->hasPages())
            <div class="border-t border-gray-100 px-5 py-3.5">
                {{ $announcements->links() }}
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

function openHapus(nama, actionUrl) {
    document.getElementById('hapus-nama').textContent = nama;
    document.getElementById('form-hapus').action = actionUrl;
    openModal('modal-hapus');
}

document.querySelectorAll('.announcement-form').forEach((form) => {
    const targetEl = form.querySelector('.js-target-type');
    const unitField = form.querySelector('.js-unit-field');
    const usersField = form.querySelector('.js-users-field');

    function syncTargetFields() {
        const value = targetEl?.value;
        unitField?.classList.toggle('hidden', value !== 'unit');
        usersField?.classList.toggle('hidden', value !== 'users');
    }

    targetEl?.addEventListener('change', syncTargetFields);
    syncTargetFields();
});
</script>
@endsection
