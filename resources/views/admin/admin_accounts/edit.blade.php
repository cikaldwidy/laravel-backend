@extends('layouts.admin')

@section('title', 'Edit Admin')

@section('content')
<div class="space-y-6">
    <div>
        <a href="{{ route('admin.settings.admin_accounts.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-500 transition hover:text-blue-600">
            <i class="fa-solid fa-arrow-left text-xs"></i>
            Kembali ke Akun Admin
        </a>
        <div class="mt-3">
            <h1 class="text-2xl font-bold text-gray-900">Edit Admin</h1>
            <p class="mt-1 text-sm text-gray-500">{{ $admin->email }}</p>
        </div>
    </div>

    <section class="overflow-hidden rounded-md border border-gray-200 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-6 py-5">
            <h2 class="text-lg font-bold text-gray-900">Informasi Akun</h2>
        </div>
        <form method="POST" action="{{ route('admin.settings.admin_accounts.update', $admin) }}" class="space-y-5 p-6">
            @csrf
            @method('PUT')
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">Nama</label>
                    <input name="name" value="{{ old('name', $admin->name) }}" class="h-11 w-full rounded-md border border-gray-200 px-3 text-sm text-gray-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    @error('name') <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">Username</label>
                    <input name="username" value="{{ old('username', $admin->username) }}" class="h-11 w-full rounded-md border border-gray-200 px-3 text-sm text-gray-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    @error('username') <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">Email</label>
                    <input type="email" name="email" value="{{ old('email', $admin->email) }}" class="h-11 w-full rounded-md border border-gray-200 px-3 text-sm text-gray-700 outline-none transition focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    @error('email') <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="mb-2 block text-sm font-semibold text-gray-700">Password Baru</label>
                    <input type="password" name="password" placeholder="Kosongkan jika tidak diubah" class="h-11 w-full rounded-md border border-gray-200 px-3 text-sm text-gray-700 outline-none transition placeholder:text-gray-400 focus:border-blue-500 focus:ring-4 focus:ring-blue-100">
                    @error('password') <p class="mt-1 text-xs font-semibold text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="flex flex-col-reverse gap-2 border-t border-gray-100 pt-5 sm:flex-row sm:justify-end">
                <a href="{{ route('admin.settings.admin_accounts.index') }}" class="inline-flex h-11 items-center justify-center rounded-md border border-gray-200 bg-white px-5 text-sm font-semibold text-gray-600 transition hover:bg-gray-50">Batal</a>
                <button class="inline-flex h-11 items-center justify-center gap-2 rounded-md bg-blue-600 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-blue-700">
                    <i class="fas fa-save text-xs"></i>
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </section>
</div>
@endsection
