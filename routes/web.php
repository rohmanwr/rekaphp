<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RekapController;
use App\Http\Controllers\PenjualanController;
use App\Http\Controllers\PembelianController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TokoController;


Route::get('/', function () {
    return redirect()->route('login');
});

// Route yang wajib LOGIN
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard (Diberikan nama ganda agar aman untuk view manapun)
    Route::get('/dashboard', [RekapController::class, 'index'])->name('dashboard');
    Route::get('/rekap', [RekapController::class, 'index'])->name('rekap.index');

    // Rekap Pembelian
    Route::get('/pembelian', [PembelianController::class, 'index'])->name('pembelian.index');
    Route::get('/pembelian/create', [PembelianController::class, 'create'])->name('pembelian.create');
    Route::post('/pembelian', [PembelianController::class, 'store'])->name('pembelian.store');
    Route::put('/pembelian/{id}', [PembelianController::class, 'update'])->name('pembelian.update');
    Route::patch('/pembelian/{id}/status', [PembelianController::class, 'updateStatus'])->name('pembelian.updateStatus');
    Route::delete('/pembelian/{id}', [PembelianController::class, 'destroy'])->name('pembelian.destroy');

    // Route Master Data Barang
    Route::get('/barang', [BarangController::class, 'index'])->name('barang.index');
    Route::post('/barang', [BarangController::class, 'store'])->name('barang.store');
    Route::put('/barang/{id}', [BarangController::class, 'update'])->name('barang.update');
    Route::delete('/barang/{id}', [BarangController::class, 'destroy'])->name('barang.destroy');

    // Penjualan & Detail Barang
    Route::get('/penjualan/siap-jual', [PenjualanController::class, 'siapJual'])->name('penjualan.siap-jual');
    Route::get('/penjualan/detail-barang', [PenjualanController::class, 'detailBarang'])->name('penjualan.detail-barang');
    Route::put('/penjualan/update-detail-barang/{id}', [PenjualanController::class, 'updateDetailBarang'])->name('penjualan.update-detail-barang');
    Route::delete('/penjualan/destroy-detail-barang/{id}', [PenjualanController::class, 'destroyDetailBarang'])->name('penjualan.destroy-detail-barang');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Master Data Toko
    Route::get('/toko', [TokoController::class, 'index'])->name('toko.index');
    Route::post('/toko', [TokoController::class, 'store'])->name('toko.store');
    Route::put('/toko/{id}', [TokoController::class, 'update'])->name('toko.update');
    Route::delete('/toko/{id}', [TokoController::class, 'destroy'])->name('toko.destroy');

    Route::get('/pembelian/siap-jual', [PembelianController::class, 'barangSiapJual'])->name('pembelian.siap_jual');



    // Rute untuk menampilkan halaman/preview invoice dari barang yang dicentang
    Route::post('/pembelian/invoice/create', [PembelianController::class, 'createInvoice'])->name('invoice.create');

    // Rute untuk menyimpan invoice ke database
    Route::post('/pembelian/invoice/store', [PembelianController::class, 'storeInvoice'])->name('invoice.store');

    // Rute untuk melihat/mencetak invoice yang sudah jadi
    Route::get('/pembelian/invoice/{id}', [PembelianController::class, 'showInvoice'])->name('invoice.show');

    Route::prefix('penjualan')->name('penjualan.')->group(function () {
        Route::get('/siap-jual', [PenjualanController::class, 'siapJual'])->name('siap-jual');
        Route::get('/histori', [PenjualanController::class, 'historiPenjualan'])->name('histori'); // <-- Route Histori
        Route::get('/detail-barang', [PenjualanController::class, 'detailBarang'])->name('detail-barang');
        Route::put('/detail-barang/{id}', [PenjualanController::class, 'updateDetailBarang'])->name('update-detail-barang');
        Route::delete('/detail-barang/{id}', [PenjualanController::class, 'destroyDetailBarang'])->name('destroy-detail-barang');
        Route::post('/store', [PenjualanController::class, 'store'])->name('store');
    });
    Route::put('/penjualan/update-histori-item/{invoiceId}', [PenjualanController::class, 'updateHistoriItem'])->name('penjualan.update-histori-item');
});

require __DIR__ . '/auth.php';
