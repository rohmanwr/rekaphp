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
use App\Http\Controllers\KeuanganController;


Route::get('/', function () {
    return redirect()->route('login');
});

// ==========================================
// ROUTE TAMU / GUEST (Belum Login)
// ==========================================
Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', [DirectPasswordResetController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [DirectPasswordResetController::class, 'store'])->name('password.update.direct');
});

// ==========================================
// ROUTE UTAMA (Wajib LOGIN & Terverifikasi)
// ==========================================
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard & Rekap (Bisa diakses umum atau diatur dasarnya)
    Route::get('/dashboard', [RekapController::class, 'index'])->name('dashboard');
    Route::get('/rekap', [RekapController::class, 'index'])->name('rekap.index');

    // Profile (Bisa diakses semua user yang login)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');


    // ==========================================
    // 1. MENU PEMBELIAN & INPUT BARANG (Permission: pembelian)
    // ==========================================
    Route::middleware(['permission:pembelian'])->group(function () {
        Route::get('/pembelian', [PembelianController::class, 'index'])->name('pembelian.index');
        Route::get('/pembelian/create', [PembelianController::class, 'create'])->name('pembelian.create');
        Route::post('/pembelian', [PembelianController::class, 'store'])->name('pembelian.store');

        // <-- Rute statis HARUS DI ATAS sebelum rute yang menggunakan {id}
        Route::patch('/pembelian/update-status-massal', [PembelianController::class, 'updateStatusMassal'])->name('pembelian.updateStatusMassal');
        Route::patch('/pembelian/{id}/toggle-check', [PembelianController::class, 'toggleCheck'])->name('pembelian.toggleCheck');
        Route::put('/pembelian/{id}', [PembelianController::class, 'update'])->name('pembelian.update');
        Route::patch('/pembelian/{id}/status', [PembelianController::class, 'updateStatus'])->name('pembelian.updateStatus');
        Route::delete('/pembelian/{id}', [PembelianController::class, 'destroy'])->name('pembelian.destroy');
        Route::post('/pembelian/save-checklist', [PembelianController::class, 'saveChecklist'])->name('pembelian.saveChecklist');
    });


    // ==========================================
    // 2. MENU BARANG SIAP JUAL (Permission: siap_jual)
    // ==========================================
    Route::middleware(['permission:siap_jual'])->group(function () {
        Route::get('/pembelian/siap-jual', [PembelianController::class, 'barangSiapJual'])->name('pembelian.siap_jual');

        // Tambahkan rute patch ini untuk update status massal di halaman siap jual
        Route::patch('/pembelian/siap-jual/update-status-massal', [PembelianController::class, 'updateStatusMassal'])->name('pembelian.siap_jual.updateStatusMassal');
    });

    // ==========================================
    // 3. MENU TRANSAKSI & INVOICE / PENJUALAN (Permission: penjualan)
    // ==========================================
    Route::middleware(['permission:penjualan'])->group(function () {
        Route::prefix('penjualan')->name('penjualan.')->group(function () {
            Route::get('/siap-jual', [PenjualanController::class, 'siapJual'])->name('siap-jual');
            Route::get('/detail-barang', [PenjualanController::class, 'detailBarang'])->name('detail-barang');
            Route::put('/detail-barang/{id}', [PenjualanController::class, 'updateDetailBarang'])->name('update-detail-barang');
            Route::delete('/detail-barang/{id}', [PenjualanController::class, 'destroyDetailBarang'])->name('destroy-detail-barang');
            Route::post('/store', [PenjualanController::class, 'store'])->name('store');
        });

        // Invoice & Pencetakan Pembelian
        Route::post('/pembelian/invoice/create', [PembelianController::class, 'createInvoice'])->name('invoice.create');
        Route::post('/pembelian/invoice/store', [PembelianController::class, 'storeInvoice'])->name('invoice.store');
        Route::get('/pembelian/invoice/{id}', [PembelianController::class, 'showInvoice'])->name('invoice.show');
    });


    // ==========================================
    // 4. HISTORI PENJUALAN & ARSIP INVOICE (Permission: histori_penjualan)
    // ==========================================
    Route::middleware(['permission:histori_penjualan'])->group(function () {
        Route::get('/penjualan/histori', [PenjualanController::class, 'historiPenjualan'])->name('penjualan.histori');
        Route::put('/penjualan/update-histori-item/{invoiceId}', [PenjualanController::class, 'updateHistoriItem'])->name('penjualan.update-histori-item');
    });


    // ==========================================
    // 5. HISTORI REKAP PEMBELIAN (Permission: histori_rekap)
    // ==========================================
    Route::middleware(['permission:histori_rekap'])->group(function () {
        Route::get('/pembelian/histori-rekap', [PembelianController::class, 'historiRekap'])->name('pembelian.histori_rekap');
        Route::patch('/pembelian/{id}/restore-status', [PembelianController::class, 'restoreStatus'])->name('pembelian.restoreStatus');
    });


    // ==========================================
    // 6. MANAJEMEN PENGGUNA / ADMIN (Permission: user_management)
    // ==========================================
    Route::middleware(['permission:user_management'])->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('user.index');
        Route::patch('/users/{id}/update-role', [UserController::class, 'updateRole'])->name('user.update-role');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('user.update');
        Route::patch('/users/{id}/toggle-status', [UserController::class, 'toggleStatus'])->name('user.toggle-status');
        Route::patch('/users/{id}/reset-password', [UserController::class, 'resetPassword'])->name('user.reset-password');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('user.destroy');
    });

    //KEUANGAN
    // Keuangan & Kalkulasi Aset
    Route::get('/keuangan', [KeuanganController::class, 'index'])->name('keuangan.index');
    Route::post('/keuangan', [KeuanganController::class, 'storeOrUpdate'])->name('keuangan.store');

    // ==========================================
    // MASTER DATA
    // ==========================================
    Route::middleware(['permission:master_data'])->group(function () {
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
    });
});

require __DIR__ . '/auth.php';
