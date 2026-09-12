<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Ubah data lama yang berisi string file biasa menjadi format array JSON valid
        // Contoh: "folder/file.jpg" diubah jadi ["folder/file.jpg"]
        DB::statement("
            UPDATE pembelians 
            SET file_lampiran = JSON_ARRAY(file_lampiran) 
            WHERE file_lampiran IS NOT NULL 
              AND file_lampiran != '' 
              AND file_lampiran NOT LIKE '[%'
        ");

        // 2. Set nilai string kosong ('') menjadi NULL agar tidak menyebabkan error JSON
        DB::statement("
            UPDATE pembelians 
            SET file_lampiran = NULL 
            WHERE file_lampiran = ''
        ");

        // 3. Setelah datanya valid, baru ubah tipe kolom menjadi JSON
        Schema::table('pembelians', function (Blueprint $table) {
            $table->json('file_lampiran')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('pembelians', function (Blueprint $table) {
            $table->string('file_lampiran')->nullable()->change();
        });
    }
};
