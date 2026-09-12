<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('pembelians', function (Blueprint $table) {
            $table->id();
            $table->string('kode_otomatis')->unique(); // Kode sistem (misal: TRX-BUY-20260722-0001)
            $table->string('kode_manual')->nullable();  // Kode/No. Resi/Invoice Manual dari Toko
            $table->string('nama_barang');
            $table->string('nama_toko');
            $table->string('via')->change();
            $table->date('tanggal_beli');
            $table->bigInteger('total_modal');
            $table->text('deskripsi_imei')->nullable();
            $table->boolean('is_ready')->default(false); // Checkbox/Tombol Status Ready
            $table->string('file_lampiran')->nullable(); // Path gambar/pdf bukti transaksi
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pembelians', function (Blueprint $table) {
            $table->enum('via', ['Tokopedia', 'Shopee', 'Lazada', 'TikTok', 'Lainnya'])->change();
        });
    }
};
