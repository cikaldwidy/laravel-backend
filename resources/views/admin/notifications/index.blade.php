@extends('layouts.admin')

@section('title', 'Notifikasi Admin')

@section('content')
<div class="space-y-5">
    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
        <div>
            <h1 class="text-xl font-bold tracking-tight text-gray-700">Notifikasi Admin</h1>
            <p class="mt-0.5 text-sm text-gray-500">Pantau izin pending, tukar shift, dan status presensi hari ini.</p>
        </div>
        <span class="inline-flex w-fit items-center gap-2 rounded-md bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700">
            <i class="fa-solid fa-bell text-xs"></i>
            {{ $notificationCount }} notifikasi
        </span>
    </div>

    <div class="overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-3.5">
            <span class="text-sm font-semibold text-gray-700">Daftar Notifikasi</span>
            <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-500">{{ count($notifications) }} data</span>
        </div>

        <div class="divide-y divide-gray-50">
            @forelse($notifications as $notification)
                <a href="{{ $notification['url'] }}" class="group flex items-start gap-3 px-5 py-4 transition hover:bg-gray-50/70">
                    <span class="inline-flex h-10 w-10 shrink-0 items-center justify-center rounded-xl {{ $notification['tone'] }}">
                        <i class="{{ $notification['icon'] }} text-sm"></i>
                    </span>
                    <span class="min-w-0 flex-1">
                        <span class="block text-sm font-semibold text-gray-800">{{ $notification['title'] }}</span>
                        <span class="mt-1 block text-sm text-gray-500">{{ $notification['message'] }}</span>
                        <span class="mt-2 block text-xs font-medium text-gray-400 md:hidden">
                            {{ $notification['time']?->diffForHumans() }}
                        </span>
                    </span>
                    <span class="hidden whitespace-nowrap text-xs font-medium text-gray-400 md:block">
                        {{ $notification['time']?->diffForHumans() }}
                    </span>
                    <span class="mt-0.5 hidden h-8 w-8 shrink-0 items-center justify-center rounded-md border border-gray-200 bg-white text-gray-400 transition group-hover:border-blue-200 group-hover:bg-blue-50 group-hover:text-blue-600 md:inline-flex">
                        <i class="fa-solid fa-chevron-right text-[10px]"></i>
                    </span>
                </a>
            @empty
                <div class="py-14 text-center">
                    <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-blue-50 text-blue-600">
                        <i class="fa-solid fa-bell text-sm"></i>
                    </div>
                    <p class="mt-3 text-sm font-semibold text-gray-700">Belum ada notifikasi</p>
                    <p class="text-xs text-gray-500">Semua aktivitas penting sedang aman.</p>
                </div>
            @endforelse
        </div>
    </div>
</div>
@endsection
