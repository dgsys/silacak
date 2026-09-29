@extends('layouts.app')
@section('title', isset($branch) ? 'Ubah cabang' : 'Tambah cabang')
@section('content')
<div class="card">
    <h1>{{ isset($branch) ? 'Ubah cabang' : 'Tambah cabang' }}</h1>
    <form method="POST" action="{{ isset($branch) ? route('branches.update', $branch) : route('branches.store') }}">
        @csrf
        @if (isset($branch))
            @method('PUT')
        @endif
        <label for="kode">Kode cabang</label>
        <input id="kode" name="kode" value="{{ old('kode', $branch->kode ?? '') }}" maxlength="10" required>
        @error('kode')<p class="err">{{ $message }}</p>@enderror

        <label for="nama">Nama cabang</label>
        <input id="nama" name="nama" value="{{ old('nama', $branch->nama ?? '') }}" maxlength="100" required>
        @error('nama')<p class="err">{{ $message }}</p>@enderror

        <label for="kota">Kota</label>
        <input id="kota" name="kota" value="{{ old('kota', $branch->kota ?? '') }}" maxlength="100" required>
        @error('kota')<p class="err">{{ $message }}</p>@enderror

        <p>
            <button class="btn" type="submit">Simpan</button>
            <a class="btn secondary" href="{{ route('branches.index') }}">Batal</a>
        </p>
    </form>
</div>
@endsection