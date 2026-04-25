@extends('layouts.admin')

@section('title','Tambah User')

@section('content')

<div class="bg-white p-6 rounded-xl shadow w-96">

<form method="POST" action="/admin/users">
    @csrf

    <input name="name" placeholder="Nama" class="w-full p-2 border mb-2">
    <input name="email" placeholder="Email" class="w-full p-2 border mb-2">
    <input type="password" name="password" placeholder="Password" class="w-full p-2 border mb-2">

    <select name="role" class="w-full p-2 border mb-2">
        <option value="user">User</option>
        <option value="admin">Admin</option>
    </select>

    <button class="bg-green-500 text-white w-full p-2">Simpan</button>
</form>

</div>

@endsection