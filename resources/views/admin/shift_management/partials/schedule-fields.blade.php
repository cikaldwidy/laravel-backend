<div class="grid sm:grid-cols-2 gap-3">
    <div>
        <label class="text-xs font-semibold text-gray-600">Tanggal</label>
        <input type="date" name="tanggal" value="{{ old('tanggal', $tanggal) }}" class="w-full border rounded-md px-3 py-2 text-sm" required>
    </div>
    <div>
        <label class="text-xs font-semibold text-gray-600">Status</label>
        <select name="status" class="w-full border rounded-md px-3 py-2 text-sm" required>
            <option value="aktif" @selected(old('status', 'aktif') === 'aktif')>Masuk</option>
            <option value="libur" @selected(old('status') === 'libur')>Libur</option>
        </select>
    </div>
</div>

<div>
    <label class="text-xs font-semibold text-gray-600">Template shift</label>
    <select name="shift_id" class="w-full border rounded-md px-3 py-2 text-sm">
        <option value="">Manual / tanpa template</option>
        @foreach($shiftTemplates as $shift)
            <option value="{{ $shift->id }}" @selected((string) old('shift_id') === (string) $shift->id)>
                {{ $shift->nama_shift }} ({{ \Illuminate\Support\Str::of($shift->jam_masuk)->substr(0,5) }} - {{ \Illuminate\Support\Str::of($shift->jam_pulang)->substr(0,5) }})
            </option>
        @endforeach
    </select>
    <p class="text-xs text-slate-500 mt-1">Jika pilih template, jam manual boleh dikosongkan.</p>
</div>

<div class="grid sm:grid-cols-2 gap-3">
    <div>
        <label class="text-xs font-semibold text-gray-600">Jam masuk manual</label>
        <input type="time" name="jam_masuk" value="{{ old('jam_masuk') }}" class="w-full border rounded-md px-3 py-2 text-sm">
    </div>
    <div>
        <label class="text-xs font-semibold text-gray-600">Jam pulang manual</label>
        <input type="time" name="jam_pulang" value="{{ old('jam_pulang') }}" class="w-full border rounded-md px-3 py-2 text-sm">
    </div>
</div>
