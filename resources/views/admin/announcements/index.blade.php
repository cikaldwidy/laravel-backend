@extends('layouts.admin')

@section('title', 'Pengumuman')

@section('content')
<div class="space-y-6">
    <div class="bg-white p-6 rounded-xl shadow">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Buat Pengumuman</h2>
        <form method="POST" action="{{ route('admin.announcements.store') }}" class="grid md:grid-cols-2 gap-4 announcement-form">
            @csrf
            <label class="md:col-span-2">
                <span class="block text-xs font-semibold text-gray-600 mb-1">Judul</span>
                <input name="judul" placeholder="Judul" class="border rounded px-3 py-2 w-full">
            </label>
            <label class="md:col-span-2">
                <span class="block text-xs font-semibold text-gray-600 mb-1">Isi Pengumuman</span>
                <textarea name="isi" rows="4" placeholder="Isi pengumuman" class="border rounded px-3 py-2 w-full"></textarea>
            </label>
            <label>
                <span class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Mulai</span>
                <input type="date" name="tanggal_mulai" class="border rounded px-3 py-2 w-full">
            </label>
            <label>
                <span class="block text-xs font-semibold text-gray-600 mb-1">Tanggal Berakhir</span>
                <input type="date" name="tanggal_berakhir" class="border rounded px-3 py-2 w-full">
            </label>
            <label>
                <span class="block text-xs font-semibold text-gray-600 mb-1">Target</span>
                <select name="target_type" class="border rounded px-3 py-2 w-full js-target-type">
                    <option value="all">Semua User</option>
                    <option value="unit">Per Unit</option>
                    <option value="users">Khusus User</option>
                </select>
            </label>
            <label class="js-unit-field hidden">
                <span class="block text-xs font-semibold text-gray-600 mb-1">Unit</span>
                <select name="unit_id" class="border rounded px-3 py-2 w-full">
                    <option value="">Pilih Unit</option>
                    @foreach($units as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->nama_unit }}</option>
                    @endforeach
                </select>
            </label>
            <label class="md:col-span-2 js-users-field hidden">
                <span class="block text-xs font-semibold text-gray-600 mb-1">User Khusus</span>
                <select name="user_ids[]" multiple class="border rounded px-3 py-2 w-full min-h-32">
                    @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                    @endforeach
                </select>
                <span class="block mt-1 text-xs text-gray-500">Tahan Ctrl untuk memilih lebih dari satu user.</span>
            </label>
            <button class="bg-blue-600 text-white px-4 py-2 rounded font-semibold md:col-span-2">Publish</button>
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
                        <option value="unit" @selected($announcement->target_type === 'unit')>Per Unit</option>
                        <option value="users" @selected($announcement->target_type === 'users')>Khusus User</option>
                    </select>
                    <select name="unit_id" class="border rounded px-3 py-2 js-unit-field">
                        <option value="">Pilih Unit</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" @selected((string) $announcement->unit_id === (string) $unit->id)>{{ $unit->nama_unit }}</option>
                        @endforeach
                    </select>
                    <select name="user_ids[]" multiple class="border rounded px-3 py-2 md:col-span-2 min-h-32 js-users-field">
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected($announcement->users->contains('id', $user->id))>{{ $user->name }}</option>
                        @endforeach
                    </select>
                    <label class="flex items-center gap-2 md:col-span-2">
                        <input type="checkbox" name="is_published" value="1" @checked($announcement->is_published)>
                        <span>Published</span>
                    </label>
                    <div class="flex gap-3 md:col-span-2">
                        <button class="bg-amber-500 text-white px-4 py-2 rounded font-semibold">Update</button>
                    </div>
                </form>
                <form method="POST" action="{{ route('admin.announcements.destroy', $announcement) }}" class="mt-3" onsubmit="return confirm('Hapus pengumuman ini?')">
                    @csrf
                    @method('DELETE')
                    <button class="text-red-600 font-semibold">Hapus</button>
                </form>
            </div>
        @empty
            <div class="bg-white p-6 rounded-xl shadow text-gray-500">Belum ada pengumuman.</div>
        @endforelse
    </div>
</div>

<script>
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
