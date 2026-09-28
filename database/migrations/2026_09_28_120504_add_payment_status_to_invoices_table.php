<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->string('status_payment')->default('belum')->nullable()->after('terbilang');
            $table->date('tanggal_payment')->nullable()->after('status_payment');
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['status_payment', 'tanggal_payment']);
        });
    }
};
