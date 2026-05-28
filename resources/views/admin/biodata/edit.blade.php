@extends('layouts.admin')

@section('title', 'Edit Biodata Pegawai')

@section('content')
@php
    $departmentOptions = $departments->map(fn ($department) => [
        'id' => $department->id,
        'name' => $department->nama_departemen,
        'positions' => $department->positions->map(fn ($position) => ['id' => $position->id, 'name' => $position->nama_jabatan])->values(),
    ])->values();
    $selectedDepartmentId = old('department_id', $employeeDetail?->department_id);
    $selectedPositionId = old('position_id', $employeeDetail?->position_id);
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

    html[data-admin-theme="dark"] main .employee-biodata-edit-page .admin-edit-field {
        background: #0b1728 !important;
        border-color: var(--admin-border) !important;
        color: var(--admin-ink) !important;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.025);
    }

    html[data-admin-theme="dark"] main .employee-biodata-edit-page .admin-edit-field:focus {
        background: #0b1728 !important;
        border-color: var(--admin-blue) !important;
        box-shadow: 0 0 0 3px rgba(96, 165, 250, .14) !important;
    }

    html[data-admin-theme="dark"] main .employee-biodata-edit-page .admin-edit-field[readonly] {
        background: #101c2e !important;
        color: #cbd5e1 !important;
    }

    html[data-admin-theme="dark"] main .employee-biodata-edit-page .admin-edit-label {
        color: #8fa3bf !important;
    }

    html[data-admin-theme="dark"] main .employee-biodata-edit-shell,
    html[data-admin-theme="dark"] main .employee-biodata-edit-card {
        background: #111f33 !important;
        border-color: var(--admin-border) !important;
    }

    html[data-admin-theme="dark"] main .employee-biodata-edit-sidebar {
        background: #0b1728 !important;
        border-color: var(--admin-border) !important;
    }

    html[data-admin-theme="dark"] main .employee-biodata-edit-content {
        background: #111f33 !important;
    }

    html[data-admin-theme="dark"] main .employee-biodata-section-title,
    html[data-admin-theme="dark"] main .employee-biodata-actions {
        border-color: var(--admin-border) !important;
        color: var(--admin-ink) !important;
    }

    html[data-admin-theme="dark"] main .employee-biodata-cancel {
        background: #0b1728 !important;
        border-color: var(--admin-border) !important;
        color: #cbd5e1 !important;
    }

    html[data-admin-theme="dark"] main .employee-biodata-cancel:hover {
        background: rgba(96, 165, 250, .12) !important;
        color: var(--admin-ink) !important;
    }
</style>

<div class="employee-biodata-edit-page space-y-4">
    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-500 transition hover:text-blue-700">
        <i class="fas fa-arrow-left text-xs"></i>
        Kembali ke Akun Pegawai
    </a>

    <section class="employee-biodata-edit-shell overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-md bg-blue-50 text-blue-700">
                    <i class="fas fa-id-card"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-gray-800">Edit Biodata Pegawai</h1>
                    <p class="text-xs text-gray-500">Perbarui profil pribadi, pekerjaan, alamat, dan foto pegawai.</p>
                </div>
            </div>
           
        </div>

        <div class="grid xl:grid-cols-[17rem_1fr]">
            <aside class="employee-biodata-edit-sidebar border-b border-gray-100 bg-gray-50/60 p-5 xl:border-b-0 xl:border-r">
                <div class="employee-biodata-edit-card rounded-md border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="aspect-square overflow-hidden rounded-md bg-blue-50">
                        @if($profile?->foto)
                            <img src="{{ asset('storage/' . $profile->foto) }}" alt="Foto {{ $user->name }}" class="h-full w-full object-cover">
                        @else
                            <div class="flex h-full w-full items-center justify-center text-5xl font-bold text-blue-700">
                                {{ strtoupper(substr($user->name, 0, 1)) }}
                            </div>
                        @endif
                    </div>
                    <div class="mt-4 text-center">
                        <h2 class="text-base font-bold text-gray-800">{{ $user->name }}</h2>
                        <p class="mt-1 text-sm text-gray-500">{{ $user->email }}</p>
                    </div>
                </div>

                <div class="employee-biodata-edit-card mt-4 rounded-md border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Foto Profil</p>
                    <p class="mt-2 text-xs leading-5 text-gray-500">Format JPG/PNG, maksimal 2 MB.</p>
                </div>
            </aside>

            <main class="employee-biodata-edit-content p-5 lg:p-6">
                <form method="POST" action="{{ route('admin.biodata.update', $user) }}" enctype="multipart/form-data" class="space-y-8">
                    @csrf

                    <section>
                        <h3 class="employee-biodata-section-title border-b border-gray-100 pb-3 text-sm font-bold text-gray-700">Data Akun</h3>
                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="admin-edit-label">Nama</label>
                                <input value="{{ $user->name }}" readonly class="admin-edit-field">
                            </div>
                            <div>
                                <label class="admin-edit-label">Email</label>
                                <input value="{{ $user->email }}" readonly class="admin-edit-field">
                            </div>
                        </div>
                    </section>

                    <section>
                        <h3 class="employee-biodata-section-title border-b border-gray-100 pb-3 text-sm font-bold text-gray-700">Profil Pribadi</h3>
                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="admin-edit-label">No. HP</label>
                                <input name="no_hp" value="{{ old('no_hp', $profile?->no_hp) }}" class="admin-edit-field">
                                @error('no_hp') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="admin-edit-label">Tanggal Lahir</label>
                                <input type="date" name="tanggal_lahir" value="{{ old('tanggal_lahir', optional($profile?->tanggal_lahir)->toDateString()) }}" class="admin-edit-field">
                                @error('tanggal_lahir') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="admin-edit-label">Jenis Kelamin</label>
                                <select name="jenis_kelamin" class="admin-edit-field">
                                    <option value="">Pilih jenis kelamin</option>
                                    <option value="L" @selected(old('jenis_kelamin', $profile?->jenis_kelamin) === 'L')>Laki-laki</option>
                                    <option value="P" @selected(old('jenis_kelamin', $profile?->jenis_kelamin) === 'P')>Perempuan</option>
                                </select>
                                @error('jenis_kelamin') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="admin-edit-label">Agama</label>
                                <select name="agama" class="admin-edit-field">
                                    <option value="">Pilih agama</option>
                                    @foreach(['Islam', 'Kristen Protestan', 'Katolik', 'Hindu', 'Buddha', 'Konghucu', 'Lainnya'] as $agama)
                                        <option value="{{ $agama }}" @selected(old('agama', $profile?->agama) === $agama)>{{ $agama }}</option>
                                    @endforeach
                                </select>
                                @error('agama') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="admin-edit-label">NIK</label>
                                <input name="nik" value="{{ old('nik', $profile?->nik) }}" class="admin-edit-field">
                                @error('nik') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                        <div class="mt-4">
                            <label class="admin-edit-label">Alamat</label>
                            <textarea name="alamat" rows="4" class="admin-edit-field">{{ old('alamat', $profile?->alamat) }}</textarea>
                            @error('alamat') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </section>

                    <section>
                        <h3 class="employee-biodata-section-title border-b border-gray-100 pb-3 text-sm font-bold text-gray-700">Data Pekerjaan</h3>
                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="admin-edit-label">NIP</label>
                                <input name="nip" value="{{ old('nip', $employeeDetail?->nip) }}" class="admin-edit-field">
                                @error('nip') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="admin-edit-label">Status Kerja</label>
                                <select name="status_kerja" class="admin-edit-field">
                                    <option value="">Pilih status</option>
                                    <option value="tetap" @selected(old('status_kerja', $employeeDetail?->status_kerja) === 'tetap')>Tetap</option>
                                    <option value="kontrak" @selected(old('status_kerja', $employeeDetail?->status_kerja) === 'kontrak')>Kontrak</option>
                                    <option value="magang" @selected(old('status_kerja', $employeeDetail?->status_kerja) === 'magang')>Magang</option>
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
                        <h3 class="employee-biodata-section-title border-b border-gray-100 pb-3 text-sm font-bold text-gray-700">Foto Profil</h3>
                        <div class="mt-4">
                            <label class="admin-edit-label">Upload Foto Baru</label>
                            <input type="file" name="foto" accept="image/png,image/jpeg" class="admin-edit-field bg-white">
                            <p class="mt-1 text-xs text-gray-500">Format JPG/PNG, maksimal 2 MB.</p>
                            @error('foto') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </section>

                    <div class="employee-biodata-actions flex flex-wrap items-center gap-2 border-t border-gray-100 pt-5">
                        <button class="rounded-md bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                            Simpan Biodata
                        </button>
                        <a href="{{ route('admin.users.show', $user->id) }}" class="employee-biodata-cancel rounded-md border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-600 transition hover:bg-gray-50">
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
