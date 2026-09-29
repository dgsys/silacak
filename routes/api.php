<?php

use App\Http\Controllers\Api\LacakApiController;
use App\Http\Controllers\Api\OngkirApiController;
use Illuminate\Support\Facades\Route;

// API stateless (tanpa sesi/cookie) => tidak memakai CSRF.
// Hanya endpoint publik yang tidak mengubah data: lacak resi & hitung ongkir.
// Endpoint tulis (buat paket, ubah status) sebaiknya ditambahkan dengan Laravel Sanctum (token Bearer).
Route::prefix('v1')->group(function () {
    Route::get('lacak/{resi}', [LacakApiController::class, 'show'])
        ->where('resi', '[A-Za-z0-9]{8,20}')
        ->middleware('throttle:lacak');

    Route::post('ongkir', [OngkirApiController::class, 'hitung'])
        ->middleware('throttle:ongkir');
});
