@extends('layouts.app')

@section('title', 'Buat Izin')

@section('content')
@php
    $leaveTypeOptions = collect([
        ['key' => 'sakit', 'label' => 'Izin Sakit', 'enabled' => \App\Models\FeatureSetting::enabled('sakit', 'user')],
        ['key' => 'cuti', 'label' => 'Izin Cuti', 'enabled' => \App\Models\FeatureSetting::enabled('cuti', 'user')],
    ])->filter(fn ($item) => $item['enabled'] ?? true)->values();

    $selectedLabel = $leaveTypeOptions->firstWhere('key', $selectedJenisIzin)['label'] ?? 'Izin';
    $pageTitle = 'Buat ' . $selectedLabel;
    $minimumCutiDate = now()->addMonthNoOverflow()->toDateString();
    $cutiSubmitBlocked = $selectedJenisIzin === 'cuti'
        && (($cutiQuota['quota_days'] ?? 0) <= 0 || ($cutiQuota['remaining_days'] ?? 0) <= 0);
@endphp

<div class="user-page">
    <div class="user-phone bg-gradient-to-b from-emerald-50 via-white to-cyan-50">
        <header class="bg-emerald-700 px-4 text-white shadow-md shadow-emerald-900/10" style="padding-top: calc(0.85rem + env(safe-area-inset-top)); padding-bottom: 0.85rem;">
            <div class="relative flex items-center justify-center">
                <a href="{{ $backUrl ?? route('leave_requests.index') }}" class="absolute left-0 flex h-9 w-9 items-center justify-center rounded-lg bg-white/10 text-white">
                    <i class="fa-solid fa-chevron-left text-sm"></i>
                </a>
                <h1 class="text-sm font-extrabold tracking-wide">{{ $pageTitle }}</h1>
            </div>
        </header>

        <main class="px-4 pt-5">
            <form method="POST" action="{{ route('leave_requests.store') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <input type="hidden" name="jenis_izin" value="{{ $selectedJenisIzin }}">

                @if($selectedJenisIzin === 'cuti')
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-900 shadow-sm">
                        <div class="flex items-start gap-3">
                            <i class="fa-solid fa-circle-info mt-0.5 text-emerald-700"></i>
                            <div class="min-w-0 flex-1">
                                <p class="font-extrabold">
                                    Sisa cuti {{ $cutiQuota['year'] ?? now()->year }}:
                                    {{ $cutiQuota['remaining_days'] ?? 0 }} / {{ $cutiQuota['quota_days'] ?? 0 }} hari
                                </p>
                                <p class="mt-1 text-xs font-semibold text-emerald-800">
                                    Status: {{ $cutiQuota['status_label'] ?? 'Belum Diatur' }}. Pengajuan cuti minimal 1 bulan sebelum tanggal mulai dan sebelum jadwal kerja dibuat.
                                </p>
                            </div>
                        </div>
                    </div>
                    @error('jenis_izin')
                        <p class="rounded-xl border border-red-100 bg-red-50 px-3 py-2 text-xs font-bold text-red-700">{{ $message }}</p>
                    @enderror
                @endif

                <label class="block rounded-2xl border-2 border-emerald-900/45 bg-white/70 px-4 py-3 shadow-sm">
                    <div class="flex items-center gap-3">
                        <i class="fa-regular fa-calendar-days text-xl text-emerald-800"></i>
                        <span class="min-w-0 flex-1">
                            <span class="block text-[11px] font-bold text-slate-600">Dari Tanggal</span>
                            <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}" @if($selectedJenisIzin === 'cuti') min="{{ $minimumCutiDate }}" @endif class="mt-1 w-full bg-transparent text-base font-extrabold text-slate-800 outline-none">
                        </span>
                    </div>
                    @error('tanggal_mulai')
                        <p class="mt-2 text-xs font-bold text-red-600">{{ $message }}</p>
                    @enderror
                </label>

                <label class="block rounded-2xl border-2 border-emerald-900/45 bg-white/70 px-4 py-3 shadow-sm">
                    <div class="flex items-center gap-3">
                        <i class="fa-regular fa-calendar-days text-xl text-emerald-800"></i>
                        <span class="min-w-0 flex-1">
                            <span class="block text-[11px] font-bold text-slate-600">Sampai Tanggal</span>
                            <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}" @if($selectedJenisIzin === 'cuti') min="{{ $minimumCutiDate }}" @endif class="mt-1 w-full bg-transparent text-base font-extrabold text-slate-800 outline-none">
                        </span>
                    </div>
                    @error('tanggal_selesai')
                        <p class="mt-2 text-xs font-bold text-red-600">{{ $message }}</p>
                    @enderror
                </label>

                <div class="rounded-2xl border-2 border-emerald-900/45 bg-white/70 px-4 py-3 shadow-sm">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-calculator text-xl text-emerald-800"></i>
                        <span class="min-w-0 flex-1">
                            <span class="block text-[11px] font-bold text-slate-600">Jumlah Hari</span>
                            <span id="leaveDaysCount" class="mt-1 block text-base font-extrabold text-slate-800">-</span>
                        </span>
                    </div>
                </div>

                <label class="block rounded-2xl border-2 border-emerald-900/45 bg-white/70 px-4 py-3 shadow-sm">
                    <div class="flex items-start gap-3">
                        <i class="fa-regular fa-file-lines mt-1 text-xl text-emerald-800"></i>
                        <span class="min-w-0 flex-1">
                            <span class="block text-[11px] font-bold text-slate-600">Keterangan</span>
                            <textarea name="keterangan" rows="3" class="mt-1 w-full resize-none bg-transparent text-base font-extrabold text-slate-800 outline-none" placeholder="Tulis keterangan">{{ old('keterangan') }}</textarea>
                        </span>
                    </div>
                    @error('keterangan')
                        <p class="mt-2 text-xs font-bold text-red-600">{{ $message }}</p>
                    @enderror
                </label>

                <label class="block rounded-2xl border-2 border-emerald-900/45 bg-white/70 px-4 py-3 shadow-sm">
                    <div class="flex items-center gap-3">
                        <i class="fa-solid fa-paperclip text-xl text-emerald-800"></i>
                        <span class="min-w-0 flex-1">
                            <span class="block text-[11px] font-bold text-slate-600">Lampiran</span>
                            <input type="file" name="lampiran" class="mt-1 w-full text-sm font-semibold text-slate-700 file:mr-3 file:rounded-lg file:border-0 file:bg-emerald-700 file:px-3 file:py-2 file:text-xs file:font-bold file:text-white">
                        </span>
                    </div>
                    @error('lampiran')
                        <p class="mt-2 text-xs font-bold text-red-600">{{ $message }}</p>
                    @enderror
                </label>

                <button @disabled($cutiSubmitBlocked) class="flex w-full items-center justify-center gap-3 rounded-xl bg-emerald-700 px-4 py-4 text-base font-extrabold text-white shadow-lg shadow-emerald-900/20 disabled:cursor-not-allowed disabled:bg-slate-400 disabled:shadow-none">
                        <i class="fa-solid fa-paper-plane"></i>
                        {{ $cutiSubmitBlocked ? 'Kuota Cuti Tidak Tersedia' : 'Ajukan ' . $selectedLabel }}
                </button>
            </form>
        </main>

        @include('user.partials.bottom-nav', ['active' => ''])
    </div>
</div>

<script>
    (() => {
        const startInput = document.querySelector('input[name="tanggal_mulai"]');
        const endInput = document.querySelector('input[name="tanggal_selesai"]');
        const output = document.getElementById('leaveDaysCount');

        const updateDays = () => {
            if (!startInput?.value || !endInput?.value || !output) {
                if (output) output.textContent = '-';
                return;
            }

            const start = new Date(`${startInput.value}T00:00:00`);
            const end = new Date(`${endInput.value}T00:00:00`);
            const diff = Math.floor((end - start) / 86400000) + 1;
            output.textContent = diff > 0 ? String(diff) : '-';
        };

        startInput?.addEventListener('change', updateDays);
        endInput?.addEventListener('change', updateDays);
        updateDays();
    })();
</script>
@endsection
