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
        'subtotal',
        'total',
        'bank_info',
        'terbilang',
    ];

    protected $casts = [
        'items' => 'array',
    ];
}
