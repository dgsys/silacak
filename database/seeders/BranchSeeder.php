<?php

namespace Database\Seeders;

use App\Models\Branch;
use Illuminate\Database\Seeder;

class BranchSeeder extends Seeder
{
    /** Contoh 4 cabang. Untuk 120 cabang, impor dari CSV (kode, nama, kota) dengan pola yang sama. */
    public function run(): void
    {
        $rows = [
            ['kode' => 'JKT001', 'nama' => 'Cabang Jakarta Pusat', 'kota' => 'Jakarta'],
            ['kode' => 'BDG001', 'nama' => 'Cabang Bandung', 'kota' => 'Bandung'],
            ['kode' => 'YGY001', 'nama' => 'Cabang Yogyakarta', 'kota' => 'Yogyakarta'],
            ['kode' => 'SBY001', 'nama' => 'Cabang Surabaya', 'kota' => 'Surabaya'],
        ];

        foreach ($rows as $row) {
            Branch::updateOrCreate(['kode' => $row['kode']], $row);
        }
    }
}
