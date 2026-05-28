@extends('layouts.app')

@section('title', 'Izin')

@section('content')
@php
    $typeMeta = [
        'izin' => ['label' => 'Izin Absen', 'icon' => 'fa-file-lines', 'color' => 'text-emerald-700', 'bg' => 'bg-emerald-50', 'fab' => 'bg-emerald-600'],
        'sakit' => ['label' => 'Izin Sakit', 'icon' => 'fa-kit-medical', 'color' => 'text-rose-700', 'bg' => 'bg-rose-50', 'fab' => 'bg-rose-500'],
        'cuti' => ['label' => 'Izin Cuti', 'icon' => 'fa-umbrella-beach', 'color' => 'text-orange-700', 'bg' => 'bg-orange-50', 'fab' => 'bg-orange-500'],
        'dinas' => ['label' => 'Izin Dinas', 'icon' => 'fa-route', 'color' => 'text-cyan-700', 'bg' => 'bg-cyan-50', 'fab' => 'bg-cyan-500'],
    ];

    $fabTypes = collect([
        ['key' => 'izin', 'label' => 'Izin Absen'],
        ['key' => 'sakit', 'label' => 'Izin Sakit', 'enabled' => \App\Models\FeatureSetting::enabled('sakit', 'user')],
        ['key' => 'cuti', 'label' => 'Izin Cuti', 'enabled' => \App\Models\FeatureSetting::enabled('cuti', 'user')],
        ['key' => 'dinas', 'label' => 'Izin Dinas'],
    ])->filter(fn ($item) => $item['enabled'] ?? true)->values();

    $statusMeta = [
        'pending' => ['label' => 'Diajukan', 'class' => 'bg-amber-50 text-amber-700 border-amber-100'],
        'approved' => ['label' => 'Disetujui', 'class' => 'bg-emerald-50 text-emerald-700 border-emerald-100'],
        'rejected' => ['label' => 'Ditolak', 'class' => 'bg-red-50 text-red-700 border-red-100'],
    ];

    $dayNames = ['Sun' => 'Min', 'Mon' => 'Sen', 'Tue' => 'Sel', 'Wed' => 'Rab', 'Thu' => 'Kam', 'Fri' => 'Jum', 'Sat' => 'Sab'];
@endphp

<div class="user-page">
    <div class="user-phone bg-gradient-to-b from-emerald-50 via-white to-cyan-50">
        <header class="bg-emerald-700 px-4 text-white shadow-md shadow-emerald-900/10" style="padding-top: calc(0.85rem + env(safe-area-inset-top)); padding-bottom: 0.85rem;">
            <div class="relative flex items-center justify-center">
                <a href="{{ route('dashboard') }}" class="absolute left-0 flex h-9 w-9 items-center justify-center rounded-lg bg-white/10 text-white">
                    <i class="fa-solid fa-chevron-left text-sm"></i>
                </a>
                <h1 class="text-sm font-extrabold tracking-wide">Ajuan Izin</h1>
            </div>
        </header>

        <main class="px-4 pt-4 space-y-4">
            @if(session('success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-3 text-sm font-semibold text-emerald-800 shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            @if(request()->hasAny(['search', 'jenis_izin', 'status', 'date_from', 'date_to']))
                <div class="flex items-center justify-between gap-3 rounded-xl border border-white/80 bg-white/80 px-3 py-2 text-xs text-slate-600 shadow-sm">
                    <span>Filter aktif</span>
                    <a href="{{ route('leave_requests.index') }}" class="font-bold text-emerald-700">Reset</a>
                </div>
            @endif

            <section class="space-y-3">
                @forelse($requests as $requestItem)
                    @php
                        $meta = $typeMeta[$requestItem->jenis_izin] ?? ['label' => 'Izin ' . ucfirst(str_replace('_', ' ', $requestItem->jenis_izin)), 'icon' => 'fa-calendar-check', 'color' => 'text-slate-700', 'bg' => 'bg-slate-50'];
                        $status = $statusMeta[$requestItem->status] ?? ['label' => ucfirst($requestItem->status), 'class' => 'bg-slate-50 text-slate-700 border-slate-100'];
                        $days = $requestItem->tanggal_mulai->diffInDays($requestItem->tanggal_selesai) + 1;
                        $weekday = $dayNames[$requestItem->tanggal_mulai->format('D')] ?? $requestItem->tanggal_mulai->format('D');
                    @endphp
                    <article class="rounded-xl border border-emerald-800/35 bg-white p-3 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="flex h-14 w-14 shrink-0 flex-col items-center justify-center rounded-xl bg-emerald-50 text-emerald-800">
                                <span class="text-[10px] font-extrabold uppercase leading-none">{{ $weekday }}</span>
                                <span class="mt-1 text-lg font-black leading-none">{{ $requestItem->tanggal_mulai->format('d') }}</span>
                            </div>

                            <div class="min-w-0 flex-1">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="min-w-0">
                                        <p class="truncate text-sm font-extrabold text-slate-800">{{ $meta['label'] }}</p>
                                        <p class="mt-1 truncate text-[11px] text-slate-500">
                                            <i class="fa-regular fa-calendar mr-1"></i>
                                            {{ $requestItem->tanggal_mulai->format('d M Y') }} - {{ $requestItem->tanggal_selesai->format('d M Y') }}
                                            <span class="font-semibold text-slate-600">{{ $days }} Hari</span>
                                        </p>
                                    </div>
                                    <span class="shrink-0 rounded-full border px-2 py-0.5 text-[10px] font-extrabold {{ $status['class'] }}">
                                        {{ $status['label'] }}
                                    </span>
                                </div>

                                <div class="mt-2 flex items-center gap-2 text-[11px] text-slate-500">
                                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg {{ $meta['bg'] }} {{ $meta['color'] }}">
                                        <i class="fa-solid {{ $meta['icon'] }}"></i>
                                    </span>
                                    <span class="truncate">{{ $requestItem->keterangan ?: 'Tanpa keterangan' }}</span>
                                </div>
                            </div>
                        </div>

                        @if($requestItem->catatan_admin)
                            <div class="mt-3 rounded-xl bg-slate-50 p-3 text-xs text-slate-600">
                                {{ $requestItem->catatan_admin }}
                            </div>
                        @endif

                        @if($requestItem->status === 'pending')
                            <form method="POST" action="{{ route('leave_requests.destroy', $requestItem) }}" data-confirm-form data-confirm-title="Hapus pengajuan?" data-confirm-message="Pengajuan izin ini akan dihapus." data-confirm-button="Hapus" class="mt-3">
                                @csrf
                                <button class="w-full rounded-xl bg-red-50 px-4 py-2 text-sm font-bold text-red-700">
                                    <i class="fa-solid fa-trash-can mr-1"></i>
                                    Hapus Pengajuan
                                </button>
                            </form>
                        @endif
                    </article>
                @empty
                    <section class="rounded-xl border border-white/80 bg-white/85 p-6 text-center shadow-sm">
                        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-xl bg-emerald-50 text-emerald-700">
                            <i class="fa-solid fa-calendar-days"></i>
                        </div>
                        <p class="mt-3 text-sm font-semibold text-slate-700">Belum ada pengajuan izin.</p>
                        <p class="mt-1 text-xs text-slate-500">Pilih jenis izin dari tombol kanan bawah.</p>
                    </section>
                @endforelse
            </section>
        </main>

        <div class="fixed bottom-24 right-4 z-40 flex flex-col items-end gap-3">
            <div id="leaveFabMenu" class="pointer-events-none flex translate-y-3 flex-col items-end gap-2 opacity-0 transition duration-200">
                @foreach($fabTypes as $type)
                    @php($meta = $typeMeta[$type['key']])
                    <a href="{{ route('leave_requests.create', ['jenis_izin' => $type['key']]) }}" class="flex items-center gap-2">
                        <span class="rounded-lg bg-white px-3 py-2 text-xs font-bold text-slate-700 shadow-lg shadow-slate-900/10">{{ $type['label'] }}</span>
                        <span class="flex h-11 w-11 items-center justify-center rounded-full text-white shadow-lg {{ $meta['fab'] }}">
                            <i class="fa-solid {{ $meta['icon'] }}"></i>
                        </span>
                    </a>
                @endforeach
            </div>
            <button type="button" id="leaveFabButton" class="flex h-14 w-14 items-center justify-center rounded-full bg-emerald-700 text-white shadow-xl shadow-emerald-900/25 transition" aria-expanded="false" aria-controls="leaveFabMenu">
                <i class="fa-solid fa-plus text-lg transition" id="leaveFabIcon"></i>
            </button>
        </div>

        @include('user.partials.bottom-nav', ['active' => ''])
    </div>
</div>

<script>
    (() => {
        const button = document.getElementById('leaveFabButton');
        const menu = document.getElementById('leaveFabMenu');
        const icon = document.getElementById('leaveFabIcon');

        if (!button || !menu || !icon) return;

        button.addEventListener('click', () => {
            const isOpen = button.getAttribute('aria-expanded') === 'true';

            button.setAttribute('aria-expanded', String(!isOpen));
            menu.classList.toggle('pointer-events-none', isOpen);
            menu.classList.toggle('opacity-0', isOpen);
            menu.classList.toggle('translate-y-3', isOpen);
            icon.classList.toggle('fa-plus', isOpen);
            icon.classList.toggle('fa-xmark', !isOpen);
            button.classList.toggle('bg-emerald-700', isOpen);
            button.classList.toggle('bg-slate-800', !isOpen);
        });
    })();
</script>
@endsection
