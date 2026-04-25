@extends('layouts.admin')

@section('title','Edit User')

@section('content')

<div class="bg-white p-6 rounded-xl shadow w-96">

<form method="POST" action="/admin/users/{{ $user->id }}/update">
    @csrf

    <input name="name" value="{{ $user->name }}" class="w-full p-2 border mb-2">
    <input name="email" value="{{ $user->email }}" class="w-full p-2 border mb-2">

    <input type="password" name="password" placeholder="Kosongkan jika tidak diubah"
        class="w-full p-2 border mb-2">

    <select name="role" class="w-full p-2 border mb-2">
        <option value="user" {{ $user->role=='user'?'selected':'' }}>User</option>
        <option value="admin" {{ $user->role=='admin'?'selected':'' }}>Admin</option>
    </select>

    <button class="bg-blue-500 text-white w-full p-2">Update</button>
</form>

</div>

@endsection