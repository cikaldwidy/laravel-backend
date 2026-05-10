@extends('layouts.admin')

@section('title', 'Tambah Jadwal Pegawai')

@section('content')
@php
    $activeMode = old('form_mode', 'bulk');
    $unitOptions = $units->mapWithKeys(fn ($unit) => [$unit->nama_unit => $unit->nama_unit])->sortKeys();
@endphp

<div class="space-y-5" data-schedule-form>
    <div class="bg-white rounded-md shadow border border-gray-200 p-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-base font-bold text-gray-900">Tambah Jadwal Pegawai</h2>
            <p class="text-xs text-gray-500 mt-1">Pilih cara input, tentukan tanggal dan shift, lalu simpan.</p>
        </div>
        <a href="{{ route('admin.shift_management.schedules', ['tanggal' => $tanggal]) }}" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-md text-sm font-semibold">Lihat Jadwal</a>
    </div>

    <div class="bg-white rounded-md shadow border border-gray-200 p-2">
        <div class="grid sm:grid-cols-3 gap-2" role="tablist" aria-label="Mode tambah jadwal">
            <button type="button" data-mode-tab="single" class="schedule-mode-tab px-4 py-3 rounded-md text-left {{ $activeMode === 'single' ? 'bg-slate-950 text-white' : 'bg-slate-50 text-slate-700 hover:bg-slate-100' }}">
                <span class="block text-sm font-bold">Satu pegawai</span>
                <span class="block text-xs opacity-80 mt-0.5">Untuk koreksi atau input cepat.</span>
            </button>
            <button type="button" data-mode-tab="bulk" class="schedule-mode-tab px-4 py-3 rounded-md text-left {{ $activeMode === 'bulk' ? 'bg-slate-950 text-white' : 'bg-slate-50 text-slate-700 hover:bg-slate-100' }}">
                <span class="block text-sm font-bold">Banyak pegawai</span>
                <span class="block text-xs opacity-80 mt-0.5">Checklist pegawai tanpa Ctrl/Cmd.</span>
            </button>
            <button type="button" data-mode-tab="import" class="schedule-mode-tab px-4 py-3 rounded-md text-left {{ $activeMode === 'import' ? 'bg-slate-950 text-white' : 'bg-slate-50 text-slate-700 hover:bg-slate-100' }}">
                <span class="block text-sm font-bold">Import file</span>
                <span class="block text-xs opacity-80 mt-0.5">Untuk jadwal satu unit.</span>
            </button>
        </div>
    </div>

    <section data-mode-panel="single" class="{{ $activeMode === 'single' ? '' : 'hidden' }}">
        <div class="bg-white rounded-md shadow border border-gray-200 p-4">
            <form action="{{ route('admin.shift_management.schedules.store') }}" method="POST" class="grid lg:grid-cols-[1fr_1fr_auto] gap-4 items-end">
                @csrf
                <input type="hidden" name="form_mode" value="single">

                <div>
                    <label class="text-xs font-semibold text-gray-600">Pegawai</label>
                    <select name="user_id" class="w-full border rounded-md px-3 py-2 text-sm" required>
                        <option value="">Pilih pegawai</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}" @selected((string) old('user_id') === (string) $user->id)>{{ $user->name }}{{ $user->employeeDetail?->unit?->nama_unit ? ' - '.$user->employeeDetail->unit->nama_unit : '' }}</option>
                        @endforeach
                    </select>
                </div>

                @include('admin.shift_management.partials.schedule-fields', [
                    'prefix' => 'single',
                    'tanggal' => $tanggal,
                    'shiftTemplates' => $shiftTemplates,
                ])

                <button type="submit" class="bg-emerald-600 text-white px-5 py-2.5 rounded-md text-sm font-bold">Simpan</button>
            </form>
        </div>
    </section>

    <section data-mode-panel="bulk" class="{{ $activeMode === 'bulk' ? '' : 'hidden' }}">
        <div class="grid xl:grid-cols-[minmax(0,1fr)_22rem] gap-4">
            <form action="{{ route('admin.shift_management.schedules.bulk_assign') }}" method="POST" class="bg-white rounded-md shadow border border-gray-200 p-4 space-y-4">
                @csrf
                <input type="hidden" name="form_mode" value="bulk">

                <div class="grid lg:grid-cols-2 gap-4">
                    @include('admin.shift_management.partials.schedule-fields', [
                        'prefix' => 'bulk',
                        'tanggal' => $tanggal,
                        'shiftTemplates' => $shiftTemplates,
                    ])
                </div>

                <div class="border border-slate-100 rounded-md overflow-hidden">
                    <div class="bg-slate-50 p-3 grid md:grid-cols-[1fr_14rem_auto] gap-2 items-end">
                        <div>
                            <label class="text-xs font-semibold text-gray-600">Cari pegawai</label>
                            <input type="search" data-user-search placeholder="Nama atau unit" class="w-full border rounded-md px-3 py-2 text-sm">
                        </div>
                        <div>
                            <label class="text-xs font-semibold text-gray-600">Filter unit</label>
                            <select data-unit-filter class="w-full border rounded-md px-3 py-2 text-sm">
                                <option value="">Semua unit</option>
                                @foreach($unitOptions as $unitName)
                                    <option value="{{ $unitName }}">{{ $unitName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="button" data-select-visible class="bg-slate-900 text-white px-4 py-2 rounded-md text-sm font-semibold">Pilih tampil</button>
                    </div>

                    <div class="max-h-80 overflow-y-auto divide-y divide-slate-100" data-user-list>
                        @foreach($users as $user)
                            @php($unitName = $user->employeeDetail?->unit?->nama_unit ?? 'Tanpa unit')
                            <label class="flex items-center gap-3 px-3 py-2 hover:bg-slate-50 cursor-pointer" data-user-row data-name="{{ \Illuminate\Support\Str::lower($user->name) }}" data-unit="{{ $unitName }}">
                                <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="rounded border-slate-300 text-emerald-600" @checked(in_array((string) $user->id, old('user_ids', []), true))>
                                <span class="min-w-0">
                                    <span class="block text-sm font-semibold text-slate-800 truncate">{{ $user->name }}</span>
                                    <span class="block text-xs text-slate-500 truncate">{{ $unitName }}</span>
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3">
                    <p class="text-xs text-slate-500"><span data-selected-count>0</span> pegawai dipilih.</p>
                    <button type="submit" class="bg-emerald-600 text-white px-5 py-2.5 rounded-md text-sm font-bold">Simpan Jadwal Banyak Pegawai</button>
                </div>
            </form>

            <aside class="bg-white rounded-md shadow border border-gray-200 p-4 space-y-3">
                <h3 class="text-sm font-bold text-slate-900">Alur cepat</h3>
                <div class="space-y-2 text-xs text-slate-600">
                    <p><span class="font-bold text-slate-900">1.</span> Pilih tanggal dan status.</p>
                    <p><span class="font-bold text-slate-900">2.</span> Pakai template shift, atau isi jam manual.</p>
                    <p><span class="font-bold text-slate-900">3.</span> Cari/filter pegawai, lalu centang yang perlu dijadwalkan.</p>
                </div>
                <div class="rounded-md bg-emerald-50 border border-emerald-100 p-3 text-xs text-emerald-800">
                    Jika pegawai sudah punya jadwal di tanggal itu, sistem akan memperbarui jadwalnya.
                </div>
            </aside>
        </div>
    </section>

    <section data-mode-panel="import" class="{{ $activeMode === 'import' ? '' : 'hidden' }}">
        <div class="grid xl:grid-cols-2 gap-4">
            <div class="bg-white rounded-md shadow border border-gray-200 p-4">
                <div class="mb-4">
                    <h3 class="text-sm font-bold text-slate-900">1. Download template bulanan</h3>
                    <p class="text-xs text-slate-500 mt-1">Template mengikuti format laporan: judul, periode, legend, unit, nama pegawai, dan tanggal sebulan.</p>
                </div>
                <form action="{{ route('admin.shift_management.schedules.import_template') }}" method="GET" class="space-y-3">
                    <div>
                        <label class="text-xs font-semibold text-gray-600">Unit</label>
                        <select name="unit_id" class="w-full border rounded-md px-3 py-2 text-sm" required>
                            <option value="">Pilih unit</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" @selected((string)old('unit_id') === (string)$unit->id)>{{ $unit->nama_unit }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-600">Bulan jadwal</label>
                        <input type="month" name="bulan_import" value="{{ old('bulan_import', \Illuminate\Support\Carbon::parse($tanggal)->format('Y-m')) }}" class="w-full border rounded-md px-3 py-2 text-sm" required>
                    </div>
                    <button type="submit" class="w-full bg-emerald-600 text-white px-5 py-2.5 rounded-md text-sm font-bold">Download Template Excel</button>
                </form>
            </div>

            <div class="bg-white rounded-md shadow border border-gray-200 p-4">
                <div class="mb-4">
                    <h3 class="text-sm font-bold text-slate-900">2. Upload template yang sudah diisi</h3>
                    <p class="text-xs text-slate-500 mt-1">Isi kode shift di kolom tanggal: P, S, M, atau O untuk libur.</p>
                </div>
                <form action="{{ route('admin.shift_management.schedules.import_unit') }}" method="POST" enctype="multipart/form-data" class="space-y-3">
                    @csrf
                    <input type="hidden" name="form_mode" value="import">
                    <div>
                        <label class="text-xs font-semibold text-gray-600">Unit</label>
                        <select name="unit_id" class="w-full border rounded-md px-3 py-2 text-sm" required>
                            <option value="">Pilih unit yang sama dengan template</option>
                            @foreach($units as $unit)
                                <option value="{{ $unit->id }}" @selected((string)old('unit_id') === (string)$unit->id)>{{ $unit->nama_unit }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-600">Bulan jadwal</label>
                        <input type="month" name="bulan_import" value="{{ old('bulan_import', \Illuminate\Support\Carbon::parse($tanggal)->format('Y-m')) }}" class="w-full border rounded-md px-3 py-2 text-sm" required>
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-gray-600">File template</label>
                        <input type="file" name="file" accept=".xlsx,.csv,.txt" class="w-full border rounded-md px-3 py-2 text-sm bg-white" required>
                    </div>
                    <div class="rounded-md bg-slate-50 border border-slate-100 p-3 text-xs text-slate-600 leading-relaxed">
                        Format template: <span class="font-mono">Nama / Tanggal</span> dengan kolom tanggal <span class="font-mono">1, 2, 3, ...</span>.
                        Kode <span class="font-mono">P</span>, <span class="font-mono">S</span>, dan <span class="font-mono">M</span> akan dicocokkan ke master shift Pagi, Sore, dan Malam.
                    </div>
                    <button type="submit" class="w-full bg-slate-950 text-white px-5 py-2.5 rounded-md text-sm font-bold">Import Jadwal Bulanan</button>
                </form>
            </div>
        </div>
    </section>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const root = document.querySelector('[data-schedule-form]');
    if (!root) return;

    const tabs = root.querySelectorAll('[data-mode-tab]');
    const panels = root.querySelectorAll('[data-mode-panel]');
    const setMode = (mode) => {
        tabs.forEach((tab) => {
            const active = tab.dataset.modeTab === mode;
            tab.classList.toggle('bg-slate-950', active);
            tab.classList.toggle('text-white', active);
            tab.classList.toggle('bg-slate-50', !active);
            tab.classList.toggle('text-slate-700', !active);
            tab.classList.toggle('hover:bg-slate-100', !active);
        });
        panels.forEach((panel) => panel.classList.toggle('hidden', panel.dataset.modePanel !== mode));
    };
    tabs.forEach((tab) => tab.addEventListener('click', () => setMode(tab.dataset.modeTab)));

    const search = root.querySelector('[data-user-search]');
    const unit = root.querySelector('[data-unit-filter]');
    const rows = Array.from(root.querySelectorAll('[data-user-row]'));
    const selectedCount = root.querySelector('[data-selected-count]');
    const updateCount = () => {
        selectedCount.textContent = root.querySelectorAll('[data-user-row] input[type="checkbox"]:checked').length;
    };
    const filterRows = () => {
        const keyword = (search?.value || '').toLowerCase().trim();
        const selectedUnit = unit?.value || '';
        rows.forEach((row) => {
            const matchText = !keyword || row.dataset.name.includes(keyword) || row.dataset.unit.toLowerCase().includes(keyword);
            const matchUnit = !selectedUnit || row.dataset.unit === selectedUnit;
            row.classList.toggle('hidden', !(matchText && matchUnit));
        });
    };
    search?.addEventListener('input', filterRows);
    unit?.addEventListener('change', filterRows);
    root.querySelector('[data-select-visible]')?.addEventListener('click', () => {
        rows.filter((row) => !row.classList.contains('hidden')).forEach((row) => {
            row.querySelector('input[type="checkbox"]').checked = true;
        });
        updateCount();
    });
    rows.forEach((row) => row.querySelector('input[type="checkbox"]').addEventListener('change', updateCount));
    updateCount();
});
</script>
@endsection
