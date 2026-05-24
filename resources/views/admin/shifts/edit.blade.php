@extends('layouts.admin')

@section('title', 'Edit Shift')

@section('content')
<div class="space-y-6">
    <div>
        <a href="{{ route('admin.shifts.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-500 transition hover:text-blue-600">
            <i class="fa-solid fa-arrow-left text-xs"></i>
            Kembali ke Master Shift
        </a>
        <div class="mt-3 flex items-center gap-3">
            <span class="flex h-12 w-12 items-center justify-center rounded-md bg-gradient-to-br from-blue-600 to-sky-500 text-white shadow-lg shadow-blue-500/20">
                <i class="fa-solid fa-pen text-lg"></i>
            </span>
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Edit Shift</h1>
                <p class="mt-1 text-sm text-gray-500">Perbarui template shift dan rentang jam kerja.</p>
            </div>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_340px]">
        <section class="overflow-hidden rounded-md border border-blue-100 bg-white shadow-sm">
            <div class="border-b border-blue-50 px-6 py-5">
                <h2 class="text-lg font-bold text-gray-900">Informasi Shift</h2>
                <p class="mt-1 text-sm text-gray-500">Edit nama shift, jam masuk, dan jam pulang.</p>
            </div>

            <form method="POST" action="{{ route('admin.shifts.update', $shift) }}" class="space-y-5 p-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="nama_shift" class="mb-2 block text-sm font-semibold text-gray-700">Nama Shift</label>
                    <input
                        id="nama_shift"
                        name="nama_shift"
                        value="{{ old('nama_shift', $shift->nama_shift) }}"
                        class="h-11 w-full rounded-md border border-blue-100 bg-white px-3 text-sm font-medium text-gray-700 outline-none transition placeholder:text-gray-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                    >
                    @error('nama_shift') <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="jam_masuk" class="mb-2 block text-sm font-semibold text-gray-700">Jam Masuk</label>
                        <input
                            id="jam_masuk"
                            type="time"
                            name="jam_masuk"
                            value="{{ old('jam_masuk', \Illuminate\Support\Str::of($shift->jam_masuk)->substr(0, 5)) }}"
                            class="h-11 w-full rounded-md border border-blue-100 bg-white px-3 text-sm font-medium text-gray-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                        >
                        @error('jam_masuk') <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label for="jam_pulang" class="mb-2 block text-sm font-semibold text-gray-700">Jam Pulang</label>
                        <input
                            id="jam_pulang"
                            type="time"
                            name="jam_pulang"
                            value="{{ old('jam_pulang', \Illuminate\Support\Str::of($shift->jam_pulang)->substr(0, 5)) }}"
                            class="h-11 w-full rounded-md border border-blue-100 bg-white px-3 text-sm font-medium text-gray-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100"
                        >
                        @error('jam_pulang') <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="flex flex-col-reverse gap-2 border-t border-blue-50 pt-5 sm:flex-row sm:justify-end">
                    <a href="{{ route('admin.shifts.index') }}" class="inline-flex h-11 items-center justify-center rounded-md border border-gray-200 bg-white px-5 text-sm font-semibold text-gray-600 transition hover:bg-gray-50">
                        Batal
                    </a>
                    <button class="inline-flex h-11 items-center justify-center gap-2 rounded-md bg-gradient-to-r from-blue-600 to-sky-500 px-5 text-sm font-semibold text-white shadow-lg shadow-blue-500/20 transition hover:from-blue-700 hover:to-sky-600">
                        <i class="fa-solid fa-floppy-disk text-xs"></i>
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </section>

        <aside class="space-y-4">
            <div class="rounded-md border border-blue-100 bg-white p-5 shadow-sm">
                <p class="font-semibold text-gray-900">Shift Saat Ini</p>
                <div class="mt-4 rounded-md bg-blue-50 p-4">
                    <p class="text-sm font-semibold text-blue-700">{{ $shift->nama_shift }}</p>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ \Illuminate\Support\Str::of($shift->jam_masuk)->substr(0, 5) }}
                        -
                        {{ \Illuminate\Support\Str::of($shift->jam_pulang)->substr(0, 5) }}
                    </p>
                </div>
            </div>

            <div class="rounded-md border border-blue-100 bg-white p-5 shadow-sm">
                <p class="font-semibold text-gray-900">Catatan</p>
                <p class="mt-2 text-sm leading-relaxed text-gray-500">Perubahan template shift dapat memengaruhi jadwal yang memakai data shift ini.</p>
            </div>
        </aside>
    </div>
</div>
@endsection
