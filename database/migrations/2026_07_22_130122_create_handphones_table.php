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
        Schema::create('handphones', function (Blueprint $table) {
            $table->id();
            $table->string('brand'); // Contoh: Samsung, iPhone, Xiaomi
            $table->string('tipe');  // Contoh: Galaxy S23, iPhone 13
            $table->integer('harga_beli');
            $table->integer('harga_jual');
            $table->integer('stok');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('handphones');
    }
};
