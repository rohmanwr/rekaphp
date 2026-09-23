<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RekapController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TokoController;
use App\Http\Controllers\Auth\DirectPasswordResetController;
use App\Http\Controllers\UserController;


Route::get('/', function () {
    return redirect()->route('login');
});

// ==========================================
// ROUTE TAMU / GUEST (Belum Login / Ubah Password Langsung)
// ==========================================
Route::middleware('guest')->group(function () {
    // Form Ubah Password Langsung (Tanpa Kirim Email)
    Route::get('/forgot-password', [DirectPasswordResetController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [DirectPasswordResetController::class, 'store'])->name('password.update.direct');
});

// ==========================================
// ROUTE UTAMA (Wajib LOGIN & Terverifikasi)
// ==========================================
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard & Rekap
    Route::get('/dashboard', [RekapController::class, 'index'])->name('dashboard');
    Route::get('/rekap', [RekapController::class, 'index'])->name('rekap.index');

    // Rekap Pembelian
    Route::get('/pembelian', [PembelianController::class, 'index'])->name('pembelian.index');
    Route::get('/pembelian/create', [PembelianController::class, 'create'])->name('pembelian.create');
    Route::post('/pembelian', [PembelianController::class, 'store'])->name('pembelian.store');
    Route::put('/pembelian/{id}', [PembelianController::class, 'update'])->name('pembelian.update');
    Route::patch('/pembelian/{id}/status', [PembelianController::class, 'updateStatus'])->name('pembelian.updateStatus');
    Route::delete('/pembelian/{id}', [PembelianController::class, 'destroy'])->name('pembelian.destroy');
    Route::get('/pembelian/siap-jual', [PembelianController::class, 'barangSiapJual'])->name('pembelian.siap_jual');

    // Histori Rekap Pembelian & Restore Status
    Route::get('/pembelian/histori-rekap', [PembelianController::class, 'historiRekap'])->name('pembelian.histori_rekap');
    Route::patch('/pembelian/{id}/restore-status', [PembelianController::class, 'restoreStatus'])->name('pembelian.restoreStatus');

    // Invoice & Pencetakan Pembelian
    Route::post('/pembelian/invoice/create', [PembelianController::class, 'createInvoice'])->name('invoice.create');
    Route::post('/pembelian/invoice/store', [PembelianController::class, 'storeInvoice'])->name('invoice.store');
    Route::get('/pembelian/invoice/{id}', [PembelianController::class, 'showInvoice'])->name('invoice.show');

    // Master Data Barang
    Route::get('/barang', [BarangController::class, 'index'])->name('barang.index');
    Route::post('/barang', [BarangController::class, 'store'])->name('barang.store');
    Route::put('/barang/{id}', [BarangController::class, 'update'])->name('barang.update');
    Route::delete('/barang/{id}', [BarangController::class, 'destroy'])->name('barang.destroy');

    // Master Data Toko
    Route::get('/toko', [TokoController::class, 'index'])->name('toko.index');
    Route::post('/toko', [TokoController::class, 'store'])->name('toko.store');
    Route::put('/toko/{id}', [TokoController::class, 'update'])->name('toko.update');
    Route::delete('/toko/{id}', [TokoController::class, 'destroy'])->name('toko.destroy');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Penjualan & Histori Penjualan (Group Prefix)
    Route::prefix('penjualan')->name('penjualan.')->group(function () {
        Route::get('/siap-jual', [PenjualanController::class, 'siapJual'])->name('siap-jual');
        Route::get('/histori', [PenjualanController::class, 'historiPenjualan'])->name('histori');
        Route::get('/detail-barang', [PenjualanController::class, 'detailBarang'])->name('detail-barang');
        Route::put('/detail-barang/{id}', [PenjualanController::class, 'updateDetailBarang'])->name('update-detail-barang');
        Route::delete('/detail-barang/{id}', [PenjualanController::class, 'destroyDetailBarang'])->name('destroy-detail-barang');
        Route::post('/store', [PenjualanController::class, 'store'])->name('store');
    });

    // Update item histori penjualan secara manual
    Route::put('/penjualan/update-histori-item/{invoiceId}', [PenjualanController::class, 'updateHistoriItem'])->name('penjualan.update-histori-item');

    Route::middleware(['auth'])->group(function () {
        // Rute list user
        Route::get('/users', [UserController::class, 'index'])->name('user.index');
    });
});

require __DIR__ . '/auth.php';
