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
</style>

<div class="space-y-4">
    <a href="{{ route('admin.users.show', $user->id) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-500 transition hover:text-blue-700">
        <i class="fas fa-arrow-left text-xs"></i>
        Kembali ke Detail Pegawai
    </a>

    <section class="overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm">
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
            <a href="{{ route('admin.biodata.edit', $user) }}" class="inline-flex items-center gap-2 rounded-md bg-blue-600 px-3 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-blue-700">
                <i class="fas fa-id-card text-[10px]"></i> Edit Biodata
            </a>
        </div>

        <div class="grid xl:grid-cols-[17rem_1fr]">
            <aside class="border-b border-gray-100 bg-gray-50/60 p-5 xl:border-b-0 xl:border-r">
                <div class="rounded-md border border-gray-200 bg-white p-4 shadow-sm">
                    <div class="flex aspect-square items-center justify-center rounded-md bg-blue-50 text-5xl font-bold text-blue-700">
                        {{ strtoupper(substr($user->name, 0, 1)) }}
                    </div>
                    <div class="mt-4 text-center">
                        <h2 class="text-base font-bold text-gray-800">{{ $user->name }}</h2>
                        <p class="mt-1 text-sm text-gray-500">{{ $user->email }}</p>
                    </div>
                </div>
            </aside>

            <main class="p-5 lg:p-6">
                <form method="POST" action="{{ route('admin.users.update', $user->id) }}" class="space-y-6">
                    @csrf
                    <section>
                        <h3 class="border-b border-gray-100 pb-3 text-sm font-bold text-gray-700">Informasi Akun</h3>
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

                    <div class="flex flex-wrap items-center gap-2 border-t border-gray-100 pt-5">
                        <button class="rounded-md bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                            Simpan Akun
                        </button>
                        <a href="{{ route('admin.users.show', $user->id) }}" class="rounded-md border border-gray-300 px-5 py-2.5 text-sm font-semibold text-gray-600 transition hover:bg-gray-50">
                            Batal
                        </a>
                    </div>
                </form>
            </main>
        </div>
    </section>
</div>
@endsection
