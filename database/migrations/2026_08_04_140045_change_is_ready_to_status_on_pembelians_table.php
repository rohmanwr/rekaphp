<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pembelians', function (Blueprint $table) {
            // Hapus kolom is_ready lama dan tambahkan kolom status baru
            $table->dropColumn('is_ready');
            $table->string('status')->default('Belum Ready')->after('total_modal');
        });
    }

    public function down(): void
    {
        Schema::table('pembelians', function (Blueprint $table) {
            $table->dropColumn('status');
            $table->boolean('is_ready')->default(0);
        });
    }
};
