<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'kode_manual',
        'nama_alamat', // Tambahkan ini
        'kode_otomatis',
        'nama_barang',
        'nama_toko',
        'via',
        'tanggal_beli',
        'total_modal',
        'status',
        'deskripsi_imei',
        'detail_imei', // Tambahkan ini
        'file_lampiran',
    ];

    protected $casts = [
        'file_lampiran' => 'array',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
