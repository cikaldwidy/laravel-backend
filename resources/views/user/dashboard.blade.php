@extends('layouts.app')

@section('content')

<div class="w-full min-h-screen bg-white flex justify-center">

    <!-- CONTAINER -->
    <div class="w-full md:max-w-md bg-white min-h-screen shadow-xl flex flex-col">

        <!-- HEADER -->
        <div class="bg-blue-500 pt-10 pb-20 px-6 rounded-b-[3rem] text-white">

            <div class="flex items-center space-x-4 mb-8">
                <img src="https://i.pravatar.cc/100" class="w-12 h-12 rounded-full border-2 border-white">

                <div>
                    <h2 class="font-bold text-lg">
                        {{ auth()->user()->name }}
                    </h2>
                    <p class="text-sm opacity-80">
                        {{ auth()->user()->role }}
                    </p>
                </div>
            </div>

            <!-- JAM -->
            <div class="text-center">
                <h1 id="clock" class="text-4xl font-bold tracking-widest"></h1>
                <p class="text-sm mt-1 opacity-90">
                    {{ now()->translatedFormat('l, d F Y') }}
                </p>
            </div>
        </div>

        <!-- CARD JAM -->
        <div class="px-4 -mt-10 z-10">
            <div class="bg-white rounded-full shadow-lg p-3 flex justify-between items-center">

                <div class="flex items-center space-x-2">
                    <div class="bg-green-500 text-white p-2 rounded-lg text-xs">
                        <i class="fas fa-sign-in-alt"></i>
                    </div>
                    <div>
                        <p class="text-xs text-gray-400">Masuk</p>
                        <p class="font-bold text-sm">08:10</p>
                    </div>
                </div>

                <div class="text-center">
                    <p class="text-xs text-gray-400">Kerja</p>
                    <p class="font-bold text-sm text-blue-600">4 Jam</p>
                </div>

                <div class="flex items-center space-x-2">
                    <div>
                        <p class="text-xs text-gray-400">Pulang</p>
                        <p class="font-bold text-sm">16:30</p>
                    </div>
                    <div class="bg-red-500 text-white p-2 rounded-lg text-xs">
                        <i class="fas fa-sign-out-alt"></i>
                    </div>
                </div>

            </div>
        </div>

        <!-- REKAP -->
        <div class="px-6 mt-6">
            <div class="bg-gray-50 rounded-2xl p-4">

                <h3 class="text-center font-bold text-gray-700 mb-4 text-sm">
                    Rekap Bulanan
                </h3>

                <div class="grid grid-cols-3 gap-3 text-center">

                    <div>
                        <p class="text-xl font-bold text-blue-500">75%</p>
                        <p class="text-xs text-gray-500">Hadir</p>
                    </div>

                    <div>
                        <p class="text-xl font-bold text-yellow-500">10%</p>
                        <p class="text-xs text-gray-500">Izin</p>
                    </div>

                    <div>
                        <p class="text-xl font-bold text-red-500">5%</p>
                        <p class="text-xs text-gray-500">Cuti</p>
                    </div>

                </div>
            </div>
        </div>

        <!-- MENU -->
        <div class="px-6 mt-6 flex-grow">
            <div class="grid grid-cols-3 gap-4">

                @php
                $menus = [
                    ['icon'=>'fa-user-check','label'=>'Absen'],
                    ['icon'=>'fa-calendar','label'=>'Jadwal'],
                    ['icon'=>'fa-history','label'=>'Riwayat'],
                    ['icon'=>'fa-file','label'=>'Izin'],
                    ['icon'=>'fa-bell','label'=>'Notif'],
                    ['icon'=>'fa-cog','label'=>'Setting'],
                ];
                @endphp

                @foreach($menus as $menu)
                <div class="text-center">
                    @if($menu['label'] === 'Absen')
                        <a href="{{ route('absen.page') }}" class="block">
                            <div class="bg-blue-100 text-blue-600 w-14 h-14 flex items-center justify-center rounded-xl mx-auto">
                                <i class="fas {{ $menu['icon'] }}"></i>
                            </div>
                            <p class="text-xs mt-1">{{ $menu['label'] }}</p>
                        </a>
                    @else
                        <div class="bg-gray-200 w-14 h-14 flex items-center justify-center rounded-xl mx-auto">
                            <i class="fas {{ $menu['icon'] }}"></i>
                        </div>
                        <p class="text-xs mt-1">{{ $menu['label'] }}</p>
                    @endif
                </div>
                @endforeach

            </div>
        </div>

        <!-- BOTTOM NAV -->
        <div class="bg-white border-t py-3 px-6 flex justify-between items-center relative">

            <div class="text-center text-blue-600">
                <i class="fas fa-home"></i>
                <p class="text-xs">Home</p>
            </div>

            <div class="text-center text-gray-400">
                <i class="fas fa-calendar"></i>
                <p class="text-xs">Jadwal</p>
            </div>

            <!-- BUTTON ABSEN -->
            <div class="absolute -top-8 left-1/2 -translate-x-1/2">
                <a href="{{ route('absen.page') }}" class="bg-blue-600 w-16 h-16 rounded-full flex items-center justify-center text-white shadow-lg">
                    <i class="fas fa-camera text-xl"></i>
                </a>
            </div>

            <div class="text-center text-gray-400">
                <i class="fas fa-clock"></i>
                <p class="text-xs">Riwayat</p>
            </div>

            <div class="text-center text-gray-400">
                <i class="fas fa-user"></i>
                <p class="text-xs">Profil</p>
            </div>

        </div>

    </div>
</div>

<!-- CLOCK SCRIPT -->
<script>
function updateClock(){
    const now = new Date();
    const time = now.toLocaleTimeString('id-ID');
    document.getElementById('clock').innerText = time;
}
setInterval(updateClock,1000);
updateClock();
</script>

@endsection
