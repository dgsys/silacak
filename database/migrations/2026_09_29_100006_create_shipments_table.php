<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipments', function (Blueprint $t) {
            $t->id();
            $t->string('resi', 20)->unique(); // indeks unik => pencarian resi cepat
            $t->foreignId('customer_id')->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $t->foreignId('service_id')->constrained()->restrictOnDelete()->cascadeOnUpdate();
            $t->foreignId('origin_branch_id')->constrained('branches')->restrictOnDelete()->cascadeOnUpdate();
            $t->foreignId('dest_branch_id')->constrained('branches')->restrictOnDelete()->cascadeOnUpdate();
            $t->string('penerima_nama', 100);
            $t->string('penerima_alamat', 255);
            $t->decimal('berat_aktual', 8, 2);
            $t->unsignedSmallInteger('panjang')->default(0);
            $t->unsignedSmallInteger('lebar')->default(0);
            $t->unsignedSmallInteger('tinggi')->default(0);
            $t->unsignedSmallInteger('berat_tagih');
            $t->unsignedBigInteger('nilai_barang')->default(0);
            $t->unsignedBigInteger('ongkir');
            $t->string('status_terakhir', 30)->default('DITERIMA')->index();
            $t->timestamps();

            $t->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipments');
    }
};
