<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Password TIDAK ditulis di kode. Isi SEED_ADMIN_PASSWORD di .env.
        // Nilai bawaan hanya diizinkan di lingkungan lokal.
        $password = env('SEED_ADMIN_PASSWORD');

        if (! $password) {
            if (! app()->isLocal()) {
                throw new RuntimeException('Isi SEED_ADMIN_PASSWORD di .env sebelum menjalankan seeder di luar lokal.');
            }
            $password = 'Password!12345';
        }

        $hash = Hash::make($password);

        User::updateOrCreate(['email' => 'admin@silacak.test'], [
            'nama' => 'Admin Pusat', 'role' => 'admin', 'branch_id' => null, 'password' => $hash,
        ]);

        foreach (['JKT001' => 'jkt', 'YGY001' => 'ygy'] as $kode => $slug) {
            $branch = Branch::where('kode', $kode)->first();
            User::updateOrCreate(['email' => "cabang.{$slug}@silacak.test"], [
                'nama' => 'Petugas '.$branch?->nama, 'role' => 'cabang', 'branch_id' => $branch?->id, 'password' => $hash,
            ]);
        }
    }
}
