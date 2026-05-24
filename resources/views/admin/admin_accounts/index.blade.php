@extends('layouts.admin')

@section('title', 'Akun Admin')

@section('content')
<div class="space-y-5">
    <div class="bg-white border border-white/70 p-6">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            <div>
                <h1 class="text-xl font-extrabold text-slate-950">Akun Admin</h1>
                <p class="text-sm text-slate-500 mt-1">Kelola akun administrator terpisah dari akun pegawai.</p>
            </div>
            <a href="{{ route('admin.settings.admin_accounts.create') }}" class="inline-flex items-center gap-2 bg-blue-600 text-white px-4 py-2 rounded font-semibold text-sm">
                <i class="fa-solid fa-plus"></i>
                Tambah Admin
            </a>
        </div>
    </div>

    <div class="bg-white border border-white/70 p-6">
        <form method="GET" data-auto-filter class="mb-5">
            <label class="block text-xs font-semibold text-gray-500 mb-2">Cari Admin</label>
            <div class="relative max-w-md">
                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="search" name="search" value="{{ request('search') }}" placeholder="Nama atau email admin..." class="w-full border border-gray-200 rounded-md pl-8 pr-3 py-2 text-sm focus:outline-none focus:border-gray-500 transition text-gray-700">
            </div>
        </form>

        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="p-3 text-left">Admin</th>
                        <th class="p-3 text-left">Role</th>
                        <th class="p-3 text-left">Dibuat</th>
                        <th class="p-3 text-left">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
                    @forelse($admins as $admin)
                        <tr>
                            <td class="p-3">
                                <div class="font-semibold text-gray-800">{{ $admin->name }}</div>
                                <div class="text-xs text-gray-500">{{ $admin->email }}</div>
                            </td>
                            <td class="p-3">
                                <span class="px-2 py-1 rounded text-xs font-semibold bg-slate-100 text-slate-700">Admin</span>
                            </td>
                            <td class="p-3 text-gray-500">{{ $admin->created_at?->format('d/m/Y') ?? '-' }}</td>
                            <td class="p-3">
                                <div class="flex flex-wrap gap-3">
                                    <a href="{{ route('admin.settings.admin_accounts.edit', $admin) }}" class="text-blue-600 font-semibold">Edit</a>
                                    <form method="POST" action="{{ route('admin.settings.admin_accounts.destroy', $admin) }}" data-confirm-form data-confirm-title="Hapus akun admin?" data-confirm-message="Akun admin ini akan dihapus dari sistem." data-confirm-button="Hapus">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-red-600 font-semibold" @disabled($admin->id === auth()->id())>Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-6 text-center text-gray-500">Belum ada akun admin.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
