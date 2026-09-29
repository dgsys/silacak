@extends('layouts.app')
@section('title', 'Masuk')
@section('content')
<div class="card">
    <h1>Masuk petugas</h1>
    <form method="POST" action="{{ route('login.attempt') }}">
        @csrf
        <label for="email">Email</label>
        <input id="email" type="email" name="email" value="{{ old('email') }}" autocomplete="username" required autofocus>
        @error('email')<p class="err">{{ $message }}</p>@enderror

        <label for="password">Password</label>
        <input id="password" type="password" name="password" autocomplete="current-password" required>
        @error('password')<p class="err">{{ $message }}</p>@enderror

        <p><button class="btn" type="submit">Masuk</button></p>
    </form>
</div>
@endsection
