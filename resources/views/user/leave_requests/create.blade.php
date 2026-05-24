@extends('layouts.app')

@section('title', 'Buat Izin')

@section('content')
<div class="user-page">
    <div class="user-phone">
        @include('user.partials.header', [
            'title' => 'Form Izin',
            'subtitle' => 'Ajukan izin, sakit, cuti, atau dinas luar.',
            'back' => $backUrl ?? route('leave_requests.index'),
        ])

        <main class="px-4 pt-4">
            <form method="POST" action="{{ route('leave_requests.store') }}" enctype="multipart/form-data" class="user-card p-4 space-y-4">
                @csrf
                <div>
                    <label class="text-xs font-semibold text-slate-600">Jenis Izin</label>
                    <select name="jenis_izin" class="user-field mt-1">
                        @if(\App\Models\FeatureSetting::enabled('sakit', 'user'))
                            <option value="sakit" @selected($selectedJenisIzin === 'sakit')>Sakit</option>
                        @endif
                        @if(\App\Models\FeatureSetting::enabled('cuti', 'user'))
                            <option value="cuti" @selected($selectedJenisIzin === 'cuti')>Cuti</option>
                        @endif
                        <option value="izin" @selected($selectedJenisIzin === 'izin')>Izin</option>
                        <option value="dinas" @selected($selectedJenisIzin === 'dinas')>Dinas Luar</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Tanggal Mulai</label>
                        <input type="date" name="tanggal_mulai" value="{{ old('tanggal_mulai') }}" class="user-field mt-1">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Tanggal Selesai</label>
                        <input type="date" name="tanggal_selesai" value="{{ old('tanggal_selesai') }}" class="user-field mt-1">
                    </div>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-600">Keterangan</label>
                    <textarea name="keterangan" rows="4" class="user-field mt-1">{{ old('keterangan') }}</textarea>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-600">Lampiran</label>
                    <input type="file" name="lampiran" class="user-field mt-1">
                </div>
                <div class="grid grid-cols-2 gap-3 pt-1">
                    <a href="{{ $backUrl ?? route('leave_requests.index') }}" class="user-btn-secondary">Kembali</a>
                    <button class="user-btn-primary">
                        <i class="fa-solid fa-paper-plane"></i>
                        Submit
                    </button>
                </div>
            </form>
        </main>

        @include('user.partials.bottom-nav', ['active' => ''])
    </div>
</div>
@endsection
