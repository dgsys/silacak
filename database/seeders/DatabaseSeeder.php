<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            ServiceSeeder::class,
            BranchSeeder::class,
            UserSeeder::class,
        ]);

        // Data contoh hanya untuk lingkungan lokal.
        if (app()->environment('local')) {
            $this->call(DemoSeeder::class);
        }
    }
}
