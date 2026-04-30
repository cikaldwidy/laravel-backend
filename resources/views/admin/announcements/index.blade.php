@extends('layouts.admin')

@section('title', 'Pengumuman')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="bg-green-100 text-green-800 px-4 py-3 rounded">{{ session('success') }}</div>
    @endif

    <div class="bg-white p-6 rounded-xl shadow">
        <h2 class="text-lg font-bold text-gray-800 mb-4">Buat Pengumuman</h2>
        <form method="POST" action="{{ route('admin.announcements.store') }}" class="grid md:grid-cols-2 gap-4">
            @csrf
            <input name="judul" placeholder="Judul" class="border rounded px-3 py-2 md:col-span-2">
            <textarea name="isi" rows="4" placeholder="Isi pengumuman" class="border rounded px-3 py-2 md:col-span-2"></textarea>
            <input type="date" name="tanggal_mulai" class="border rounded px-3 py-2">
            <input type="date" name="tanggal_berakhir" class="border rounded px-3 py-2">
            <select name="target_type" class="border rounded px-3 py-2">
                <option value="all">Semua User</option>
                <option value="unit">Per Unit</option>
            </select>
            <select name="unit_id" class="border rounded px-3 py-2">
                <option value="">Pilih Unit</option>
                @foreach($units as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->nama_unit }}</option>
                @endforeach
            </select>
            <button class="bg-blue-600 text-white px-4 py-2 rounded font-semibold md:col-span-2">Publish</button>
        </form>
    </div>

    <div class="space-y-4">
        @forelse($announcements as $announcement)
            <div class="bg-white p-6 rounded-xl shadow">
                <form method="POST" action="{{ route('admin.announcements.update', $announcement) }}" class="grid md:grid-cols-2 gap-4">
                    @csrf
                    @method('PUT')
                    <input name="judul" value="{{ $announcement->judul }}" class="border rounded px-3 py-2 md:col-span-2">
                    <textarea name="isi" rows="3" class="border rounded px-3 py-2 md:col-span-2">{{ $announcement->isi }}</textarea>
                    <input type="date" name="tanggal_mulai" value="{{ $announcement->tanggal_mulai->toDateString() }}" class="border rounded px-3 py-2">
                    <input type="date" name="tanggal_berakhir" value="{{ $announcement->tanggal_berakhir->toDateString() }}" class="border rounded px-3 py-2">
                    <select name="target_type" class="border rounded px-3 py-2">
                        <option value="all" @selected($announcement->target_type === 'all')>Semua User</option>
                        <option value="unit" @selected($announcement->target_type === 'unit')>Per Unit</option>
                    </select>
                    <select name="unit_id" class="border rounded px-3 py-2">
                        <option value="">Pilih Unit</option>
                        @foreach($units as $unit)
                            <option value="{{ $unit->id }}" @selected((string) $announcement->unit_id === (string) $unit->id)>{{ $unit->nama_unit }}</option>
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
@endsection
