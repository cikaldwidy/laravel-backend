@extends('layouts.admin')

@section('title', 'Tambah Akun Pegawai')

@section('content')
@php
    $departmentOptions = $departments->map(fn ($department) => [
        'id' => $department->id,
        'name' => $department->nama_departemen,
        'positions' => $department->positions->map(fn ($position) => ['id' => $position->id, 'name' => $position->nama_jabatan])->values(),
    ])->values();
    $selectedDepartmentId = old('department_id');
    $selectedPositionId = old('position_id');
@endphp

<style>
    .admin-edit-field {
        width: 100%;
        border: 1px solid #e5e7eb;
        border-radius: 0.5rem;
        background: #f9fafb;
        padding: 0.75rem 0.875rem;
        font-size: 0.875rem;
        font-weight: 600;
        color: #1f2937;
        outline: none;
        transition: 150ms ease;
    }
    .admin-edit-field:focus {
        border-color: #2563eb;
        background: #fff;
        box-shadow: 0 0 0 3px rgb(37 99 235 / 0.12);
    }
    .admin-edit-label {
        margin-bottom: 0.4rem;
        display: block;
        font-size: 0.75rem;
        font-weight: 700;
        color: #6b7280;
    }
</style>

<div class="space-y-4">
    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-500 transition hover:text-blue-700">
        <i class="fas fa-arrow-left text-xs"></i>
        Kembali ke Akun Pegawai
    </a>

    <section class="overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-md bg-blue-50 text-blue-700">
                    <i class="fas fa-user-plus"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-gray-800">Tambah Akun Pegawai</h1>
                    <p class="text-xs text-gray-500">Buat akun login sekaligus lengkapi profil dan pekerjaan.</p>
                </div>
            </div>
        </div>

        <div class="grid xl:grid-cols-[17rem_1fr]">
            <aside class="border-b border-gray-100 bg-gray-50/60 p-5 xl:border-b-0 xl:border-r">
                <div class="rounded-md border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="flex aspect-square items-center justify-center rounded-md bg-blue-50 text-5xl font-bold text-blue-700">
                        <i class="fa-regular fa-user"></i>
                    </div>
                    <div class="mt-4 text-center">
                        <h2 class="text-base font-bold text-gray-800">Pegawai Baru</h2>
                        <p class="mt-1 text-sm text-gray-500">Data akan dibuat setelah disimpan.</p>
                    </div>
                </div>
                <div class="mt-4 rounded-md border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Foto Profil</p>
                    <p class="mt-2 text-xs leading-5 text-gray-500">Upload foto di bagian bawah form. Format JPG/PNG, maksimal 2 MB.</p>
                </div>
            </aside>

            <main class="p-5 lg:p-6">
                <form method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data" class="space-y-8">
                    @csrf

                    <section>
                        <h3 class="border-b border-gray-100 pb-3 text-sm font-bold text-gray-700">Data Akun</h3>
                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="admin-edit-label">Nama</label>
                                <input name="name" value="{{ old('name') }}" class="admin-edit-field">
                                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="admin-edit-label">Username</label>
                                <input name="username" value="{{ old('username') }}" class="admin-edit-field" placeholder="Contoh: budi_santoso">
                                <p class="mt-1 text-xs text-gray-500">Gunakan huruf, angka, tanda hubung, atau underscore.</p>
                                @error('username') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="admin-edit-label">Email</label>
                                <input type="email" name="email" value="{{ old('email') }}" class="admin-edit-field">
                                @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="admin-edit-label">Password</label>
                                <input type="password" name="password" class="admin-edit-field">
                                @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </section>

                    <section>
                        <h3 class="border-b border-gray-100 pb-3 text-sm font-bold text-gray-700">Profil Pribadi</h3>
                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="admin-edit-label">No. HP</label>
                                <input name="no_hp" value="{{ old('no_hp') }}" class="admin-edit-field">
                                @error('no_hp') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="admin-edit-label">Tanggal Lahir</label>
                                <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir') }}" class="admin-edit-field">
                                @error('tanggal_lahir') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="admin-edit-label">Jenis Kelamin</label>
                                <select name="jenis_kelamin" class="admin-edit-field">
                                    <option value="">Pilih jenis kelamin</option>
                                    <option value="L" @selected(old('jenis_kelamin') === 'L')>Laki-laki</option>
                                    <option value="P" @selected(old('jenis_kelamin') === 'P')>Perempuan</option>
                                </select>
                                @error('jenis_kelamin') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="admin-edit-label">Agama</label>
                                <select name="agama" class="admin-edit-field">
                                    <option value="">Pilih agama</option>
                                    @foreach(['Islam', 'Kristen Protestan', 'Katolik', 'Hindu', 'Buddha', 'Konghucu', 'Lainnya'] as $agama)
                                        <option value="{{ $agama }}" @selected(old('agama') === $agama)>{{ $agama }}</option>
                                    @endforeach
                                </select>
                                @error('agama') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="admin-edit-label">NIK</label>
                                <input name="nik" value="{{ old('nik') }}" class="admin-edit-field">
                                @error('nik') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="admin-edit-label">Alamat</label>
                            <textarea name="alamat" rows="4" class="admin-edit-field">{{ old('alamat') }}</textarea>
                            @error('alamat') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </section>

                    <section>
                        <h3 class="border-b border-gray-100 pb-3 text-sm font-bold text-gray-700">Data Pekerjaan</h3>
                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="admin-edit-label">NIP</label>
                                <input name="nip" value="{{ old('nip') }}" class="admin-edit-field">
                                @error('nip') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="admin-edit-label">Status Kerja</label>
                                <select name="status_kerja" class="admin-edit-field">
                                    <option value="">Pilih status</option>
                                    <option value="tetap" @selected(old('status_kerja') === 'tetap')>Tetap</option>
                                    <option value="kontrak" @selected(old('status_kerja') === 'kontrak')>Kontrak</option>
                                    <option value="magang" @selected(old('status_kerja') === 'magang')>Magang</option>
                                </select>
                                @error('status_kerja') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="admin-edit-label">Unit Kerja/Bagian</label>
                                <select name="department_id" id="department_id" class="admin-edit-field">
                                    <option value="">Pilih unit kerja/bagian</option>
                                    @foreach($departments as $department)
                                        <option value="{{ $department->id }}" @selected((string) $selectedDepartmentId === (string) $department->id)>{{ $department->nama_departemen }}</option>
                                    @endforeach
                                </select>
                                @error('department_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="admin-edit-label">Jabatan</label>
                                <select name="position_id" id="position_id" class="admin-edit-field">
                                    <option value="">Pilih jabatan</option>
                                </select>
                                @error('position_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </section>

                    <section>
                        <h3 class="border-b border-gray-100 pb-3 text-sm font-bold text-gray-700">Foto Profil</h3>
                        <div class="mt-4">
                            <label class="admin-edit-label">Upload Foto</label>
                            <input type="file" name="foto" accept="image/png,image/jpeg" class="admin-edit-field bg-white">
                            <p class="mt-1 text-xs text-gray-500">Format JPG/PNG, maksimal 2 MB.</p>
                            @error('foto') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </section>

                    <div class="flex flex-wrap items-center gap-2 border-t border-gray-100 pt-5">
                        <button class="rounded-md bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                            Simpan Akun Pegawai
                        </button>
                        <a href="{{ route('admin.users.index') }}" class="rounded-md border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-600 transition hover:bg-gray-50">
                            Batal
                        </a>
                    </div>
                </form>
            </main>
        </div>
    </section>
</div>

<script>
const departmentOptions = @json($departmentOptions);
const departmentSelect = document.getElementById('department_id');
const positionSelect = document.getElementById('position_id');
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

function syncDepartmentPositions(useStoredSelection = false) {
    const selectedDepartment = departmentOptions.find((department) => String(department.id) === departmentSelect.value);
    const positionValue = useStoredSelection ? selectedPositionId : positionSelect.value;

    fillDependentOptions(positionSelect, selectedDepartment?.positions ?? [], positionValue, 'Pilih jabatan');
}

departmentSelect?.addEventListener('change', () => syncDepartmentPositions(false));
syncDepartmentPositions(true);
</script>
@endsection
