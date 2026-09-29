@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
    <header class="dashboard-heading">
        <div>
            <p class="dashboard-eyebrow">SiLacak</p>
            <h1>Dashboard</h1>
        </div>
    </header>

    <section class="dashboard-stats" aria-label="Statistik aplikasi">
        <article class="dashboard-stat dashboard-stat--shipments">
            <h2>Total Resi</h2>
            <p class="dashboard-stat__value" data-stat="shipments">{{ number_format($shipmentCount, 0, ',', '.') }}</p>
            <p class="dashboard-stat__caption">Paket tercatat</p>
        </article>

        <article class="dashboard-stat dashboard-stat--customers">
            <h2>Total Customer</h2>
            <p class="dashboard-stat__value" data-stat="customers">{{ number_format($customerCount, 0, ',', '.') }}</p>
            <p class="dashboard-stat__caption">Customer terdaftar</p>
        </article>

        <article class="dashboard-stat dashboard-stat--branches">
            <h2>Total Branch</h2>
            <p class="dashboard-stat__value" data-stat="branches">{{ number_format($branchCount, 0, ',', '.') }}</p>
            <p class="dashboard-stat__caption">Cabang terdaftar</p>
        </article>
    </section>

    <section class="dashboard-statuses" aria-labelledby="dashboard-statuses-title">
        <h2 id="dashboard-statuses-title">Jumlah Resi per Status</h2>
        <ul class="dashboard-status-list">
            @foreach ($statusCounts as $status)
                <li class="dashboard-status-item">
                    <span>{{ $status['label'] }}</span>
                    <span class="dashboard-status-count" data-status="{{ $status['value'] }}">{{ number_format($status['count'], 0, ',', '.') }}</span>
                </li>
            @endforeach
        </ul>
    </section>
@endsection