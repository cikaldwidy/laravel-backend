@extends('layouts.admin')

@section('title', 'Jadwal Shift')

@section('content')
<div class="space-y-4">
    @if(session('success'))
        <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
        <div>
            <h2 class="text-lg font-bold text-gray-800">Jadwal Shift</h2>
            <p class="text-sm text-gray-500">Atur shift per user per tanggal.</p>
        </div>

        <form method="GET" action="{{ route('admin.user_shifts.index') }}" class="flex items-center gap-2">
            <input type="date" name="date" value="{{ $tanggal }}"
                   class="border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-semibold">
                Tampilkan
            </button>
        </form>
    </div>

    <form method="POST" action="{{ route('admin.user_shifts.store') }}">
        @csrf
        <input type="hidden" name="tanggal" value="{{ $tanggal }}">

        <div class="bg-white rounded-lg shadow overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="p-3 text-left">User</th>
                        <th class="p-3 text-left">Shift</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($users as $user)
                        @php
                            $selected = $assignments[$user->id] ?? null;
                        @endphp
                        <tr class="hover:bg-gray-50">
                            <td class="p-3 font-medium text-gray-800">{{ $user->name }}</td>
                            <td class="p-3">
                                <select name="shift[{{ $user->id }}]"
                                        class="w-full max-w-xs border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">-- Tidak ada shift --</option>
                                    @foreach($shifts as $shift)
                                        <option value="{{ $shift->id }}" @selected((string) $selected === (string) $shift->id)>
                                            {{ $shift->nama_shift }} ({{ \Illuminate\Support\Str::of($shift->jam_masuk)->substr(0,5) }} - {{ \Illuminate\Support\Str::of($shift->jam_pulang)->substr(0,5) }})
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="2" class="p-8 text-center text-gray-500">Tidak ada user.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4 flex items-center gap-2">
            <button class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded-md text-sm font-semibold">
                Simpan Jadwal
            </button>
            <span class="text-xs text-gray-500">Tanggal: <span class="font-semibold text-gray-700">{{ $tanggal }}</span></span>
        </div>
    </form>
</div>
@endsection

