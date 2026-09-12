<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Penjualan extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_penjualan',
        'pembelian_id',
        'nama_pembeli',
        'no_hp_pembeli',
        'tanggal_jual',
        'harga_jual',
        'via_jual',
        'catatan',
    ];

    public function pembelian()
    {
        return $this->belongsTo(Pembelian::class, 'pembelian_id');
    }
}
