@extends('layouts.app')
@section('title', 'Daftar paket')
@section('content')
<div class="card">
    <h1>Daftar paket</h1>
    <form method="GET" action="{{ route('shipments.index') }}" class="inline">
        <input type="text" name="q" value="{{ $q }}" placeholder="Awalan nomor resi" maxlength="20">
        <button class="btn secondary" type="submit">Cari</button>
        @can('create', App\Models\Shipment::class)
            <a class="btn" href="{{ route('shipments.create') }}">Paket baru</a>
        @endcan
    </form>
    @error('q')<p class="err">{{ $message }}</p>@enderror
</div>
<div class="card">
    <table>
        <thead><tr><th>Resi</th><th>Layanan</th><th>Rute</th><th>Ongkir</th><th>Status</th></tr></thead>
        <tbody>
        @forelse ($shipments as $s)
            <tr>
                <td><a href="{{ route('shipments.show', $s) }}">{{ $s->resi }}</a></td>
                <td>{{ $s->service->nama }}</td>
                <td>{{ $s->originBranch->kota }} &rarr; {{ $s->destBranch->kota }}</td>
                <td>Rp{{ number_format($s->ongkir, 0, ',', '.') }}</td>
                <td><span class="badge">{{ $s->status_terakhir->label() }}</span></td>
            </tr>
        @empty
            <tr><td colspan="5" class="muted">Belum ada paket.</td></tr>
        @endforelse
        </tbody>
    </table>
    <p>{{ $shipments->links() }}</p>
</div>
@endsection
