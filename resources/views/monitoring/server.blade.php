@extends('layouts.app')

@section('title', 'Monitoring Server')

@section('content')
    <header class="dashboard-heading">
        <div>
            <p class="dashboard-eyebrow">Infrastruktur</p>
            <h1>Monitoring Server</h1>
        </div>
        <p class="muted">Diambil {{ $sampledAt->format('d M Y, H:i:s') }}</p>
    </header>

    <section class="monitoring-grid" aria-label="Penggunaan resources server">
        <article class="monitoring-panel">
            <div class="monitoring-panel__heading">
                <h2>Memori sistem</h2>
                <span class="monitoring-indicator" aria-hidden="true"></span>
            </div>
            @if ($systemMemory)
                <p class="monitoring-value">{{ number_format($systemMemory['percent'], 1, ',', '.') }}%</p>
                <p class="muted">{{ number_format($systemMemory['used'] / 1073741824, 2, ',', '.') }} GB digunakan dari {{ number_format($systemMemory['total'] / 1073741824, 2, ',', '.') }} GB</p>
                <progress class="monitoring-progress" max="100" value="{{ $systemMemory['percent'] }}" aria-label="Penggunaan memori sistem"></progress>
                <p class="monitoring-detail">{{ number_format($systemMemory['available'] / 1073741824, 2, ',', '.') }} GB tersedia</p>
            @else
                <p class="monitoring-unavailable">Tidak tersedia pada OS ini</p>
                <p class="monitoring-detail">Data RAM sistem tersedia pada host Linux dengan akses ke /proc/meminfo.</p>
            @endif
        </article>

        <article class="monitoring-panel">
            <div class="monitoring-panel__heading">
                <h2>Memori proses PHP</h2>
                <span class="monitoring-indicator monitoring-indicator--green" aria-hidden="true"></span>
            </div>
            <p class="monitoring-value">{{ number_format($phpMemory['used'] / 1048576, 1, ',', '.') }} MB</p>
            <p class="muted">Puncak request {{ number_format($phpMemory['peak'] / 1048576, 1, ',', '.') }} MB</p>
            @if ($phpMemory['percent'] !== null)
                <progress class="monitoring-progress" max="100" value="{{ $phpMemory['percent'] }}" aria-label="Penggunaan memori PHP"></progress>
                <p class="monitoring-detail">{{ number_format($phpMemory['percent'], 1, ',', '.') }}% dari batas {{ number_format($phpMemory['limit'] / 1048576, 0, ',', '.') }} MB</p>
            @else
                <p class="monitoring-detail">Batas memori: {{ $phpMemory['limitLabel'] }}</p>
            @endif
        </article>

        <article class="monitoring-panel">
            <div class="monitoring-panel__heading">
                <h2>Penyimpanan server</h2>
                <span class="monitoring-indicator monitoring-indicator--orange" aria-hidden="true"></span>
            </div>
            @if ($disk['percent'] !== null)
                <p class="monitoring-value">{{ number_format($disk['percent'], 1, ',', '.') }}%</p>
                <p class="muted">{{ number_format($disk['used'] / 1073741824, 2, ',', '.') }} GB digunakan dari {{ number_format($disk['total'] / 1073741824, 2, ',', '.') }} GB</p>
                <progress class="monitoring-progress" max="100" value="{{ $disk['percent'] }}" aria-label="Penggunaan penyimpanan server"></progress>
                <p class="monitoring-detail">{{ number_format($disk['free'] / 1073741824, 2, ',', '.') }} GB tersedia pada volume aplikasi</p>
            @else
                <p class="monitoring-unavailable">Data penyimpanan tidak tersedia</p>
            @endif
        </article>

        <article class="monitoring-panel">
            <div class="monitoring-panel__heading">
                <h2>Load average</h2>
                <span class="monitoring-indicator monitoring-indicator--blue" aria-hidden="true"></span>
            </div>
            @if ($loadAverage)
                <div class="monitoring-loads">
                    @foreach (['1 menit', '5 menit', '15 menit'] as $index => $period)
                        <div>
                            <span class="monitoring-detail">{{ $period }}</span>
                            <strong>{{ number_format($loadAverage[$index], 2, ',', '.') }}</strong>
                        </div>
                    @endforeach
                </div>
                <p class="monitoring-detail">Beban sistem; bukan persentase penggunaan CPU.</p>
            @else
                <p class="monitoring-unavailable">Tidak tersedia pada OS ini</p>
                <p class="monitoring-detail">Load average disediakan oleh sistem operasi berbasis Unix.</p>
            @endif
        </article>
    </section>
@endsection