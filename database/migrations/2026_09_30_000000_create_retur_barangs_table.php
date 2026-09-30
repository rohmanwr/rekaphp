<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('retur_barangs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('item_index');
            $table->string('nama_barang');
            $table->unsignedInteger('kuantitas');
            $table->decimal('harga_satuan', 15, 2);
            $table->decimal('nilai_retur', 15, 2);
            $table->decimal('nilai_modal', 15, 2);
            $table->string('alasan', 80);
            $table->string('kondisi', 40);
            $table->text('detail_imei')->nullable();
            $table->date('tanggal_retur');
            $table->text('catatan')->nullable();
            $table->timestamps();

            $table->index(['invoice_id', 'item_index']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('retur_barangs');
    }
};
