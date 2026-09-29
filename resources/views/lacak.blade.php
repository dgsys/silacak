@extends('layouts.app')
@section('title', 'Lacak resi')
@section('content')
<div class="card">
    <h1>Lacak paket</h1>
    {{-- GET (hanya membaca data) sehingga tidak memerlukan token CSRF --}}
    <form method="GET" action="{{ route('lacak') }}" class="inline">
        <input type="text" name="resi" value="{{ $resi ?? old('resi') }}" placeholder="Masukkan nomor resi, mis. SLN260929123456" maxlength="20" required>
        <button class="btn" type="submit">Lacak</button>
    </form>
    @error('resi')<p class="err">{{ $message }}</p>@enderror
</div>

@if (! empty($dicari))
    @if ($hasil)
        <div class="card">
            <h2>{{ $hasil['resi'] }} <span class="badge">{{ $hasil['status'] }}</span></h2>
            <p class="muted">{{ $hasil['layanan'] }} &middot; {{ $hasil['asal'] }} &rarr; {{ $hasil['tujuan'] }} &middot; {{ $hasil['berat_tagih'] }} kg &middot; Penerima: {{ $hasil['penerima'] }}</p>
        </div>
        <div class="card">
            <h2>Riwayat perjalanan</h2>
            <ul class="timeline">
                @foreach ($hasil['events'] as $e)
                    <li>
                        <strong>{{ $e['status'] }}</strong><br>
                        <span class="muted">{{ $e['waktu'] }} &middot; {{ $e['lokasi'] }}</span>
                        @if ($e['catatan'])<br>{{ $e['catatan'] }}@endif
                    </li>
                @endforeach
            </ul>
        </div>
    @else
        <div class="card"><p>Resi <strong>{{ $resi }}</strong> tidak ditemukan. Periksa kembali nomor resi Anda.</p></div>
    @endif
@endif
@endsection
