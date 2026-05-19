@extends('layouts.app')

@section('title', 'Edit Profil')

@section('content')
<div class="user-page">
    <div class="user-phone">
        @include('user.partials.header', [
            'title' => 'Edit Biodata',
            'subtitle' => 'Lengkapi biodata dan detail kepegawaian.',
            'back' => route('profile.index'),
        ])

        <main class="px-4 pt-4">
            <form method="POST" action="{{ route('profile.update') }}" enctype="multipart/form-data" class="user-card p-4 space-y-4">
                @csrf

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Nama</label>
                        <input value="{{ $user->name }}" readonly class="user-field mt-1 bg-slate-50">
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Email</label>
                        <input value="{{ $user->email }}" readonly class="user-field mt-1 bg-slate-50">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-slate-600">No. HP</label>
                        <input name="no_hp" value="{{ old('no_hp', $profile?->no_hp) }}" class="user-field mt-1">
                        @error('no_hp') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Tanggal Lahir</label>
                        <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', optional($profile?->tanggal_lahir)->toDateString()) }}" class="user-field mt-1">
                        @error('tanggal_lahir') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="user-field mt-1">
                            <option value="">-- pilih --</option>
                            <option value="L" @selected(old('jenis_kelamin', $profile?->jenis_kelamin) === 'L')>L</option>
                            <option value="P" @selected(old('jenis_kelamin', $profile?->jenis_kelamin) === 'P')>P</option>
                        </select>
                        @error('jenis_kelamin') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600">NIK (Opsional)</label>
                        <input name="nik" value="{{ old('nik', $profile?->nik) }}" class="user-field mt-1">
                        @error('nik') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-600">Alamat</label>
                    <textarea name="alamat" rows="3" class="user-field mt-1">{{ old('alamat', $profile?->alamat) }}</textarea>
                    @error('alamat') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    @php
                        $departmentOptions = $departments->map(fn ($department) => [
                            'id' => $department->id,
                            'name' => $department->nama_departemen,
                            'units' => $department->units->map(fn ($unit) => ['id' => $unit->id, 'name' => $unit->nama_unit])->values(),
                            'positions' => $department->positions->map(fn ($position) => ['id' => $position->id, 'name' => $position->nama_jabatan])->values(),
                        ])->values();
                        $selectedDepartmentId = old('department_id', $employeeDetail?->department_id);
                        $selectedUnitId = old('unit_id', $employeeDetail?->unit_id);
                        $selectedPositionId = old('position_id', $employeeDetail?->position_id);
                    @endphp
                    <div>
                        <label class="text-xs font-semibold text-slate-600">NIP</label>
                        <input name="nip" value="{{ old('nip', $employeeDetail?->nip) }}" class="user-field mt-1">
                        @error('nip') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Status Kerja</label>
                        <select name="status_kerja" class="user-field mt-1">
                            <option value="">-- pilih --</option>
                            <option value="tetap" @selected(old('status_kerja', $employeeDetail?->status_kerja) === 'tetap')>tetap</option>
                            <option value="kontrak" @selected(old('status_kerja', $employeeDetail?->status_kerja) === 'kontrak')>kontrak</option>
                            <option value="magang" @selected(old('status_kerja', $employeeDetail?->status_kerja) === 'magang')>magang</option>
                        </select>
                        @error('status_kerja') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Departemen</label>
                        <select name="department_id" id="department_id" class="user-field mt-1">
                            <option value="">Pilih departemen</option>
                            @foreach($departments as $department)
                                <option value="{{ $department->id }}" @selected((string) $selectedDepartmentId === (string) $department->id)>{{ $department->nama_departemen }}</option>
                            @endforeach
                        </select>
                        @error('department_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Unit</label>
                        <select name="unit_id" id="unit_id" class="user-field mt-1">
                            <option value="">Pilih unit</option>
                        </select>
                        @error('unit_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600">Jabatan</label>
                        <select name="position_id" id="position_id" class="user-field mt-1">
                            <option value="">Pilih jabatan</option>
                        </select>
                        @error('position_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="text-xs font-semibold text-slate-600">Upload Foto (jpg/png)</label>
                    <input type="file" name="foto" accept="image/png,image/jpeg" class="user-field mt-1">
                    @error('foto') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                    @if($profile?->foto)
                        <p class="text-xs text-gray-500 mt-1">Foto saat ini tersimpan.</p>
                    @endif
                </div>

                <div class="grid grid-cols-2 gap-3 pt-2">
                    <a href="{{ route('profile.index') }}" class="user-btn-secondary">
                        Batal
                    </a>
                    <button class="user-btn-primary">
                        Simpan
                    </button>
                </div>
            </form>
        </main>

        @include('user.partials.bottom-nav', ['active' => 'profile'])
    </div>
</div>
<script>
const departmentOptions = @json($departmentOptions);
const departmentSelect = document.getElementById('department_id');
const unitSelect = document.getElementById('unit_id');
const positionSelect = document.getElementById('position_id');
const selectedUnitId = @json((string) $selectedUnitId);
const selectedPositionId = @json((string) $selectedPositionId);

function fillDependentOptions(select, items, selectedValue, placeholder) {
    select.innerHTML = '';

    const placeholderOption = document.createElement('option');
    placeholderOption.value = '';
    placeholderOption.textContent = placeholder;
    select.appendChild(placeholderOption);

    items.forEach((item) => {
        const option = document.createElement('option');
        option.value = String(item.id);
        option.textContent = item.name;
        option.selected = String(selectedValue) === String(item.id);
        select.appendChild(option);
    });
}

function syncDepartmentRelations(useStoredSelection = false) {
    const selectedDepartment = departmentOptions.find((department) => String(department.id) === departmentSelect.value);
    const unitValue = useStoredSelection ? selectedUnitId : unitSelect.value;
    const positionValue = useStoredSelection ? selectedPositionId : positionSelect.value;

    fillDependentOptions(unitSelect, selectedDepartment?.units ?? [], unitValue, 'Pilih unit');
    fillDependentOptions(positionSelect, selectedDepartment?.positions ?? [], positionValue, 'Pilih jabatan');
}

departmentSelect?.addEventListener('change', () => syncDepartmentRelations(false));
syncDepartmentRelations(true);
</script>
@endsection
