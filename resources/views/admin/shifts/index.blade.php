@extends('layouts.admin')

@section('title', 'Master Shift')

@section('content')
<div class="space-y-4">
    <div class="flex items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-bold text-gray-800">Master Shift</h2>
            <p class="text-sm text-gray-500">Kelola daftar shift (jam masuk dan jam pulang).</p>
        </div>
        <a href="{{ route('admin.shifts.create') }}" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-semibold text-sm">
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
                                <form method="POST" action="{{ route('admin.shifts.destroy', $shift) }}" onsubmit="return confirm('Hapus shift ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="px-3 py-1 rounded bg-red-50 text-red-700 hover:bg-red-100 text-xs font-semibold">
                                        Hapus
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="p-8 text-center text-gray-500">Belum ada data shift.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
