<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->unsignedBigInteger('biaya_dasar')->nullable()->after('ongkir');
            $table->unsignedBigInteger('diskon')->nullable()->after('biaya_dasar');
            $table->unsignedBigInteger('asuransi')->nullable()->after('diskon');
        });
    }

    public function down(): void
    {
        Schema::table('shipments', function (Blueprint $table) {
            $table->dropColumn(['biaya_dasar', 'diskon', 'asuransi']);
        });
    }
};