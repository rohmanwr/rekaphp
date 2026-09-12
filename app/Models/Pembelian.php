<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $casts = [
        'file_lampiran' => 'array',
    ];

    public function penjualan()
    {
        return $this->hasOne(Penjualan::class, 'pembelian_id');
    }
}
