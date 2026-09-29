@extends('layouts.app')
@section('title', 'Daftar cabang')
@section('content')
<div class="card">
    <h1>Daftar cabang</h1>
    @error('cabang')<p class="err">{{ $message }}</p>@enderror
    <p><a class="btn" href="{{ route('branches.create') }}">Tambah cabang</a></p>
</div>
<div class="card">
    <table>
        <thead><tr><th>Kode</th><th>Nama cabang</th><th>Kota</th><th>Aksi</th></tr></thead>
        <tbody>
        @forelse ($branches as $branch)
            <tr>
                <td>{{ $branch->kode }}</td>
                <td>{{ $branch->nama }}</td>
                <td>{{ $branch->kota }}</td>
                <td>
                    <div class="branch-actions">
                        <a class="btn branch-edit" href="{{ route('branches.edit', $branch) }}">Ubah</a>
                        <form method="POST" action="{{ route('branches.destroy', $branch) }}" onsubmit="return confirm('Hapus cabang ini?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn branch-delete" type="submit">Hapus</button>
                        </form>
                    </div>
                </td>
            </tr>
        @empty
            <tr><td colspan="4" class="muted">Belum ada cabang.</td></tr>
        @endforelse
        </tbody>
    </table>
    <p>{{ $branches->links() }}</p>
</div>
@endsection