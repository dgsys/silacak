<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Service;
use App\Models\Shipment;
use App\Models\User;
use Database\Seeders\ServiceSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class ResiFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dapat_menyimpan_shipment_dummy_ke_database(): void
    {
        $this->seed(ServiceSeeder::class);

        $origin = Branch::create([
            'kode' => 'TEST01',
            'nama' => 'Cabang Asal Test',
            'kota' => 'Jakarta',
        ]);
        $destination = Branch::create([
            'kode' => 'TEST02',
            'nama' => 'Cabang Tujuan Test',
            'kota' => 'Bandung',
        ]);
        $admin = User::create([
            'nama' => 'Admin Test',
            'email' => 'admin-resi-test@example.test',
            'password' => 'password-test',
            'role' => 'admin',
        ]);

        Log::shouldReceive('channel')->once()->with('shipments')->andReturnSelf();
        Log::shouldReceive('info')->once();

        $response = $this->actingAs($admin)->post(route('shipments.store'), [
            'customer_nama' => 'Pelanggan Dummy',
            'customer_telepon' => '081234567890',
            'customer_alamat' => 'Jl. Contoh No. 10',
            'service_id' => Service::where('kode', 'REGULER')->value('id'),
            'origin_branch_id' => $origin->id,
            'dest_branch_id' => $destination->id,
            'penerima_nama' => 'Penerima Dummy',
            'penerima_alamat' => 'Jl. Tujuan No. 20',
            'berat_aktual' => '1.50',
            'nilai_barang' => 100000,
        ]);

        $shipment = Shipment::firstOrFail();

        $response->assertRedirect(route('shipments.show', $shipment));
        $this->assertDatabaseHas('customers', [
            'id' => $shipment->customer_id,
            'nama' => 'Pelanggan Dummy',
            'telepon' => '081234567890',
        ]);
        $this->assertDatabaseHas('shipments', [
            'id' => $shipment->id,
            'resi' => $shipment->resi,
            'origin_branch_id' => $origin->id,
            'dest_branch_id' => $destination->id,
            'status_terakhir' => 'DITERIMA',
        ]);
        $this->assertDatabaseHas('tracking_events', [
            'shipment_id' => $shipment->id,
            'branch_id' => $origin->id,
            'status' => 'DITERIMA',
        ]);
    }
}