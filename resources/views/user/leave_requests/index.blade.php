@extends('layouts.app')

@section('title', 'Izin')

@section('content')
<div class="user-page">
    <div class="user-phone">
        @include('user.partials.header', [
            'title' => 'Pengajuan Izin',
            'subtitle' => 'Riwayat izin pribadi.',
            'action' => new \Illuminate\Support\HtmlString('<a href="' . route('leave_requests.create') . '" class="user-header-icon text-emerald-700 bg-white/80 border border-white/60 shadow-sm"><i class="fa-solid fa-plus"></i></a>'),
        ])

        <main class="px-4 pt-4 space-y-4">
            @if(session('success'))
                <div class="bg-emerald-50 border border-emerald-200 text-emerald-800 rounded-2xl p-4 text-sm shadow-sm">
                    {{ session('success') }}
                </div>
            @endif

            <form method="GET" data-auto-filter class="user-card p-4 space-y-3">
                <div>
                    <label class="text-[11px] font-semibold text-slate-500">Cari</label>
                    <input type="search" name="search" value="{{ request('search') }}" placeholder="Cari jenis, keterangan, catatan..." class="user-field mt-1">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-[11px] font-semibold text-slate-500">Jenis</label>
                        <select name="jenis_izin" class="user-field mt-1">
                            <option value="">Semua Jenis</option>
                            <option value="sakit" @selected(request('jenis_izin') === 'sakit')>Sakit</option>
                            <option value="cuti" @selected(request('jenis_izin') === 'cuti')>Cuti</option>
                            <option value="izin" @selected(request('jenis_izin') === 'izin')>Izin</option>
                            <option value="dinas" @selected(request('jenis_izin') === 'dinas')>Dinas Luar</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-[11px] font-semibold text-slate-500">Status</label>
                        <select name="status" class="user-field mt-1">
                            <option value="">Semua Status</option>
                            <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                            <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                            <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-[11px] font-semibold text-slate-500">Dari</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="user-field mt-1">
                    </div>
                    <div>
                        <label class="text-[11px] font-semibold text-slate-500">Sampai</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="user-field mt-1">
                    </div>
                </div>
                @if(request()->hasAny(['search', 'jenis_izin', 'status', 'date_from', 'date_to']))
                    <a href="{{ route('leave_requests.index') }}" class="user-btn-secondary w-full">Reset</a>
                @endif
            </form>

            <section class="space-y-3">
                @forelse($requests as $requestItem)
                    @php
                        $statusClass = [
                            'pending' => 'bg-amber-100 text-amber-700',
                            'approved' => 'bg-emerald-100 text-emerald-700',
                            'rejected' => 'bg-red-100 text-red-700',
                        ][$requestItem->status] ?? 'bg-slate-100 text-slate-700';
                    @endphp
                    <article class="user-card p-4">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
                                    <i class="fa-solid fa-calendar-check"></i>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-slate-800">{{ ucfirst($requestItem->jenis_izin) }}</p>
                                    <p class="text-xs text-slate-500">{{ $requestItem->tanggal_mulai->format('d/m/Y') }} - {{ $requestItem->tanggal_selesai->format('d/m/Y') }}</p>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-[11px] font-bold {{ $statusClass }}">
                                {{ strtoupper($requestItem->status) }}
                            </span>
                        </div>

                        @if($requestItem->catatan_admin)
                            <div class="mt-3 user-soft-card text-xs text-slate-600">
                                {{ $requestItem->catatan_admin }}
                            </div>
                        @endif

                        @if($requestItem->status === 'pending')
                            <form method="POST" action="{{ route('leave_requests.destroy', $requestItem) }}" onsubmit="return confirm('Hapus pengajuan ini?')" class="mt-3">
                                @csrf
                                <button class="w-full rounded-xl bg-red-50 text-red-700 px-4 py-2 text-sm font-bold">
                                    <i class="fa-solid fa-trash-can mr-1"></i>
                                    Hapus Pengajuan
                                </button>
                            </form>
                        @endif
                    </article>
                @empty
                    <section class="user-card p-6 text-center">
                        <div class="w-12 h-12 mx-auto rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center">
                            <i class="fa-solid fa-calendar-days"></i>
                        </div>
                        <p class="mt-3 text-sm font-semibold text-slate-700">Belum ada pengajuan izin.</p>
                        <a href="{{ route('leave_requests.create') }}" class="user-btn-primary mt-4">Buat Izin</a>
                    </section>
                @endforelse
            </section>
        </main>

        @include('user.partials.bottom-nav', ['active' => 'leave'])
    </div>
</div>
@endsection
