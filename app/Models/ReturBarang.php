<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ReturBarang extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id',
        'user_id',
        'item_index',
        'nama_barang',
        'kuantitas',
        'harga_satuan',
        'nilai_retur',
        'nilai_modal',
        'alasan',
        'kondisi',
        'detail_imei',
        'tanggal_retur',
        'catatan',
    ];

    protected $casts = [
        'tanggal_retur' => 'date',
        'kuantitas' => 'integer',
        'harga_satuan' => 'decimal:2',
        'nilai_retur' => 'decimal:2',
        'nilai_modal' => 'decimal:2',
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
