@extends('layouts.admin')

@section('title', 'Pengumuman')

@section('content')
@php
    $createTargetType = old('target_type', 'all');
    $createUserIds = collect(old('user_ids', []))->map(fn ($id) => (string) $id);
@endphp
<div class="space-y-6">
    <div class="bg-white p-6 rounded-xl shadow">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Buat Pengumuman</h2>
        <form method="POST" action="{{ route('admin.announcements.store') }}" class="grid md:grid-cols-2 gap-4 announcement-form">
            @csrf
            <label class="md:col-span-2">
                <span class="block text-xs font-semibold text-gray-600 mb-1">Judul</span>
                <input name="judul" value="{{ old('judul') }}" placeholder="Judul" class="border rounded px-3 py-2 w-full">
                @error('judul')
                    <span class="block mt-1 text-xs text-red-600">{{ $message }}</span>
                @enderror
            </label>
            <label class="md:col-span-2">
                <span class="block text-xs font-semibold text-gray-600 mb-1">Isi Pengumuman</span>
                <textarea name="isi" rows="4" placeholder="Isi pengumuman" class="border rounded px-3 py-2 w-full">{{ old('isi') }}</textarea>
                @error('isi')
                    <span class="block mt-1 text-xs text-red-600">{{ $message }}</span>
                @enderror
            </label>
            <label>
                <span class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Mulai</span>
                <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}" class="border rounded px-3 py-2 w-full">
                @error('tanggal_mulai')
                    <span class="block mt-1 text-xs text-red-600">{{ $message }}</span>
                @enderror
            </label>
            <label>
                <span class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Berakhir</span>
                <input type="date" name="tanggal_berakhir" value="{{ old('tanggal_berakhir') }}" class="border rounded px-3 py-2 w-full">
                @error('tanggal_berakhir')
                    <span class="block mt-1 text-xs text-red-600">{{ $message }}</span>
                @enderror
            </label>
            <label>
                <span class="block text-xs font-semibold text-gray-600 mb-1">Target</span>
                <select name="target_type" class="border rounded px-3 py-2 w-full js-target-type">
                    <option value="all" @selected($createTargetType === 'all')>Semua User</option>
                    <option value="unit" @selected($createTargetType === 'unit')>Per Unit Kerja/Bagian</option>
                    <option value="users" @selected($createTargetType === 'users')>Khusus User</option>
                </select>
                @error('target_type')
                    <span class="block mt-1 text-xs text-red-600">{{ $message }}</span>
                @enderror
            </label>
            <label class="js-unit-field hidden">
                <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500">Unit Kerja/Bagian</span>
                <select name="unit_id" class="w-full rounded-md border border-gray-200 px-3.5 py-2.5 text-sm text-gray-800 transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Pilih Unit Kerja/Bagian</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}" @selected((string) old('unit_id') === (string) $unit->id)>{{ $unit->nama_departemen }}</option>
                    @endforeach
                </select>
                @error('unit_id')
                    <span class="block mt-1 text-xs text-red-600">{{ $message }}</span>
                @enderror
            </label>
            <label class="js-users-field hidden md:col-span-2">
                <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-500">User Khusus</span>
                <select name="user_ids[]" multiple class="min-h-32 w-full rounded-md border border-gray-200 px-3.5 py-2.5 text-sm text-gray-800 transition focus:border-transparent focus:outline-none focus:ring-2 focus:ring-blue-500">
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected($createUserIds->contains((string) $user->id))>{{ $user->name }}</option>
                    @endforeach
                </select>
                <span class="block mt-1 text-xs text-gray-500">Tahan Ctrl untuk memilih lebih dari satu user.</span>
                @error('user_ids')
                    <span class="block mt-1 text-xs text-red-600">{{ $message }}</span>
                @enderror
            </label>
            <label class="md:col-span-2">
                <span class="block text-xs font-semibold text-gray-600 mb-1">URL Saat Notifikasi Diklik</span>
                <input name="action_url" value="{{ old('action_url', '/pengumuman') }}" placeholder="/pengumuman" class="border rounded px-3 py-2 w-full">
                <span class="block mt-1 text-xs text-gray-500">Gunakan path aplikasi, misalnya /pengumuman, /jadwal-shift, atau /tukar-shift.</span>
                @error('action_url')
                    <span class="block mt-1 text-xs text-red-600">{{ $message }}</span>
                @enderror
            </label>
            <div class="flex justify-end border-t border-gray-100 pt-4 md:col-span-2">
                <button class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                    <i class="fas fa-paper-plane text-xs"></i>
                    Publish
                </button>
            </div>
        </form>
    </div>

    <div class="space-y-4">
        @forelse($announcements as $announcement)
            <div class="bg-white p-6 rounded-xl shadow">
                <form method="POST" action="{{ route('admin.announcements.update', $announcement) }}" class="grid md:grid-cols-2 gap-4 announcement-form">
                    @csrf
                    @method('PUT')
                    <input name="judul" value="{{ $announcement->judul }}" class="border rounded px-3 py-2 md:col-span-2">
                    <textarea name="isi" rows="3" class="border rounded px-3 py-2 md:col-span-2">{{ $announcement->isi }}</textarea>
                    <input type="date" name="tanggal_mulai" value="{{ $announcement->tanggal_mulai->toDateString() }}" class="border rounded px-3 py-2">
                    <input type="date" name="tanggal_berakhir" value="{{ $announcement->tanggal_berakhir->toDateString() }}" class="border rounded px-3 py-2">
                    <select name="target_type" class="border rounded px-3 py-2 js-target-type">
                        <option value="all" @selected($announcement->target_type === 'all')>Semua User</option>
                        <option value="unit" @selected($announcement->target_type === 'unit')>Per Unit Kerja/Bagian</option>
                        <option value="users" @selected($announcement->target_type === 'users')>Khusus User</option>
                    </select>
                    <select name="unit_id" class="border rounded px-3 py-2 js-unit-field">
                        <option value="">Pilih Unit Kerja/Bagian</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" @selected((string) $announcement->unit_id === (string) $unit->id)>{{ $unit->nama_departemen }}</option>
                        @endforeach
                    </select>
                    <select name="user_ids[]" multiple class="border rounded px-3 py-2 md:col-span-2 min-h-32 js-users-field">
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected($announcement->users->contains('id', $user->id))>{{ $user->name }}</option>
                        @endforeach
                    </select>
                    <input name="action_url" value="{{ old('action_url', $announcement->action_url ?? '/pengumuman') }}" placeholder="/pengumuman" class="border rounded px-3 py-2 md:col-span-2">
                    <label class="flex items-center gap-2 md:col-span-2">
                        <input type="checkbox" name="is_published" value="1" @checked($announcement->is_published)>
                        <span>Published</span>
                    </label>
                    <div class="flex gap-3 md:col-span-2">
                        <button class="bg-amber-500 text-white px-4 py-2 rounded font-semibold">Update</button>
                    </div>
                </form>
                <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}" class="mt-3" data-confirm-form data-confirm-title="Hapus pengumuman?" data-confirm-message="Pengumuman ini akan dihapus dari daftar aktif." data-confirm-button="Hapus">
                    @csrf
                    @method('DELETE')
                    <button class="text-red-600 font-semibold">Hapus</button>
                </form>
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

        unitField?.querySelectorAll('select, input, textarea').forEach((field) => {
            field.disabled = value !== 'unit';
        });
        usersField?.querySelectorAll('select, input, textarea').forEach((field) => {
            field.disabled = value !== 'users';
        });
    }

    targetEl?.addEventListener('change', syncTargetFields);
    syncTargetFields();
});
</script>
@endsection
