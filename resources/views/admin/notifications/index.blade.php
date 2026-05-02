@extends('layouts.admin')

@section('title', 'Notifikasi Admin')

@section('content')
<div class="space-y-5">
    <div class="bg-white border border-white/70 p-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-extrabold text-slate-950">Notifikasi Admin</h1>
                <p class="text-sm text-slate-500 mt-1">Pantau izin pending, tukar shift, dan status presensi hari ini.</p>
            </div>
            <span class="inline-flex items-center justify-center min-w-10 h-10 rounded-2xl bg-slate-950 px-3 text-sm font-bold text-white">
                {{ $notificationCount }}
            </span>
        </div>
    </div>

    <div class="bg-white border border-white/70 overflow-hidden">
        @forelse($notifications as $notification)
            <a href="{{ $notification['url'] }}" class="flex items-start gap-4 p-5 border-b border-slate-100 hover:bg-slate-50 transition">
                <span class="w-11 h-11 rounded-2xl inline-flex items-center justify-center shrink-0 {{ $notification['tone'] }}">
                    <i class="{{ $notification['icon'] }}"></i>
                </span>
                <span class="min-w-0 flex-1">
                    <span class="block text-sm font-bold text-slate-900">{{ $notification['title'] }}</span>
                    <span class="block text-sm text-slate-500 mt-1">{{ $notification['message'] }}</span>
                </span>
                <span class="hidden md:block text-xs text-slate-400 whitespace-nowrap">
                    {{ $notification['time']?->diffForHumans() }}
                </span>
            </a>
        @empty
            <div class="p-10 text-center">
                <div class="mx-auto w-14 h-14 rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
                    <i class="fa-solid fa-check"></i>
                </div>
                <p class="mt-4 text-sm font-bold text-slate-800">Belum ada notifikasi.</p>
                <p class="text-sm text-slate-500">Semua aktivitas penting sedang aman.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection
