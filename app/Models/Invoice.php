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
        'pembelian_data',
        'subtotal',
        'total',
        'terbilang',
        'status_payment',  // <-- WAJIB ADA
        'tanggal_payment', // <-- WAJIB ADA
    ];

    protected $casts = [
        'items' => 'array',
        'pembelian_data' => 'array',
        'tanggal' => 'date',
        'jatuh_tempo' => 'date',
        'tanggal_payment' => 'date',
    ];
}
