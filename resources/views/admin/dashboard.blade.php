@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

    <div class="bg-blue-500 text-white p-6 rounded-xl shadow">
        <h2 class="text-sm">Total User</h2>
        <p class="text-2xl font-bold mt-2">{{ \App\Models\User::count() }}</p>
    </div>

    <div class="bg-green-500 text-white p-6 rounded-xl shadow">
        <h2 class="text-sm">Presensi Hari Ini</h2>
        <p class="text-2xl font-bold mt-2">
            {{ \App\Models\Presensi::whereDate('tanggal', today())->count() }}
        </p>
    </div>

    <div class="bg-yellow-500 text-white p-6 rounded-xl shadow">
        <h2 class="text-sm">Izin</h2>
        <p class="text-2xl font-bold mt-2">10</p>
    </div>

    <div class="bg-purple-500 text-white p-6 rounded-xl shadow">
        <h2 class="text-sm">Cuti</h2>
        <p class="text-2xl font-bold mt-2">5</p>
    </div>

</div>

@endsection