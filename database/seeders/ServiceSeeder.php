<?php

namespace Database\Seeders;

use App\Models\Service;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['kode' => 'REGULER', 'nama' => 'Reguler', 'tarif_per_kg' => 9000, 'min_kg' => 1, 'aktif' => true],
            ['kode' => 'EXPRESS', 'nama' => 'Express', 'tarif_per_kg' => 15000, 'min_kg' => 1, 'aktif' => true],
            ['kode' => 'KARGO', 'nama' => 'Kargo', 'tarif_per_kg' => 6000, 'min_kg' => 10, 'aktif' => true],
            // Rilis v1.1.0: cukup ubah aktif menjadi true (tanpa mengubah kode program).
            ['kode' => 'SAME_DAY', 'nama' => 'Same Day', 'tarif_per_kg' => 25000, 'min_kg' => 1, 'aktif' => false],
        ];

        foreach ($rows as $row) {
            Service::updateOrCreate(['kode' => $row['kode']], $row);
        }
    }
}
