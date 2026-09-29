<?php

namespace App\Services\Tracking;

use App\Models\Shipment;
use Illuminate\Support\Facades\Cache;

/**
 * Pelacakan resi untuk publik.
 * - Satu query paket + eager loading riwayat (tanpa N+1), memakai indeks resi & (shipment_id, waktu).
 * - Hasil di-cache singkat (30 detik); cache dihapus saat status diperbarui.
 * - Data pribadi tidak dibocorkan: nama penerima disamarkan, alamat & telepon tidak ditampilkan.
 */
class TrackingService
{
    public const CACHE_TTL = 30;

    public static function cacheKey(string $resi): string
    {
        return 'lacak:'.strtoupper($resi);
    }

    public static function forget(string $resi): void
    {
        Cache::forget(self::cacheKey($resi));
    }

    /** @return array<string,mixed>|null */
    public function lookup(string $resi): ?array
    {
        $resi = strtoupper($resi);

        return Cache::remember(self::cacheKey($resi), self::CACHE_TTL, fn () => $this->query($resi));
    }

    /** @return array<string,mixed>|null */
    private function query(string $resi): ?array
    {
        $shipment = Shipment::query()
            ->where('resi', $resi)
            ->with([
                'service:id,nama',
                'originBranch:id,kota',
                'destBranch:id,kota',
                'events' => fn ($q) => $q->select('id', 'shipment_id', 'branch_id', 'status', 'catatan', 'waktu')
                    ->orderByDesc('waktu')->orderByDesc('id'),
                'events.branch:id,nama,kota',
            ])
            ->first();

        if (! $shipment) {
            return null;
        }

        return [
            'resi' => $shipment->resi,
            'layanan' => $shipment->service->nama,
            'asal' => $shipment->originBranch->kota,
            'tujuan' => $shipment->destBranch->kota,
            'penerima' => self::samarkan($shipment->penerima_nama),
            'berat_tagih' => $shipment->berat_tagih,
            'status' => $shipment->status_terakhir->label(),
            'status_kode' => $shipment->status_terakhir->value,
            'events' => $shipment->events->map(fn ($e) => [
                'status' => $e->status->label(),
                'lokasi' => $e->branch->nama.' ('.$e->branch->kota.')',
                'catatan' => $e->catatan,
                'waktu' => $e->waktu->format('d/m/Y H:i'),
            ])->all(),
        ];
    }

    /** "Sari Utami" => "S*** U****" */
    public static function samarkan(string $nama): string
    {
        return collect(preg_split('/\s+/u', trim($nama)) ?: [])
            ->map(fn ($w) => mb_substr($w, 0, 1).str_repeat('*', max(mb_strlen($w) - 1, 0)))
            ->implode(' ');
    }
}
