@extends('layouts.admin')

@section('title', $title)

@section('content')
<div class="space-y-5">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-gray-700">{{ $title }}</h1>
            <p class="mt-0.5 text-sm text-gray-500">Kelola data {{ strtolower($title) }} pegawai.</p>
        </div>
    </div>

    <div class="overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3.5">
            <span class="text-sm font-semibold text-gray-700">Daftar {{ $title }}</span>
            <span class="rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700">0 data</span>
        </div>

        <div class="py-16 text-center">
            <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-md bg-blue-50 text-blue-500">
                <i class="fas fa-clock text-xl"></i>
            </div>
            <p class="mt-3 text-sm font-semibold text-gray-700">Belum ada data {{ strtolower($title) }}</p>
            <p class="mt-1 text-xs text-gray-500">Halaman detail fitur ini belum memiliki data untuk ditampilkan.</p>
        </div>
    </div>
</div>
@endsection
