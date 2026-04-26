@extends('layouts.app')

@section('content')

<div class="w-full min-h-screen bg-gray-100 flex justify-center">

    <div class="w-full md:max-w-md min-h-screen bg-white shadow-xl flex flex-col">

        <!-- HEADER -->
        <div class="bg-gradient-to-b from-green-700 to-green-500 pt-10 pb-24 px-6 rounded-b-[2.5rem] text-white relative">

            <div class="flex justify-between items-center mb-6">
                <i class="fas fa-bell text-lg"></i>
                <img src="https://i.pravatar.cc/100" class="w-12 h-12 rounded-full border-2 border-white">
            </div>

            <div>
                <h2 class="font-bold text-lg">
                    {{ auth()->user()->name }}
                </h2>
                <p class="text-sm opacity-80">
                    {{ auth()->user()->role }}
                </p>
            </div>

            <!-- JAM -->
            <div class="text-center mt-6">
                <h1 id="clock" class="text-4xl font-bold tracking-widest"></h1>
                <p class="text-sm mt-1 opacity-90">
                    {{ now()->translatedFormat('l, d F Y') }}
                </p>
            </div>
        </div>

        <!-- STATUS SP -->
        <div class="px-4 -mt-16">
            <div class="bg-red-100 text-red-700 rounded-2xl p-4 shadow">
                <div class="flex items-center space-x-3">
                    <i class="fas fa-exclamation-triangle text-xl"></i>
                    <div>
                        <p class="font-bold text-sm">Status Dalam Masa SP</p>
                        <p class="text-xs">
                            Berlaku hingga: <b>28 Februari 2026</b>
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- JAM MASUK PULANG -->
        <div class="px-4 mt-4">
            <div class="bg-white rounded-2xl shadow p-4 flex justify-between items-center">

                <div class="text-center">
                    <i class="fas fa-camera text-green-600 mb-1"></i>
                    <p class="text-xs text-gray-500">Jam Masuk</p>
                    <p class="font-bold">--:--</p>
                </div>

                <div class="h-10 w-px bg-gray-200"></div>

                <div class="text-center">
                    <i class="fas fa-camera text-green-600 mb-1"></i>
                    <p class="text-xs text-gray-500">Jam Pulang</p>
                    <p class="font-bold">--:--</p>
                </div>

            </div>
        </div>

        <!-- REKAP -->
        <div class="px-4 mt-4">
            <div class="bg-white rounded-2xl shadow p-4">

                <h3 class="text-center font-bold text-gray-700 text-sm mb-2">
                    Rekap Presensi 30 Hari
                </h3>

                <p class="text-center text-xs text-gray-400 mb-4">
                    Update terakhir: {{ now()->format('H:i') }} WIB
                </p>

                <div class="grid grid-cols-4 text-center">
                    <div>
                        <p class="text-green-600 font-bold text-lg">3</p>
                        <p class="text-xs text-gray-500">Hadir</p>
                    </div>
                    <div>
                        <p class="text-yellow-500 font-bold text-lg">0</p>
                        <p class="text-xs text-gray-500">Sakit</p>
                    </div>
                    <div>
                        <p class="text-blue-500 font-bold text-lg">0</p>
                        <p class="text-xs text-gray-500">Izin</p>
                    </div>
                    <div>
                        <p class="text-red-500 font-bold text-lg">0</p>
                        <p class="text-xs text-gray-500">Cuti</p>
                    </div>
                </div>

            </div>
        </div>

        <!-- MENU -->
        <div class="px-4 mt-6 flex-grow">
            <div class="grid grid-cols-4 gap-4 text-center">

                @php
                $menus = [
                    ['icon'=>'fa-id-card','label'=>'ID Card'],
                    ['icon'=>'fa-coffee','label'=>'Istirahat'],
                    ['icon'=>'fa-clock','label'=>'Lembur'],
                    ['icon'=>'fa-wallet','label'=>'Slip Gaji'],
                    ['icon'=>'fa-list','label'=>'Aktivitas'],
                    ['icon'=>'fa-map','label'=>'Visit'],
                    ['icon'=>'fa-user','label'=>'Wajah'],
                    ['icon'=>'fa-th','label'=>'Lainnya'],
                ];
                @endphp

                @foreach($menus as $menu)
                <div>
                    <div class="bg-gray-100 w-14 h-14 flex items-center justify-center rounded-xl mx-auto">
                        <i class="fas {{ $menu['icon'] }}"></i>
                    </div>
                    <p class="text-xs mt-1">{{ $menu['label'] }}</p>
                </div>
                @endforeach

            </div>
        </div>

        <!-- BOTTOM NAV -->
        <!-- BOTTOM NAV -->
        <div class="fixed bottom-0 left-0 w-full flex justify-center z-50">
            
            <div class="w-full md:max-w-md bg-white border-t shadow-lg px-6 py-3 flex justify-between items-center relative">

                <!-- HOME -->
                <div class="text-center text-gray-500">
                    <i class="fas fa-home text-lg"></i>
                    <p class="text-xs">Home</p>
                </div>

                <!-- HISTORI -->
                <div class="text-center text-gray-500">
                    <i class="fas fa-file-alt text-lg"></i>
                    <p class="text-xs">Histori</p>
                </div>

                <!-- BUTTON TENGAH -->
                <div class="absolute -top-8 left-1/2 transform -translate-x-1/2">
                    <a href="{{ route('absen.page') }}"
                    class="w-16 h-16 bg-green-600 rounded-full flex items-center justify-center shadow-xl border-4 border-white">
                        <i class="fas fa-fingerprint text-white text-2xl"></i>
                    </a>
                </div>

                <!-- PENGAJUAN -->
                <div class="text-center text-gray-500">
                    <i class="fas fa-calendar-alt text-lg"></i>
                    <p class="text-xs">Pengajuan</p>
                </div>

                <!-- SETTING -->
                <div class="text-center text-gray-500">
                    <i class="fas fa-cog text-lg"></i>
                    <p class="text-xs">Setting</p>
                </div>

            </div>
        </div>

    </div>
</div>



<!-- CLOCK -->
<script>
function updateClock(){
    const now = new Date();
    document.getElementById('clock').innerText =
        now.toLocaleTimeString('id-ID');
}
setInterval(updateClock,1000);
updateClock();
</script>

@endsection