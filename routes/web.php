<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RekapController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\ProfileController; // 1. Tambahkan import ini

Route::get('/', function () {
    return redirect()->route('login');
});

// Route yang wajib LOGIN
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [RekapController::class, 'index'])->name('dashboard');

    // Route Penjualan & Detail Barang
    Route::get('/penjualan/siap-jual', [PenjualanController::class, 'siapJual'])->name('penjualan.siap-jual');
    Route::get('/penjualan/detail-barang', [PenjualanController::class, 'detailBarang'])->name('penjualan.detail-barang');
    Route::put('/penjualan/update-detail-barang/{id}', [PenjualanController::class, 'updateDetailBarang'])->name('penjualan.update-detail-barang');
    Route::delete('/penjualan/destroy-detail-barang/{id}', [PenjualanController::class, 'destroyDetailBarang'])->name('penjualan.destroy-detail-barang');

    // 2. Tambahkan Route Profile di bawah ini
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
