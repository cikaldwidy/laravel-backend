@extends('layouts.app')

@section('title', 'Ajukan Tukar Shift')

@section('content')
<div class="min-h-dvh bg-slate-50 py-4">
    <div class="max-w-2xl mx-auto px-4">
        <div class="bg-white border border-slate-200 rounded-2xl p-5 shadow-sm space-y-4">
            <h1 class="text-lg font-bold text-slate-800">Form Tukar Shift</h1>

            @if($errors->any())
                <div class="rounded-md bg-red-100 border border-red-200 text-red-700 px-4 py-3 text-sm">
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
                    <select name="shift_id" id="shift_id" class="w-full border rounded-md px-3 py-2 text-sm" required>
                        <option value="">Pilih shift Anda</option>
                        @foreach($myShifts as $shift)
                            <option value="{{ $shift->id }}">{{ $shift->tanggal->format('d/m/Y') }} | {{ $shift->jam_masuk?->format('H:i') }} - {{ $shift->jam_pulang?->format('H:i') }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-600">Teman Target</label>
                    <select name="target_user_id" id="target_user_id" class="w-full border rounded-md px-3 py-2 text-sm" required>
                        <option value="">Pilih user target</option>
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-600">Shift Target (Ajax)</label>
                    <select name="target_shift_id" id="target_shift_id" class="w-full border rounded-md px-3 py-2 text-sm" required>
                        <option value="">Pilih shift saya dan user target dulu</option>
                    </select>
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-600">Alasan (Opsional)</label>
                    <textarea name="note" rows="3" class="w-full border rounded-md px-3 py-2 text-sm" placeholder="Contoh: ada keperluan keluarga.">{{ old('note') }}</textarea>
                </div>

                <div class="flex items-center gap-2">
                    <button class="bg-emerald-600 text-white rounded-md px-4 py-2 text-sm font-semibold">Kirim Request</button>
                    <a href="{{ route('shift-swaps.index') }}" class="bg-slate-200 text-slate-700 rounded-md px-4 py-2 text-sm font-semibold">Lihat Status</a>
                </div>
            </form>
        </div>
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
                targetShiftEl.innerHTML = '<option value="">Tidak ada shift target yang cocok</option>';
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
