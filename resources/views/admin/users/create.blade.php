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
    .password-generator-box {
        border: 1px solid #dbeafe;
        border-radius: 0.875rem;
        background: #eff6ff;
        padding: 0.75rem;
    }
    .password-generator-field {
        width: 100%;
        border: 1px solid #bfdbfe;
        border-radius: 0.75rem;
        background: #ffffff;
        padding: 0.75rem 0.875rem;
        font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace;
        font-size: 0.875rem;
        font-weight: 800;
        letter-spacing: .04em;
        color: #0f172a;
        outline: none;
    }
    .password-generator-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: .375rem;
        border-radius: .75rem;
        padding: .625rem .875rem;
        font-size: .75rem;
        font-weight: 800;
        transition: 150ms ease;
        white-space: nowrap;
    }
    html[data-admin-theme="dark"] main .password-generator-box {
        background: rgba(37, 99, 235, .12) !important;
        border-color: var(--admin-border) !important;
    }
    html[data-admin-theme="dark"] main .password-generator-field {
        background: #0b1728 !important;
        border-color: var(--admin-border) !important;
        color: var(--admin-ink) !important;
    }
    html[data-admin-theme="dark"] main .employee-create-page .admin-edit-field {
        background: #0b1728 !important;
        border-color: var(--admin-border) !important;
        color: var(--admin-ink) !important;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.025);
    }
    html[data-admin-theme="dark"] main .employee-create-page .admin-edit-field:focus {
        background: #0b1728 !important;
        border-color: var(--admin-blue) !important;
        box-shadow: 0 0 0 3px rgba(96, 165, 250, .14) !important;
    }
    html[data-admin-theme="dark"] main .employee-create-page .admin-edit-field::placeholder {
        color: #64748b !important;
    }
    html[data-admin-theme="dark"] main .employee-create-page .admin-edit-label {
        color: #8fa3bf !important;
    }
    html[data-admin-theme="dark"] main .employee-create-shell,
    html[data-admin-theme="dark"] main .employee-create-card {
        background: #111f33 !important;
        border-color: var(--admin-border) !important;
    }
    html[data-admin-theme="dark"] main .employee-create-sidebar {
        background: #0b1728 !important;
        border-color: var(--admin-border) !important;
    }
    html[data-admin-theme="dark"] main .employee-create-content {
        background: #111f33 !important;
    }
    html[data-admin-theme="dark"] main .employee-create-section-title,
    html[data-admin-theme="dark"] main .employee-create-actions {
        border-color: var(--admin-border) !important;
        color: var(--admin-ink) !important;
    }
    html[data-admin-theme="dark"] main .employee-create-cancel {
        background: #0b1728 !important;
        border-color: var(--admin-border) !important;
        color: #cbd5e1 !important;
    }
    html[data-admin-theme="dark"] main .employee-create-cancel:hover {
        background: rgba(96, 165, 250, .12) !important;
        color: var(--admin-ink) !important;
    }
    html[data-admin-theme="dark"] main .password-generator-action {
        border-color: var(--admin-border) !important;
    }
    html[data-admin-theme="dark"] main #generatePasswordButton {
        background: rgba(59, 130, 246, .14) !important;
        color: #93c5fd !important;
    }
    html[data-admin-theme="dark"] main #generatePasswordButton:hover {
        background: rgba(59, 130, 246, .22) !important;
    }
    html[data-admin-theme="dark"] main #copyPasswordButton {
        background: rgba(34, 197, 94, .14) !important;
        color: #4ade80 !important;
        border-color: rgba(74, 222, 128, .35) !important;
    }
    html[data-admin-theme="dark"] main #copyPasswordButton:hover {
        background: rgba(34, 197, 94, .22) !important;
    }
</style>

<div class="employee-create-page space-y-4">
    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-500 transition hover:text-blue-700">
        <i class="fas fa-arrow-left text-xs"></i>
        Kembali ke Akun Pegawai
    </a>

    <section class="employee-create-shell overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm">
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
            <aside class="employee-create-sidebar border-b border-gray-100 bg-gray-50/60 p-5 xl:border-b-0 xl:border-r">
                <div class="employee-create-card rounded-md border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="flex aspect-square items-center justify-center rounded-md bg-blue-50 text-5xl font-bold text-blue-700">
                        <i class="fa-regular fa-user"></i>
                    </div>
                    <div class="mt-4 text-center">
                        <h2 class="text-base font-bold text-gray-800">Pegawai Baru</h2>
                        <p class="mt-1 text-sm text-gray-500">Data akan dibuat setelah disimpan.</p>
                    </div>
                </div>
                <div class="employee-create-card mt-4 rounded-md border border-gray-200 bg-white p-4 shadow-sm">
                    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500">Foto Profil</p>
                    <p class="mt-2 text-xs leading-5 text-gray-500">Upload foto di bagian bawah form. Format JPG/PNG, maksimal 2 MB.</p>
                </div>
            </aside>

            <main class="employee-create-content p-5 lg:p-6">
                <form method="POST" action="{{ route('admin.users.store') }}" enctype="multipart/form-data" class="space-y-8">
                    @csrf

                    <section>
                        <h3 class="employee-create-section-title border-b border-gray-100 pb-3 text-sm font-bold text-gray-700">Data Akun</h3>
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
                                <div class="mb-1.5 flex items-center justify-between gap-2">
                                    <label class="admin-edit-label mb-0">Password Awal</label>
                                    <span class="rounded-full bg-blue-50 px-2.5 py-1 text-[11px] font-bold text-blue-700">Auto hash saat disimpan</span>
                                </div>
                                <div class="password-generator-box">
                                    <input
                                        id="generatedPassword"
                                        type="text"
                                        name="password"
                                        value="{{ old('password') }}"
                                        class="password-generator-field"
                                        autocomplete="new-password"
                                        readonly
                                    >
                                    <div class="mt-2 flex flex-wrap gap-2">
                                        <button type="button" id="generatePasswordButton" class="password-generator-action border border-blue-200 bg-white text-blue-700 hover:bg-blue-50">
                                            <i class="fas fa-rotate text-[10px]"></i>
                                            Generate Ulang
                                        </button>
                                        <button type="button" id="copyPasswordButton" class="password-generator-action border border-emerald-200 bg-emerald-50 text-emerald-700 hover:bg-emerald-100">
                                            <i class="fas fa-copy text-[10px]"></i>
                                            Salin
                                        </button>
                                    </div>
                                    <p id="passwordGeneratorHint" class="mt-2 text-xs leading-5 text-gray-500">Berikan password awal ini ke pegawai. Pegawai bisa mengganti password setelah login.</p>
                                </div>
                                @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </section>

                    <section>
                        <h3 class="employee-create-section-title border-b border-gray-100 pb-3 text-sm font-bold text-gray-700">Profil Pribadi</h3>
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
                        <h3 class="employee-create-section-title border-b border-gray-100 pb-3 text-sm font-bold text-gray-700">Data Pekerjaan</h3>
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
                                    <option value="capeg" @selected(old('status_kerja') === 'capeg')>Capeg</option>
                                    <option value="training" @selected(old('status_kerja') === 'training')>Training</option>
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
                        <h3 class="employee-create-section-title border-b border-gray-100 pb-3 text-sm font-bold text-gray-700">Foto Profil</h3>
                        <div class="mt-4">
                            <label class="admin-edit-label">Upload Foto</label>
                            <input type="file" name="foto" accept="image/png,image/jpeg" class="admin-edit-field bg-white">
                            <p class="mt-1 text-xs text-gray-500">Format JPG/PNG, maksimal 2 MB.</p>
                            @error('foto') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </section>

                    <div class="employee-create-actions flex flex-wrap items-center gap-2 border-t border-gray-100 pt-5">
                        <button class="rounded-md bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                            Simpan Akun Pegawai
                        </button>
                        <a href="{{ route('admin.users.index') }}" class="employee-create-cancel rounded-md border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-600 transition hover:bg-gray-50">
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
const generatedPasswordInput = document.getElementById('generatedPassword');
const generatePasswordButton = document.getElementById('generatePasswordButton');
const copyPasswordButton = document.getElementById('copyPasswordButton');
const passwordGeneratorHint = document.getElementById('passwordGeneratorHint');

function generatePassword(length = 12) {
    const alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz23456789!@#$%';
    const required = ['ABCDEFGHJKLMNPQRSTUVWXYZ', 'abcdefghijkmnopqrstuvwxyz', '23456789', '!@#$%'];
    const randomValues = new Uint32Array(length);
    window.crypto.getRandomValues(randomValues);

    const chars = required.map((group, index) => group[randomValues[index] % group.length]);

    for (let index = chars.length; index < length; index++) {
        chars.push(alphabet[randomValues[index] % alphabet.length]);
    }

    for (let index = chars.length - 1; index > 0; index--) {
        const swapIndex = randomValues[index] % (index + 1);
        [chars[index], chars[swapIndex]] = [chars[swapIndex], chars[index]];
    }

    return chars.join('');
}

function setGeneratedPassword() {
    if (!generatedPasswordInput) return;
    generatedPasswordInput.value = generatePassword();
    if (passwordGeneratorHint) {
        passwordGeneratorHint.textContent = 'Password baru dibuat otomatis. Salin untuk diberikan ke pegawai.';
    }
}

generatePasswordButton?.addEventListener('click', setGeneratedPassword);
copyPasswordButton?.addEventListener('click', async () => {
    if (!generatedPasswordInput?.value) return;

    try {
        await navigator.clipboard.writeText(generatedPasswordInput.value);
        if (passwordGeneratorHint) {
            passwordGeneratorHint.textContent = 'Password berhasil disalin.';
        }
    } catch (error) {
        generatedPasswordInput.focus();
        generatedPasswordInput.select();
        if (passwordGeneratorHint) {
            passwordGeneratorHint.textContent = 'Clipboard diblokir browser. Password sudah dipilih, salin manual.';
        }
    }
});

if (generatedPasswordInput && !generatedPasswordInput.value) {
    setGeneratedPassword();
}

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
