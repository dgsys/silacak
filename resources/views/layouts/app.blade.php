<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SiLacak') - SiLacak</title>
    @vite('resources/css/app.css')
</head>
<body>
<nav class="nav">
    <a class="brand" href="{{ route('lacak') }}">SiLacak</a>
    <a href="{{ route('lacak') }}">Lacak resi</a>
    <a href="{{ route('ongkir') }}">Cek ongkir</a>
    @auth
        <a href="{{ route('shipments.index') }}">Paket</a>
        @if (auth()->user()->isAdmin())
            <a href="{{ route('branches.index') }}">Cabang</a>
        @endif
        <span class="muted">{{ auth()->user()->nama }}</span>
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn secondary" type="submit">Keluar</button>
        </form>
    @else
        <a href="{{ route('login') }}">Masuk petugas</a>
    @endauth
</nav>
<main class="wrap">
    @if (session('ok'))
        <div class="flash">{{ session('ok') }}</div>
    @endif
    @yield('content')
</main>
<script src="{{ asset('js/customer-search.js') }}" defer></script>
</body>
</html>
