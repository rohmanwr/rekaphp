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
        Schema::create('keuangans', function (Blueprint $table) {
            $table->id();
            $table->date('tanggal_input')->unique(); // 1 entri per hari
            // Disimpan dalam bentuk JSON agar fleksibel (nama bank & nominalnya)
            $table->json('tempat_aset')->nullable(); // e.g. {"Bank Jago": 120000000, "Bank BCA": 10000000}
            // Disimpan dalam bentuk JSON untuk daftar hutang
            $table->json('hutang')->nullable(); // e.g. {"Ibu": 10000000, "Bapa": 20000000}
            $table->decimal('total_bersih_aset', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('keuangans');
    }
};
