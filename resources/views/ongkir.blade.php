@extends('layouts.app')
@section('title', 'Cek ongkir')
@section('content')
<div class="card">
    <h1>Estimasi ongkos kirim</h1>
    <form method="POST" action="{{ route('ongkir.hitung') }}">
        @csrf
        <label for="service_id">Layanan</label>
        <select id="service_id" name="service_id" required>
            @foreach ($services as $s)
                <option value="{{ $s->id }}" @selected(old('service_id') == $s->id)>
                    {{ $s->nama }} - Rp{{ number_format($s->tarif_per_kg, 0, ',', '.') }}/kg (min. {{ $s->min_kg }} kg)
                </option>
            @endforeach
        </select>
        @error('service_id')<p class="err">{{ $message }}</p>@enderror

        <div class="row">
            <div>
                <label for="berat_aktual">Berat aktual (kg)</label>
                <input id="berat_aktual" type="number" step="0.01" min="0.01" name="berat_aktual" value="{{ old('berat_aktual') }}" required>
                @error('berat_aktual')<p class="err">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="nilai_barang">Nilai barang (Rp)</label>
                <input id="nilai_barang" type="number" min="0" name="nilai_barang" value="{{ old('nilai_barang', 0) }}">
                @error('nilai_barang')<p class="err">{{ $message }}</p>@enderror
            </div>
        </div>

        <label class="check-row" for="member">
            <input id="member" type="checkbox" name="member" value="1" @checked(old('member'))>
            <span>Hitung diskon member 10%</span>
        </label>

        <div class="row">
            <div><label for="panjang">Panjang (cm)</label><input id="panjang" type="number" min="0" name="panjang" value="{{ old('panjang', 0) }}"></div>
            <div><label for="lebar">Lebar (cm)</label><input id="lebar" type="number" min="0" name="lebar" value="{{ old('lebar', 0) }}"></div>
            <div><label for="tinggi">Tinggi (cm)</label><input id="tinggi" type="number" min="0" name="tinggi" value="{{ old('tinggi', 0) }}"></div>
        </div>
        @foreach (['panjang', 'lebar', 'tinggi'] as $f)
            @error($f)<p class="err">{{ $message }}</p>@enderror
        @endforeach

        <p><button class="btn" type="submit">Hitung</button></p>
    </form>
</div>

@if (session('hasil'))
    @php($h = session('hasil'))
    <div class="card">
        <h2>Rincian ({{ $h['layanan'] }})</h2>
        <table>
            <tr><td>Berat aktual (dibulatkan)</td><td>{{ $h['berat_aktual_kg'] }} kg</td></tr>
            <tr><td>Berat volumetrik</td><td>{{ $h['berat_volumetrik_kg'] }} kg</td></tr>
            <tr><td>Berat ditagih</td><td>{{ $h['berat_tagih'] }} kg &times; Rp{{ number_format($h['tarif_per_kg'], 0, ',', '.') }}</td></tr>
            <tr><td>Biaya kirim</td><td>Rp{{ number_format($h['biaya_dasar'], 0, ',', '.') }}</td></tr>
            <tr><td>Diskon member (10%)</td><td>-Rp{{ number_format($h['diskon'], 0, ',', '.') }}</td></tr>
            <tr><td>Asuransi (0,2%)</td><td>Rp{{ number_format($h['asuransi'], 0, ',', '.') }}</td></tr>
        </table>
        <p class="total">Total estimasi: Rp{{ number_format($h['total'], 0, ',', '.') }}</p>
        <p class="muted">Diskon member pada estimasi ini bersifat perkiraan; status member diverifikasi saat paket dibuat.</p>
    </div>
@endif
@endsection
