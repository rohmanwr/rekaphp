<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->string('referensi')->unique(); // Contoh: INV/00001
            $table->date('tanggal');
            $table->date('jatuh_tempo');
            $table->string('nama_pelanggan');
            $table->text('alamat_pelanggan')->nullable();
            $table->json('items'); // Menyimpan array barang yang dibeli (nama barang, imei, kuantitas, harga)
            $table->decimal('subtotal', 15, 2);
            $table->decimal('total', 15, 2);
            $table->string('bank_info')->default('BCA 7391441018 a.n Rochman Nursyaid');
            $table->string('terbilang');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
