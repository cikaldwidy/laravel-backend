@extends('layouts.app')

@section('title', $title)

@section('content')
<div class="user-page">
    <div class="user-phone">
        @include('user.partials.header', [
            'title' => $title,
            'subtitle' => 'Fitur ini sudah aktif untuk role ' . $roleLabel . '.',
            'back' => $backUrl,
        ])

        <main class="px-4 pt-4">
            <section class="user-card p-6 text-center">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
                    <i class="fa-solid fa-toggle-on text-2xl"></i>
                </div>
                <h1 class="mt-4 text-lg font-extrabold text-slate-800">{{ $title }}</h1>
                <p class="mt-2 text-sm text-slate-500">Halaman detail fitur ini belum dikembangkan. Toggle akses sudah aktif dan terlindungi.</p>
            </section>
        </main>

        @include('user.partials.bottom-nav', ['active' => ''])
    </div>
</div>
@endsection
