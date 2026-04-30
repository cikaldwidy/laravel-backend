@extends('layouts.admin')

@section('title', 'Manajemen Shift')

@section('content')
<div class="space-y-5">
    <div class="bg-white rounded-md shadow border border-gray-200 p-4">
        <form method="GET" class="grid md:grid-cols-4 gap-3 items-end">
            <div>
                <label class="text-xs font-semibold text-gray-600">Tanggal</label>
                <input type="date" name="tanggal" value="{{ $tanggal }}" class="w-full border rounded-md px-3 py-2 text-sm">
            </div>
            <div>
                <label class="text-xs font-semibold text-gray-600">User</label>
                <select name="user_id" class="w-full border rounded-md px-3 py-2 text-sm">
                    <option value="">Semua User</option>
                    @foreach($users as $user)
                        <option value="{{ $user->id }}" @selected((string)request('user_id') === (string)$user->id)>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="md:col-span-2 flex gap-2">
                <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded-md text-sm font-semibold">Filter Monitoring</button>
                <a href="{{ route('admin.shift_management.schedules') }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm font-semibold">Reset</a>
            </div>
        </form>
    </div>

    <div class="grid lg:grid-cols-2 gap-4">
        <div class="bg-white rounded-md shadow border border-gray-200 p-4">
            <h2 class="text-sm font-bold text-gray-700 mb-4">Tambah / Assign Shift Per User</h2>
            <form action="{{ route('admin.shift_management.schedules.store') }}" method="POST" class="space-y-3">
                @csrf
                <div>
                    <label class="text-xs font-semibold text-gray-600">User</label>
                    <select name="user_id" class="w-full border rounded-md px-3 py-2 text-sm" required>
                        <option value="">Pilih user</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-gray-600">Tanggal</label>
                        <input type="date" name="tanggal" class="w-full border rounded-md px-3 py-2 text-sm" value="{{ $tanggal }}" required>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-600">Status</label>
                        <select name="status" class="w-full border rounded-md px-3 py-2 text-sm" required>
                            <option value="aktif">Aktif</option>
                            <option value="libur">Libur</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-600">Template Shift (opsional)</label>
                    <select name="shift_id" class="w-full border rounded-md px-3 py-2 text-sm">
                        <option value="">Manual</option>
                        @foreach($shiftTemplates as $shift)
                            <option value="{{ $shift->id }}">{{ $shift->nama_shift }} ({{ \Illuminate\Support\Str::of($shift->jam_masuk)->substr(0,5) }} - {{ \Illuminate\Support\Str::of($shift->jam_pulang)->substr(0,5) }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-gray-600">Jam Masuk Manual</label>
                        <input type="time" name="jam_masuk" class="w-full border rounded-md px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-600">Jam Pulang Manual</label>
                        <input type="time" name="jam_pulang" class="w-full border rounded-md px-3 py-2 text-sm">
                    </div>
                </div>
                <button type="submit" class="bg-emerald-600 text-white px-4 py-2 rounded-md text-sm font-semibold">Simpan Jadwal</button>
            </form>
        </div>

        <div class="bg-white rounded-md shadow border border-gray-200 p-4">
            <h2 class="text-sm font-bold text-gray-700 mb-4">Bulk Assign Shift</h2>
            <form action="{{ route('admin.shift_management.schedules.bulk_assign') }}" method="POST" class="space-y-3">
                @csrf
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-gray-600">Tanggal</label>
                        <input type="date" name="tanggal" class="w-full border rounded-md px-3 py-2 text-sm" value="{{ $tanggal }}" required>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-600">Status</label>
                        <select name="status" class="w-full border rounded-md px-3 py-2 text-sm" required>
                            <option value="aktif">Aktif</option>
                            <option value="libur">Libur</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="text-xs font-semibold text-gray-600">Template Shift (opsional)</label>
                    <select name="shift_id" class="w-full border rounded-md px-3 py-2 text-sm">
                        <option value="">Manual</option>
                        @foreach($shiftTemplates as $shift)
                            <option value="{{ $shift->id }}">{{ $shift->nama_shift }} ({{ \Illuminate\Support\Str::of($shift->jam_masuk)->substr(0,5) }} - {{ \Illuminate\Support\Str::of($shift->jam_pulang)->substr(0,5) }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-gray-600">Jam Masuk Manual</label>
                        <input type="time" name="jam_masuk" class="w-full border rounded-md px-3 py-2 text-sm">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-600">Jam Pulang Manual</label>
                        <input type="time" name="jam_pulang" class="w-full border rounded-md px-3 py-2 text-sm">
                    </div>
                </div>

                <div>
                    <label class="text-xs font-semibold text-gray-600">Pilih User (bisa banyak)</label>
                    <select name="user_ids[]" multiple size="8" class="w-full border rounded-md px-3 py-2 text-sm" required>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Tip: tahan Ctrl / Cmd untuk pilih banyak user.</p>
                </div>

                <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded-md text-sm font-semibold">Bulk Assign</button>
            </form>
        </div>
    </div>

    <div class="bg-white rounded-md shadow border border-gray-200 overflow-x-auto">
        <table class="min-w-full text-sm">
            <thead class="bg-gray-50">
                <tr class="text-left text-xs uppercase tracking-wide text-gray-500">
                    <th class="px-4 py-3">User</th>
                    <th class="px-4 py-3">Tanggal</th>
                    <th class="px-4 py-3">Jam</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($schedules as $schedule)
                    @php
                        $statusClass = $schedule->status === 'aktif' ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700';
                    @endphp
                    <tr class="border-t border-gray-100 align-top">
                        <td class="px-4 py-3 font-semibold text-gray-700">{{ $schedule->user->name ?? '-' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $schedule->tanggal->format('d/m/Y') }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $schedule->jam_masuk?->format('H:i') ?? '00:00' }} - {{ $schedule->jam_pulang?->format('H:i') ?? '00:00' }}</td>
                        <td class="px-4 py-3"><span class="px-2 py-1 rounded-full text-xs font-semibold {{ $statusClass }}">{{ ucfirst($schedule->status) }}</span></td>
                        <td class="px-4 py-3">
                            <div class="flex flex-col gap-2">
                                <form action="{{ route('admin.shift_management.schedules.update', $schedule) }}" method="POST" class="grid md:grid-cols-6 gap-2">
                                    @csrf
                                    @method('PUT')
                                    <select name="user_id" class="border rounded-md px-2 py-1 text-xs" required>
                                        @foreach($users as $user)
                                            <option value="{{ $user->id }}" @selected($schedule->user_id === $user->id)>{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                    <input type="date" name="tanggal" value="{{ $schedule->tanggal->toDateString() }}" class="border rounded-md px-2 py-1 text-xs" required>
                                    <select name="status" class="border rounded-md px-2 py-1 text-xs" required>
                                        <option value="aktif" @selected($schedule->status === 'aktif')>Aktif</option>
                                        <option value="libur" @selected($schedule->status === 'libur')>Libur</option>
                                    </select>
                                    <input type="time" name="jam_masuk" value="{{ $schedule->jam_masuk?->format('H:i') }}" class="border rounded-md px-2 py-1 text-xs">
                                    <input type="time" name="jam_pulang" value="{{ $schedule->jam_pulang?->format('H:i') }}" class="border rounded-md px-2 py-1 text-xs">
                                    <input type="hidden" name="shift_id" value="">
                                    <button class="bg-blue-600 text-white rounded-md px-2 py-1 text-xs">Update</button>
                                </form>
                                <form action="{{ route('admin.shift_management.schedules.destroy', $schedule) }}" method="POST" onsubmit="return confirm('Hapus jadwal ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="bg-red-600 text-white rounded-md px-2 py-1 text-xs">Hapus</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-400">Belum ada jadwal pada tanggal ini.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
