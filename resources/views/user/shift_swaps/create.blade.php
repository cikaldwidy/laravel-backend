@extends('layouts.app')

@section('title', 'Ajukan Tukar Shift')

@section('content')
<div class="user-page">
    <div class="user-phone">
        @include('user.partials.header', [
            'title' => 'Form Tukar Shift',
            'subtitle' => 'Ajukan penukaran shift dalam unit yang sama.',
            'back' => route('shift-swaps.index'),
        ])

        <main class="px-4 pt-4">
            <section class="user-card p-4 space-y-4">
                <p class="text-sm text-slate-600">
                    Penukaran shift hanya bisa dilakukan dengan pegawai dalam unit yang sama{{ $unitName ? ', yaitu ' . $unitName : '' }}. Shift target yang tampil adalah jadwal aktif yang belum selesai.
                </p>

            @if($errors->any())
                <div class="rounded-2xl bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">
                    <ul class="list-disc pl-5 space-y-1">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('shift-swaps.store') }}" method="POST" class="space-y-4" id="swapForm">
                @csrf
                <div>
                    <label class="text-xs font-semibold text-slate-600">Shift Saya</label>
                    <select name="shift_id" id="shift_id" class="user-field mt-1" required>
                        <option value="">Pilih shift Anda</option>
                        @foreach($myShifts as $shift)
                            <option value="{{ $shift->id }}">{{ $shift->tanggal->format('d/m/Y') }} | {{ $shift->jam_masuk?->format('H:i') }} - {{ $shift->jam_pulang?->format('H:i') }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-600">Teman Target</label>
                    <select name="target_user_id" id="target_user_id" class="user-field mt-1" required>
                        <option value="">Pilih user target</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                    @if($users->isEmpty())
                        <p class="mt-1 text-xs text-amber-600">Belum ada pegawai lain dalam unit yang sama.</p>
                    @endif
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-600">Shift Target</label>
                    <select name="target_shift_id" id="target_shift_id" class="user-field mt-1" required>
                        <option value="">Pilih shift saya dan user target dulu</option>
                    </select>
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-600">Alasan (Opsional)</label>
                    <textarea name="note" rows="3" class="user-field mt-1" placeholder="Contoh: ada keperluan keluarga.">{{ old('note') }}</textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('shift-swaps.index') }}" class="user-btn-secondary">Status</a>
                    <button class="user-btn-primary">
                        <i class="fa-solid fa-paper-plane"></i>
                        Kirim
                    </button>
                </div>
            </form>
            </section>
        </main>

        @include('user.partials.bottom-nav', ['active' => ''])
    </div>
</div>

<script>
(function () {
    const shiftEl = document.getElementById('shift_id');
    const userEl = document.getElementById('target_user_id');
    const targetShiftEl = document.getElementById('target_shift_id');

    async function loadTargetShifts() {
        const shiftId = shiftEl.value;
        const targetUserId = userEl.value;

        targetShiftEl.innerHTML = '<option value="">Memuat...</option>';

        if (!shiftId || !targetUserId) {
            targetShiftEl.innerHTML = '<option value="">Pilih shift saya dan user target dulu</option>';
            return;
        }

        const url = `{{ route('shift-swaps.target-shifts') }}?shift_id=${encodeURIComponent(shiftId)}&target_user_id=${encodeURIComponent(targetUserId)}`;

        try {
            const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
            const items = await res.json();

            if (!Array.isArray(items) || items.length === 0) {
                targetShiftEl.innerHTML = '<option value="">Tidak ada shift target aktif yang belum selesai</option>';
                return;
            }

            targetShiftEl.innerHTML = '<option value="">Pilih shift target</option>';
            items.forEach((item) => {
                const option = document.createElement('option');
                option.value = item.id;
                option.textContent = item.text;
                targetShiftEl.appendChild(option);
            });
        } catch (error) {
            targetShiftEl.innerHTML = '<option value="">Gagal memuat data target</option>';
        }
    }

    shiftEl.addEventListener('change', loadTargetShifts);
    userEl.addEventListener('change', loadTargetShifts);
})();
</script>
@endsection
