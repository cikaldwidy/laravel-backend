@extends('layouts.admin')

@section('title','Biodata User')

@section('content')
<div class="bg-white p-6 rounded-xl shadow space-y-4">
    <div class="flex justify-between items-center">
        <div>
            <h2 class="font-bold text-lg">Biodata User</h2>
            <p class="text-sm text-gray-500">Admin mengelola biodata (create/edit/hapus).</p>
        </div>
    </div>
    <table class="w-full border text-sm">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2 text-left">Nama</th>
                <th class="p-2 text-left">Email</th>
                <th class="p-2 text-left">Role</th>
                <th class="p-2 text-left">Status</th>
                <th class="p-2 text-left">Aksi</th>
            </tr>
        </thead>

        <tbody>
            @foreach($users as $u)
                @php
                    $hasProfile = isset($profiles[$u->id]);
                    $hasDetail = isset($details[$u->id]);
                    $status = ($hasProfile && $hasDetail) ? 'Lengkap' : (($hasProfile || $hasDetail) ? 'Sebagian' : 'Belum ada');
                    $badge = ($hasProfile && $hasDetail) ? 'bg-green-100 text-green-700' : (($hasProfile || $hasDetail) ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-600');
                @endphp
                <tr class="border-t">
                    <td class="p-2">{{ $u->name }}</td>
                    <td class="p-2">{{ $u->email }}</td>
                    <td class="p-2">{{ $u->role }}</td>
                    <td class="p-2">
                        <span class="px-2 py-1 rounded text-xs font-semibold {{ $badge }}">{{ $status }}</span>
                    </td>
                    <td class="p-2 space-x-2">
                        <a href="{{ route('admin.biodata.edit', $u) }}" class="text-blue-600 hover:underline">
                            {{ ($hasProfile || $hasDetail) ? 'Edit' : 'Buat' }}
                        </a>

                        <form method="POST" action="{{ route('admin.biodata.destroy', $u) }}" class="inline" onsubmit="return confirm('Hapus biodata user ini?')">
                            @csrf
                            <button class="text-red-600 hover:underline" {{ (!$hasProfile && !$hasDetail) ? 'disabled' : '' }}>
                                Hapus
                            </button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
