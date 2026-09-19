<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'referensi',
        'tanggal',
        'jatuh_tempo',
        'nama_pelanggan',
        'alamat_pelanggan',
        'items',
        'pembelian_data', // Ditambahkan agar data snapshot pembelian tersimpan dengan benar
        'subtotal',
        'total',
        'bank_info',
        'terbilang',
    ];

    protected $casts = [
        'items' => 'array',
        'pembelian_data' => 'array', // Ditambahkan agar otomatis dibaca sebagai array oleh Laravel
        'tanggal' => 'date',
        'jatuh_tempo' => 'date',
    ];
}
