<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('penjualans', function (Blueprint $table) {
            $table->id();
            $table->string('kode_penjualan')->unique();
            $table->foreignId('pembelian_id')->constrained('pembelians')->onDelete('cascade');
            $table->string('nama_pembeli');
            $table->string('no_hp_pembeli')->nullable();
            $table->date('tanggal_jual');
            $table->decimal('harga_jual', 15, 2);
            $table->string('via_jual');
            $table->text('catatan')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('penjualans');
    }
};
