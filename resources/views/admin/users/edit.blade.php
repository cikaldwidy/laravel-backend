@extends('layouts.admin')

@section('title', 'Edit Akun')

@section('content')
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

    html[data-admin-theme="dark"] main .employee-account-edit-page .admin-edit-field {
        background: #0b1728 !important;
        border-color: var(--admin-border) !important;
        color: var(--admin-ink) !important;
        box-shadow: inset 0 1px 0 rgba(255,255,255,.025);
    }

    html[data-admin-theme="dark"] main .employee-account-edit-page .admin-edit-field:focus {
        background: #0b1728 !important;
        border-color: var(--admin-blue) !important;
        box-shadow: 0 0 0 3px rgba(96, 165, 250, .14) !important;
    }

    html[data-admin-theme="dark"] main .employee-account-edit-page .admin-edit-field::placeholder {
        color: #64748b !important;
    }

    html[data-admin-theme="dark"] main .employee-account-edit-page .admin-edit-label {
        color: #8fa3bf !important;
    }

    html[data-admin-theme="dark"] main .employee-account-edit-shell {
        background: #111f33 !important;
        border-color: var(--admin-border) !important;
    }

    html[data-admin-theme="dark"] main .employee-account-edit-content {
        background: #111f33 !important;
    }

    html[data-admin-theme="dark"] main .employee-account-section-title,
    html[data-admin-theme="dark"] main .employee-account-actions {
        border-color: var(--admin-border) !important;
        color: var(--admin-ink) !important;
    }

    html[data-admin-theme="dark"] main .employee-account-cancel {
        background: #0b1728 !important;
        border-color: var(--admin-border) !important;
        color: #cbd5e1 !important;
    }

    html[data-admin-theme="dark"] main .employee-account-cancel:hover {
        background: rgba(96, 165, 250, .12) !important;
        color: var(--admin-ink) !important;
    }
</style>

<div class="employee-account-edit-page space-y-4">
    <a href="{{ route('admin.users.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-500 transition hover:text-blue-700">
        <i class="fas fa-arrow-left text-xs"></i>
        Kembali ke Akun Pegawai
    </a>

    <section class="employee-account-edit-shell overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm">
        <div class="flex flex-col gap-3 border-b border-gray-100 px-5 py-4 lg:flex-row lg:items-center lg:justify-between">
            <div class="flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-md bg-blue-50 text-blue-700">
                    <i class="fas fa-user-pen"></i>
                </div>
                <div>
                    <h1 class="text-lg font-bold text-gray-800">Edit Akun Pegawai</h1>
                    <p class="text-xs text-gray-500">Perbarui nama, username, email, dan password akun.</p>
                </div>
            </div>
        
        </div>

        <div>
            <main class="employee-account-edit-content p-5 lg:p-6">
                <form method="POST" action="{{ route('admin.users.update', $user->id) }}" class="space-y-6">
                    @csrf
                    <section>
                        <h3 class="employee-account-section-title border-b border-gray-100 pb-3 text-sm font-bold text-gray-700">Informasi Akun</h3>
                        <div class="mt-4 grid gap-4 md:grid-cols-2">
                            <div>
                                <label class="admin-edit-label">Nama</label>
                                <input name="name" value="{{ old('name', $user->name) }}" class="admin-edit-field">
                                @error('name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="admin-edit-label">Username</label>
                                <input name="username" value="{{ old('username', $user->username) }}" class="admin-edit-field" placeholder="Contoh: budi_santoso">
                                @error('username') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="admin-edit-label">Email</label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}" class="admin-edit-field">
                                @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="admin-edit-label">Password Baru</label>
                                <input type="password" name="password" placeholder="Kosongkan jika tidak diubah" class="admin-edit-field">
                                @error('password') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </section>

                    <div class="employee-account-actions flex flex-wrap items-center gap-2 border-t border-gray-100 pt-5">
                        <button class="rounded-md bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                            Simpan Akun
                        </button>
                        <a href="{{ route('admin.users.show', $user->id) }}" class="employee-account-cancel rounded-md border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-600 transition hover:bg-gray-50">
                            Batal
                        </a>
                    </div>
                </form>
            </main>
        </div>
    </section>
</div>
@endsection
