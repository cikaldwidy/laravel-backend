@extends('layouts.admin')

@section('title', 'Tambah Admin')

@section('content')
<div class="bg-white p-6 rounded-xl shadow max-w-xl mx-auto">
    <div class="flex items-start justify-between gap-3 mb-5">
        <div>
            <h2 class="font-bold text-lg text-gray-800">Tambah Admin</h2>
            <p class="text-sm text-gray-500">Buat akses login khusus administrator.</p>
        </div>
        <a href="{{ route('admin.settings.admin_accounts.index') }}" class="px-4 py-2 rounded border text-sm font-semibold hover:bg-gray-50">Kembali</a>
    </div>

    <form method="POST" action="{{ route('admin.settings.admin_accounts.store') }}" class="space-y-4">
        @csrf
        <div>
            <label class="text-sm font-semibold text-gray-700">Nama</label>
            <input name="name" value="{{ old('name') }}" class="w-full p-2 border rounded mt-1">
            @error('name') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="text-sm font-semibold text-gray-700">Email</label>
            <input type="email" name="email" value="{{ old('email') }}" class="w-full p-2 border rounded mt-1">
            @error('email') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>
        <div>
            <label class="text-sm font-semibold text-gray-700">Password</label>
            <input type="password" name="password" class="w-full p-2 border rounded mt-1">
            @error('password') <p class="text-xs text-red-600 mt-1">{{ $message }}</p> @enderror
        </div>

        <button class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded font-semibold">Simpan</button>
    </form>
</div>
@endsection
