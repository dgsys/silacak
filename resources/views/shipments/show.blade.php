@extends('layouts.app')
@section('title', $shipment->resi)
@section('content')
<div class="card">
    <h1>{{ $shipment->resi }} <span class="badge">{{ $shipment->status_terakhir->label() }}</span></h1>
    <table>
        <tr><td>Layanan</td><td>{{ $shipment->service->nama }}</td></tr>
        <tr><td>Pengirim</td><td>{{ $shipment->customer->nama }} ({{ $shipment->customer->telepon }}){{ $shipment->customer->is_member ? ' - member' : '' }}</td></tr>
        <tr><td>Penerima</td><td>{{ $shipment->penerima_nama }}, {{ $shipment->penerima_alamat }}</td></tr>
        <tr><td>Rute</td><td>{{ $shipment->originBranch->nama }} &rarr; {{ $shipment->destBranch->nama }}</td></tr>
        <tr><td>Berat</td><td>{{ $shipment->berat_aktual }} kg aktual, {{ $shipment->berat_tagih }} kg ditagih</td></tr>
        <tr><td>Nilai barang</td><td>Rp{{ number_format($shipment->nilai_barang, 0, ',', '.') }}</td></tr>
        <tr><td>Biaya dasar</td><td>Rp{{ number_format($biaya['biaya_dasar'], 0, ',', '.') }}</td></tr>
        <tr><td>Diskon member (10%)</td><td>-Rp{{ number_format($biaya['diskon'], 0, ',', '.') }}</td></tr>
        <tr><td>Asuransi (0,2%)</td><td>Rp{{ number_format($biaya['asuransi'], 0, ',', '.') }}</td></tr>
        <tr><td>Total ongkir</td><td><strong>Rp{{ number_format($biaya['total'], 0, ',', '.') }}</strong></td></tr>
    </table>
    <p><a class="btn" href="{{ route('shipments.label', $shipment) }}" target="_blank" rel="noopener">Cetak label (PDF)</a></p>
</div>

<div class="card">
    <h2>Perbarui status</h2>
    <form method="POST" action="{{ route('shipments.status', $shipment) }}">
        @csrf
        <div class="row">
            <div>
                <label for="status">Status baru</label>
                <select id="status" name="status" required>
                    @foreach ($statuses as $value => $label)
                        <option value="{{ $value }}" @selected(old('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('status')<p class="err">{{ $message }}</p>@enderror
            </div>
            @if (auth()->user()->isAdmin())
                <div>
                    <label for="branch_id">Lokasi (cabang)</label>
                    <select id="branch_id" name="branch_id" required>
                        @foreach ($branches as $b)
                            <option value="{{ $b->id }}">{{ $b->nama }}</option>
                        @endforeach
                    </select>
                    @error('branch_id')<p class="err">{{ $message }}</p>@enderror
                </div>
            @endif
        </div>
        <label for="catatan">Catatan (opsional)</label>
        <input id="catatan" name="catatan" value="{{ old('catatan') }}" maxlength="255">
        @error('catatan')<p class="err">{{ $message }}</p>@enderror
        <p><button class="btn" type="submit">Simpan status</button></p>
    </form>
</div>

<div class="card">
    <h2>Riwayat</h2>
    <ul class="timeline">
        @foreach ($shipment->events as $e)
            <li>
                <strong>{{ $e->status->label() }}</strong><br>
                <span class="muted">{{ $e->waktu->format('d/m/Y H:i') }} &middot; {{ $e->branch->nama }}</span>
                @if ($e->catatan)<br>{{ $e->catatan }}@endif
            </li>
        @endforeach
    </ul>
</div>
@endsection
