<?php

namespace App\Services\Shipment;

use App\Enums\ShipmentStatus;
use App\Models\Customer;
use App\Models\Service;
use App\Models\Shipment;
use App\Services\Ongkir\OngkirService;
use App\Services\Tracking\TrackingService;
use Illuminate\Support\Facades\DB;

class ShipmentService
{
    public function __construct(private readonly OngkirService $ongkir)
    {
    }

    /**
     * Membuat paket + riwayat awal dalam satu transaksi.
     * Ongkir, berat tagih, dan status SELALU dihitung server, tidak diambil dari input pengguna.
     *
     * @param  array<string,mixed>  $data  data tervalidasi
     */
    public function create(array $data, Customer $customer, int $originBranchId): Shipment
    {
        $service = Service::aktif()->findOrFail($data['service_id']);

        $hasil = $this->ongkir->hitung(
            $service->tarif_per_kg,
            $service->min_kg,
            $data['berat_aktual'],
            (int) ($data['panjang'] ?? 0),
            (int) ($data['lebar'] ?? 0),
            (int) ($data['tinggi'] ?? 0),
            (int) ($data['nilai_barang'] ?? 0),
            (bool) ($customer->is_member ?? false) && (bool) ($data['member'] ?? true),
        );

        return DB::transaction(function () use ($data, $customer, $service, $originBranchId, $hasil) {
            $shipment = Shipment::create([
                'resi' => $this->buatResi(),
                'customer_id' => $customer->id,
                'service_id' => $service->id,
                'origin_branch_id' => $originBranchId,
                'dest_branch_id' => (int) $data['dest_branch_id'],
                'penerima_nama' => $data['penerima_nama'],
                'penerima_alamat' => $data['penerima_alamat'],
                'berat_aktual' => $data['berat_aktual'],
                'panjang' => (int) ($data['panjang'] ?? 0),
                'lebar' => (int) ($data['lebar'] ?? 0),
                'tinggi' => (int) ($data['tinggi'] ?? 0),
                'berat_tagih' => $hasil['berat_tagih'],
                'nilai_barang' => (int) ($data['nilai_barang'] ?? 0),
                'ongkir' => $hasil['total'],
                'biaya_dasar' => $hasil['biaya_dasar'],
                'diskon' => $hasil['diskon'],
                'asuransi' => $hasil['asuransi'],
                'status_terakhir' => ShipmentStatus::Diterima,
            ]);

            $shipment->events()->create([
                'branch_id' => $originBranchId,
                'status' => ShipmentStatus::Diterima,
                'catatan' => 'Paket diterima di cabang asal',
                'waktu' => now(),
            ]);

            return $shipment;
        });
    }

    public function updateStatus(Shipment $shipment, ShipmentStatus $status, int $branchId, ?string $catatan): void
    {
        DB::transaction(function () use ($shipment, $status, $branchId, $catatan) {
            $shipment->events()->create([
                'branch_id' => $branchId,
                'status' => $status,
                'catatan' => $catatan,
                'waktu' => now(),
            ]);
            $shipment->update(['status_terakhir' => $status]);
        });

        TrackingService::forget($shipment->resi);
    }

    /** Format: SLN + yyMMdd + 6 digit acak (15 karakter), dijamin unik. */
    private function buatResi(): string
    {
        do {
            $resi = 'SLN'.now()->format('ymd').str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        } while (Shipment::where('resi', $resi)->exists());

        return $resi;
    }
}
