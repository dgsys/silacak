<?php

namespace Database\Seeders;

use App\Enums\ShipmentStatus;
use App\Models\Branch;
use App\Models\Courier;
use App\Models\Customer;
use App\Models\Service;
use App\Services\Shipment\ShipmentService;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $svc = app(ShipmentService::class);

        $jkt = Branch::where('kode', 'JKT001')->firstOrFail();
        $ygy = Branch::where('kode', 'YGY001')->firstOrFail();

        Courier::firstOrCreate(['nama' => 'Budi Santoso'], ['branch_id' => $jkt->id, 'telepon' => '081200000001']);
        Courier::firstOrCreate(['nama' => 'Wawan Setiawan'], ['branch_id' => $ygy->id, 'telepon' => '081200000002']);

        $rina = Customer::firstOrCreate(['telepon' => '081311110001'], ['nama' => 'Rina Toko Online', 'alamat' => 'Jl. Merdeka No. 10, Jakarta']);
        $rina->forceFill(['is_member' => true])->save(); // is_member tidak mass-assignable
        $dedi = Customer::firstOrCreate(['telepon' => '081311110002'], ['nama' => 'Dedi Wijaya', 'alamat' => 'Jl. Malioboro No. 5, Yogyakarta']);

        $reguler = Service::where('kode', 'REGULER')->firstOrFail();
        $express = Service::where('kode', 'EXPRESS')->firstOrFail();

        // 1,3 kg Reguler => ditagih 2 kg x 9.000 = Rp18.000
        $p1 = $svc->create([
            'service_id' => $reguler->id, 'dest_branch_id' => $ygy->id,
            'penerima_nama' => 'Sari Utami', 'penerima_alamat' => 'Jl. Kaliurang Km 5, Sleman',
            'berat_aktual' => '1.30', 'panjang' => 20, 'lebar' => 15, 'tinggi' => 10, 'nilai_barang' => 500000,
        ], $dedi, $jkt->id);
        $svc->updateStatus($p1, ShipmentStatus::DalamPerjalanan, $jkt->id, 'Berangkat menuju Yogyakarta');

        // Express member: 10 kg volumetrik, diskon 10%, asuransi 0,2% => Rp139.000
        $svc->create([
            'service_id' => $express->id, 'dest_branch_id' => $ygy->id,
            'penerima_nama' => 'Andi Pratama', 'penerima_alamat' => 'Jl. Braga No. 20, Bandung',
            'berat_aktual' => '3.00', 'panjang' => 50, 'lebar' => 40, 'tinggi' => 30, 'nilai_barang' => 2000000,
        ], $rina, $jkt->id);
    }
}
