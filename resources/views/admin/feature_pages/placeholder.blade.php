@extends('layouts.admin')

@section('title', $title)

@section('content')
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-8 text-center">
    <div class="w-16 h-16 mx-auto rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
        <i class="fas fa-toggle-on text-3xl"></i>
    </div>
    <h1 class="mt-5 text-2xl font-bold text-gray-800">{{ $title }}</h1>
    <p class="mt-2 text-sm text-gray-500">Fitur ini sudah aktif untuk Admin. Halaman detail fitur belum dikembangkan.</p>
</div>
@endsection
