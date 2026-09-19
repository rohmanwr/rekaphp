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
        Schema::table('pembelians', function (Blueprint $table) {
            $table->date('tanggal_terbit')->nullable()->after('tanggal_beli');
            $table->string('no_invoice')->nullable()->after('tanggal_terbit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('nama_tabel_anda', function (Blueprint $table) {
            //
        });
    }
};
