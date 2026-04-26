@extends('layouts.admin')

@section('title', 'Dashboard Presensi')

@section('content')
@php
    $badge = [
        'hadir' => 'bg-green-100 text-green-700',
        'telat' => 'bg-yellow-100 text-yellow-700',
        'normal' => 'bg-blue-100 text-blue-700',
        'pulang_cepat' => 'bg-red-100 text-red-700',
    ];
@endphp

<div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
        <div class="bg-white p-5 rounded-lg shadow border-l-4 border-blue-500">
            <p class="text-sm text-gray-500">Total User</p>
            <p class="text-3xl font-bold text-gray-800 mt-2">{{ $totalUser }}</p>
        </div>

        <div class="bg-white p-5 rounded-lg shadow border-l-4 border-emerald-500">
            <p class="text-sm text-gray-500">Presensi</p>
            <p class="text-3xl font-bold text-gray-800 mt-2">{{ $totalPresensi }}</p>
        </div>

        <div class="bg-white p-5 rounded-lg shadow border-l-4 border-green-500">
            <p class="text-sm text-gray-500">Hadir</p>
            <p class="text-3xl font-bold text-gray-800 mt-2">{{ $hadir }}</p>
        </div>

        <div class="bg-white p-5 rounded-lg shadow border-l-4 border-yellow-500">
            <p class="text-sm text-gray-500">Telat</p>
            <p class="text-3xl font-bold text-gray-800 mt-2">{{ $telat }}</p>
        </div>

        <div class="bg-white p-5 rounded-lg shadow border-l-4 border-red-500">
            <p class="text-sm text-gray-500">Pulang Cepat</p>
            <p class="text-3xl font-bold text-gray-800 mt-2">{{ $pulangCepat }}</p>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <h2 class="font-bold text-lg text-gray-800">Setting Jam Kerja</h2>
            <div class="mt-2 grid grid-cols-1 sm:grid-cols-3 gap-3 text-sm">
                <div>
                    <p class="text-gray-500">Jam Masuk</p>
                    <p class="font-semibold text-gray-800">
                        {{ $workSetting ? \Illuminate\Support\Str::of($workSetting->jam_masuk)->substr(0, 5) : 'Belum diatur' }}
                    </p>
                </div>
                <div>
                    <p class="text-gray-500">Jam Pulang</p>
                    <p class="font-semibold text-gray-800">
                        {{ $workSetting ? \Illuminate\Support\Str::of($workSetting->jam_pulang)->substr(0, 5) : 'Belum diatur' }}
                    </p>
                </div>
                <div>
                    <p class="text-gray-500">Batas Telat</p>
                    <p class="font-semibold text-gray-800">
                        {{ $workSetting ? $workSetting->batas_telat . ' menit' : 'Belum diatur' }}
                    </p>
                </div>
            </div>
            <div class="mt-3 text-sm text-gray-600">
                GPS:
                <span class="font-semibold text-gray-800">
                    {{ $workSetting?->office_latitude ?? config('attendance.office_latitude') }},
                    {{ $workSetting?->office_longitude ?? config('attendance.office_longitude') }}
                </span>
                <span class="text-gray-400">|</span>
                Radius:
                <span class="font-semibold text-gray-800">
                    {{ $workSetting?->radius_meters ?? config('attendance.radius_meters', 100) }} meter
                </span>
            </div>
        </div>

        <a href="{{ route('admin.settings.work.edit') }}"
           class="inline-flex justify-center bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-md font-semibold">
            Atur Jam Kerja
        </a>
    </div>

    <div class="bg-white rounded-lg shadow">
        <div class="p-5 border-b flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h2 class="font-bold text-lg text-gray-800">Data Presensi</h2>
                <p class="text-sm text-gray-500">{{ \Carbon\Carbon::parse($tanggal)->translatedFormat('l, d F Y') }}</p>
            </div>

            <form method="GET" action="{{ route('admin.dashboard') }}" class="flex items-center gap-2">
                <input
                    type="date"
                    name="tanggal"
                    value="{{ $tanggal }}"
                    class="border rounded-md px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500"
                >
                <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md text-sm font-semibold">
                    Filter
                </button>
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="p-3 text-left">Nama</th>
                        <th class="p-3 text-left">Tanggal</th>
                        <th class="p-3 text-left">Jam Masuk</th>
                        <th class="p-3 text-left">Jam Pulang</th>
                        <th class="p-3 text-left">Status Masuk</th>
                        <th class="p-3 text-left">Status Pulang</th>
                        <th class="p-3 text-left">Jarak</th>
                        <th class="p-3 text-left">Foto</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($presensis as $presensi)
                        <tr class="hover:bg-gray-50">
                            <td class="p-3 font-medium text-gray-800">
                                {{ $presensi->user->name ?? 'User dihapus' }}
                            </td>
                            <td class="p-3 text-gray-600">
                                {{ optional($presensi->tanggal)->format('d/m/Y') }}
                            </td>
                            <td class="p-3 text-gray-600">
                                {{ $presensi->jam_masuk ? $presensi->jam_masuk->format('H:i') : '-' }}
                            </td>
                            <td class="p-3 text-gray-600">
                                {{ $presensi->jam_keluar ? $presensi->jam_keluar->format('H:i') : '-' }}
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-1 rounded text-xs font-semibold {{ $badge[$presensi->status] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ $presensi->status ? str_replace('_', ' ', ucfirst($presensi->status)) : '-' }}
                                </span>
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-1 rounded text-xs font-semibold {{ $badge[$presensi->status_pulang] ?? 'bg-gray-100 text-gray-600' }}">
                                    {{ $presensi->status_pulang ? str_replace('_', ' ', ucfirst($presensi->status_pulang)) : '-' }}
                                </span>
                            </td>
                            <td class="p-3 text-gray-600">
                                Masuk: {{ $presensi->jarak_masuk !== null ? round($presensi->jarak_masuk) . ' m' : '-' }}<br>
                                Pulang: {{ $presensi->jarak_keluar !== null ? round($presensi->jarak_keluar) . ' m' : '-' }}
                            </td>
                            <td class="p-3">
                                <div class="flex gap-2">
                                    @if($presensi->foto_masuk)
                                        <a href="{{ asset('storage/' . $presensi->foto_masuk) }}" target="_blank" class="text-blue-600 hover:underline">Masuk</a>
                                    @endif
                                    @if($presensi->foto_keluar)
                                        <a href="{{ asset('storage/' . $presensi->foto_keluar) }}" target="_blank" class="text-blue-600 hover:underline">Pulang</a>
                                    @endif
                                    @if(!$presensi->foto_masuk && !$presensi->foto_keluar)
                                        <span class="text-gray-400">-</span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="p-8 text-center text-gray-500">
                                Belum ada data presensi pada tanggal ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
