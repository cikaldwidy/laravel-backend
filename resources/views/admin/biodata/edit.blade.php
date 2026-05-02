@extends('layouts.admin')

@section('title', 'Edit Biodata Pegawai')

@section('content')
<div class="bg-white p-6 rounded-xl shadow max-w-5xl space-y-5">
    <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3">
        <div>
            <h2 class="font-bold text-lg text-gray-800">Biodata: {{ $user->name }}</h2>
            <p class="text-sm text-gray-500">{{ $user->email }}</p>
        </div>
        <a href="{{ route('admin.biodata.index') }}" class="px-4 py-2 rounded border text-sm font-semibold hover:bg-gray-50">Kembali</a>
    </div>

    <form method="POST" action="{{ route('admin.biodata.update', $user) }}" enctype="multipart/form-data" class="space-y-6">
        @csrf

        <section class="space-y-4">
            <div>
                <h3 class="font-bold text-gray-800">Data Akun</h3>
                <p class="text-sm text-gray-500">Nama dan email diubah dari menu Akun Pegawai.</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-semibold text-gray-700">Nama</label>
                    <input value="{{ $user->name }}" readonly class="w-full p-2 border rounded bg-gray-50 mt-1">
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Email</label>
                    <input value="{{ $user->email }}" readonly class="w-full p-2 border rounded bg-gray-50 mt-1">
                </div>
            </div>
        </section>

        <section class="space-y-4 border-t pt-5">
            <h3 class="font-bold text-gray-800">Profil Pribadi</h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-semibold text-gray-700">No. HP</label>
                    <input name="no_hp" value="{{ old('no_hp', $profile?->no_hp) }}" class="w-full p-2 border rounded mt-1">
                    @error('no_hp') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Tanggal Lahir</label>
                    <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', optional($profile?->tanggal_lahir)->toDateString()) }}" class="w-full p-2 border rounded mt-1">
                    @error('tanggal_lahir') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Jenis Kelamin</label>
                    <select name="jenis_kelamin" class="w-full p-2 border rounded mt-1">
                        <option value="">Pilih jenis kelamin</option>
                        <option value="L" @selected(old('jenis_kelamin', $profile?->jenis_kelamin) === 'L')>Laki-laki</option>
                        <option value="P" @selected(old('jenis_kelamin', $profile?->jenis_kelamin) === 'P')>Perempuan</option>
                    </select>
                    @error('jenis_kelamin') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">NIK</label>
                    <input name="nik" value="{{ old('nik', $profile?->nik) }}" class="w-full p-2 border rounded mt-1">
                    @error('nik') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
            <div>
                <label class="text-sm font-semibold text-gray-700">Alamat</label>
                <textarea name="alamat" rows="3" class="w-full p-2 border rounded mt-1">{{ old('alamat', $profile?->alamat) }}</textarea>
                @error('alamat') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
            </div>
        </section>

        <section class="space-y-4 border-t pt-5">
            <h3 class="font-bold text-gray-800">Data Pekerjaan</h3>
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="text-sm font-semibold text-gray-700">NIP</label>
                    <input name="nip" value="{{ old('nip', $employeeDetail?->nip) }}" class="w-full p-2 border rounded mt-1">
                    @error('nip') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Status Kerja</label>
                    <select name="status_kerja" class="w-full p-2 border rounded mt-1">
                        <option value="">Pilih status</option>
                        <option value="tetap" @selected(old('status_kerja', $employeeDetail?->status_kerja) === 'tetap')>Tetap</option>
                        <option value="kontrak" @selected(old('status_kerja', $employeeDetail?->status_kerja) === 'kontrak')>Kontrak</option>
                        <option value="magang" @selected(old('status_kerja', $employeeDetail?->status_kerja) === 'magang')>Magang</option>
                    </select>
                    @error('status_kerja') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Departemen</label>
                    <select name="department_id" id="department_id" class="w-full p-2 border rounded mt-1">
                        <option value="">Pilih departemen</option>
                        @foreach($departments as $department)
                            <option value="{{ $department->id }}" @selected((string) $selectedDepartmentId === (string) $department->id)>{{ $department->nama_departemen }}</option>
                        @endforeach
                    </select>
                    @error('department_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Unit</label>
                    <select name="unit_id" id="unit_id" class="w-full p-2 border rounded mt-1">
                        <option value="">Pilih unit</option>
                    </select>
                    @error('unit_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Jabatan</label>
                    <select name="position_id" id="position_id" class="w-full p-2 border rounded mt-1">
                        <option value="">Pilih jabatan</option>
                    </select>
                    @error('position_id') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <section class="space-y-4 border-t pt-5">
            <h3 class="font-bold text-gray-800">Foto Profil</h3>
            <div class="grid md:grid-cols-[8rem_1fr] gap-4 items-start">
                <div class="w-28 h-28 rounded-lg overflow-hidden bg-gray-100 border flex items-center justify-center">
                    @if($profile?->foto)
                        <img src="{{ asset('storage/' . $profile->foto) }}" alt="Foto {{ $user->name }}" class="w-full h-full object-cover">
                    @else
                        <i class="fa-regular fa-user text-3xl text-gray-400"></i>
                    @endif
                </div>
                <div>
                    <label class="text-sm font-semibold text-gray-700">Upload Foto Baru</label>
                    <input type="file" name="foto" accept="image/png,image/jpeg" class="w-full p-2 border rounded mt-1 bg-white">
                    <p class="text-xs text-gray-500 mt-1">Format JPG/PNG, maksimal 2 MB.</p>
                    @error('foto') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </section>

        <div class="flex items-center gap-2 border-t pt-5">
            <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-semibold">Simpan Biodata</button>
            <a href="{{ route('admin.biodata.index') }}" class="px-4 py-2 rounded border font-semibold">Batal</a>
        </div>
    </form>
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
