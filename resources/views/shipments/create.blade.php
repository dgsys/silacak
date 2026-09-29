@extends('layouts.app')
@section('title', 'Paket baru')
@section('content')
<div class="card">
    <h1>Buat paket</h1>
    <form method="POST" action="{{ route('shipments.store') }}">
        @csrf

        <h2>Pengirim</h2>
        <div class="customer-lookup" data-customer-search data-search-url="{{ route('customers.search') }}">
            <label for="customer_search">Cari pelanggan terdaftar</label>
            <input id="customer_search" type="search" value="{{ old('customer_search', $selectedCustomer ? $selectedCustomer->nama.' ('.$selectedCustomer->telepon.')' : '') }}" placeholder="Nama atau awalan telepon" autocomplete="off" role="combobox" aria-autocomplete="list" aria-controls="customer_search_results" aria-expanded="false" @if ($selectedCustomer) readonly @endif>
            <input id="customer_id" type="hidden" name="customer_id" value="{{ old('customer_id', $selectedCustomer?->id) }}">
            <button id="customer_search_clear" class="btn secondary" type="button" @if (! $selectedCustomer) hidden @endif>Ganti pelanggan</button>
            <div id="customer_search_results" class="customer-search-results" role="listbox" hidden></div>
            <p id="customer_search_status" class="muted" aria-live="polite">{{ $selectedCustomer ? 'Pelanggan dipilih.' : 'Ketik minimal 2 karakter untuk mencari.' }}</p>
        </div>
        @error('customer_id')<p class="err">{{ $message }}</p>@enderror
        <div class="row">
            <div>
                <label for="customer_nama">Nama pelanggan baru</label>
                <input id="customer_nama" name="customer_nama" value="{{ old('customer_nama') }}" maxlength="100">
                @error('customer_nama')<p class="err">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="customer_telepon">Telepon</label>
                <input id="customer_telepon" name="customer_telepon" value="{{ old('customer_telepon') }}" maxlength="20">
                @error('customer_telepon')<p class="err">{{ $message }}</p>@enderror
            </div>
        </div>
        <label for="customer_alamat">Alamat</label>
        <input id="customer_alamat" name="customer_alamat" value="{{ old('customer_alamat') }}" maxlength="255">

        <label class="check-row" for="member">
            <input type="hidden" name="member" value="0">
            <input id="member" type="checkbox" name="member" value="1" @checked(old('member', '1'))>
            <span>Terapkan diskon member 10%</span>
        </label>
        <p class="muted">Diskon hanya berlaku untuk pelanggan terdaftar dengan status member.</p>

        <h2>Rute &amp; layanan</h2>
        <div class="row">
            @if (auth()->user()->isAdmin())
                <div>
                    <label for="origin_branch_id">Cabang asal</label>
                    <select id="origin_branch_id" name="origin_branch_id" required>
                        @foreach ($branches as $b)
                            <option value="{{ $b->id }}" @selected(old('origin_branch_id') == $b->id)>{{ $b->nama }}</option>
                        @endforeach
                    </select>
                    @error('origin_branch_id')<p class="err">{{ $message }}</p>@enderror
                </div>
            @endif
            <div>
                <label for="dest_branch_id">Cabang tujuan</label>
                <select id="dest_branch_id" name="dest_branch_id" required>
                    @foreach ($branches as $b)
                        <option value="{{ $b->id }}" @selected(old('dest_branch_id') == $b->id)>{{ $b->nama }}</option>
                    @endforeach
                </select>
                @error('dest_branch_id')<p class="err">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="service_id">Layanan</label>
                <select id="service_id" name="service_id" required>
                    @foreach ($services as $s)
                        <option value="{{ $s->id }}" @selected(old('service_id') == $s->id)>{{ $s->nama }} (Rp{{ number_format($s->tarif_per_kg, 0, ',', '.') }}/kg)</option>
                    @endforeach
                </select>
                @error('service_id')<p class="err">{{ $message }}</p>@enderror
            </div>
        </div>

        <h2>Penerima</h2>
        <div class="row">
            <div>
                <label for="penerima_nama">Nama penerima</label>
                <input id="penerima_nama" name="penerima_nama" value="{{ old('penerima_nama') }}" maxlength="100" required>
                @error('penerima_nama')<p class="err">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="penerima_alamat">Alamat penerima</label>
                <input id="penerima_alamat" name="penerima_alamat" value="{{ old('penerima_alamat') }}" maxlength="255" required>
                @error('penerima_alamat')<p class="err">{{ $message }}</p>@enderror
            </div>
        </div>

        <h2>Barang</h2>
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
        <div class="row">
            <div><label for="panjang">Panjang (cm)</label><input id="panjang" type="number" min="0" name="panjang" value="{{ old('panjang', 0) }}"></div>
            <div><label for="lebar">Lebar (cm)</label><input id="lebar" type="number" min="0" name="lebar" value="{{ old('lebar', 0) }}"></div>
            <div><label for="tinggi">Tinggi (cm)</label><input id="tinggi" type="number" min="0" name="tinggi" value="{{ old('tinggi', 0) }}"></div>
        </div>

        <p class="muted">Ongkir dihitung otomatis oleh sistem (diskon member &amp; asuransi ikut dihitung).</p>
        <p><button class="btn" type="submit">Simpan paket</button></p>
    </form>
</div>
@endsection
