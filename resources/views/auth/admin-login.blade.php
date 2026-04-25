@extends('layouts.app')

@section('content')
<div class="bg-white p-8 rounded-xl shadow w-96">

    <h2 class="text-xl font-bold text-center mb-4 text-red-500">
        Login Admin
    </h2>

    @if($errors->any())
        <div class="bg-red-200 p-2 mb-2">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="/admin/login">
        @csrf

        <input name="email" placeholder="Email"
            class="w-full p-2 border mb-2">

        <input type="password" name="password" placeholder="Password"
            class="w-full p-2 border mb-2">

        <button class="bg-red-500 text-white w-full p-2">
            Login Admin
        </button>
    </form>

    <p class="text-center mt-3 text-sm">
        Login sebagai user?
        <a href="/" class="text-blue-500">Kembali</a>
    </p>

</div>
@endsection