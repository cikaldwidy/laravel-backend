@extends('layouts.admin')

@section('title','Kelola User')

@section('content')

<div class="bg-white p-6 rounded-xl shadow">

    <div class="flex justify-between mb-4">
        <h2 class="font-bold text-lg">Data User</h2>
        <a href="/admin/users/create" class="bg-blue-500 text-white px-4 py-2 rounded">
            + Tambah
        </a>
    </div>

    @if(session('success'))
        <div class="bg-green-200 p-2 mb-3">{{ session('success') }}</div>
    @endif

    <table class="w-full border text-sm">
        <thead class="bg-gray-100">
            <tr>
                <th class="p-2">Nama</th>
                <th>Email</th>
                <th>Role</th>
                <th>Aksi</th>
            </tr>
        </thead>

        <tbody>
            @foreach($users as $u)
            <tr class="border-t text-center">
                <td>{{ $u->name }}</td>
                <td>{{ $u->email }}</td>
                <td>{{ $u->role }}</td>
                <td class="space-x-2">
                    <a href="/admin/users/{{ $u->id }}/edit" class="text-blue-500">Edit</a>
                    <a href="{{ route('admin.biodata.edit', $u) }}" class="text-emerald-600">Biodata</a>

                    <form method="POST" action="/admin/users/{{ $u->id }}/delete" class="inline">
                        @csrf
                        <button class="text-red-500">Hapus</button>
                    </form>
                </td>
            </tr>
            @endforeach
        </tbody>

    </table>

</div>

@endsection
