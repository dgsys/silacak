<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\CustomerSearchController;
use App\Http\Controllers\LacakController;
use App\Http\Controllers\OngkirController;
use App\Http\Controllers\ShipmentController;
use Illuminate\Support\Facades\Route;

// ---- Publik (tanpa login) -------------------------------------------------
// Lacak resi: GET (baca saja), dibatasi rate limit. QR pada label mengarah ke sini.
Route::get('/', LacakController::class)->middleware('throttle:lacak');
Route::get('/lacak', LacakController::class)->middleware('throttle:lacak')->name('lacak');

// Estimasi ongkir: POST -> dilindungi CSRF (grup "web").
Route::get('/ongkir', [OngkirController::class, 'form'])->name('ongkir');
Route::post('/ongkir', [OngkirController::class, 'hitung'])->middleware('throttle:ongkir')->name('ongkir.hitung');

// ---- Autentikasi ----------------------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'show'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
});
// Logout wajib POST + CSRF (bukan GET) agar tidak bisa dipicu dari halaman lain.
Route::post('/logout', [LoginController::class, 'logout'])->middleware('auth')->name('logout');

// ---- Area internal (admin & cabang) --------------------------------------
Route::get('/customers/search', CustomerSearchController::class)
    ->middleware(['auth', 'role:admin,cabang'])
    ->name('customers.search');

Route::middleware(['auth', 'role:admin,cabang'])->prefix('shipments')->name('shipments.')->group(function () {
    Route::get('/', [ShipmentController::class, 'index'])->name('index');
    Route::get('/create', [ShipmentController::class, 'create'])->name('create');
    Route::post('/', [ShipmentController::class, 'store'])->name('store');
    Route::get('/{shipment}', [ShipmentController::class, 'show'])->name('show');
    Route::post('/{shipment}/status', [ShipmentController::class, 'updateStatus'])->name('status');
    Route::get('/{shipment}/label', [ShipmentController::class, 'label'])->name('label');
});

Route::middleware(['auth', 'role:admin'])->resource('branches', BranchController::class)->except('show');
