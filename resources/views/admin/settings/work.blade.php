@extends('layouts.admin')

@section('title', 'Pengaturan Jam Kerja')

@section('content')
<div class="max-w-3xl">
    @if(session('success'))
        <div class="mb-4 bg-green-100 text-green-700 border border-green-200 px-4 py-3 rounded-lg">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-4 bg-red-100 text-red-700 border border-red-200 px-4 py-3 rounded-lg">
            <p class="font-semibold mb-1">Periksa kembali input berikut:</p>
            <ul class="list-disc list-inside text-sm">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white rounded-lg shadow">
        <div class="p-5 border-b">
            <h2 class="font-bold text-lg text-gray-800">Jam Masuk dan Jam Pulang</h2>
            <p class="text-sm text-gray-500 mt-1">
                Pengaturan ini dipakai untuk menentukan status telat dan pulang cepat saat user melakukan presensi.
            </p>
        </div>

        <form method="POST" action="{{ route('admin.settings.work.update') }}" class="p-5 space-y-5">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div>
                    <label for="jam_masuk" class="block text-sm font-semibold text-gray-700 mb-2">
                        Jam Masuk
                    </label>
                    <input
                        id="jam_masuk"
                        type="time"
                        name="jam_masuk"
                        value="{{ old('jam_masuk', \Illuminate\Support\Str::of($setting->jam_masuk)->substr(0, 5)) }}"
                        class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    >
                </div>

                <div>
                    <label for="jam_pulang" class="block text-sm font-semibold text-gray-700 mb-2">
                        Jam Pulang
                    </label>
                    <input
                        id="jam_pulang"
                        type="time"
                        name="jam_pulang"
                        value="{{ old('jam_pulang', \Illuminate\Support\Str::of($setting->jam_pulang)->substr(0, 5)) }}"
                        class="w-full border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    >
                </div>
            </div>

            <div>
                <label for="batas_telat" class="block text-sm font-semibold text-gray-700 mb-2">
                    Batas Telat
                </label>
                <div class="flex items-center gap-3">
                    <input
                        id="batas_telat"
                        type="number"
                        name="batas_telat"
                        min="0"
                        max="240"
                        value="{{ old('batas_telat', $setting->batas_telat) }}"
                        class="w-32 border rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                        required
                    >
                    <span class="text-sm text-gray-600">menit setelah jam masuk</span>
                </div>
            </div>

            <div class="bg-gray-50 border rounded-lg p-4 text-sm text-gray-600">
                <p>
                    Jam masuk saat ini:
                    <span class="font-semibold text-gray-800">{{ \Illuminate\Support\Str::of($setting->jam_masuk)->substr(0, 5) }}</span>
                </p>
                <p class="mt-1">
                    Jam pulang saat ini:
                    <span class="font-semibold text-gray-800">{{ \Illuminate\Support\Str::of($setting->jam_pulang)->substr(0, 5) }}</span>
                </p>
                <p class="mt-1">
                    User dianggap telat jika presensi masuk lebih dari
                    <span class="font-semibold text-gray-800">{{ $setting->batas_telat }} menit</span>
                    dari jam masuk.
                </p>
            </div>

            <div class="flex justify-end">
                <button class="bg-blue-600 hover:bg-blue-700 text-white px-5 py-2 rounded-md font-semibold">
                    Simpan Pengaturan
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
