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
        'is_locked',
    ];

    protected $casts = [
        'items' => 'array',
        'pembelian_data' => 'array',
        'tanggal' => 'date',
        'jatuh_tempo' => 'date',
        'tanggal_payment' => 'date',
        'is_locked' => 'boolean',
    ];

    public static function isLockedForPurchase(Pembelian $pembelian): bool
    {
        if (!empty($pembelian->no_invoice) && static::where('referensi', $pembelian->no_invoice)->where('is_locked', true)->exists()) {
            return true;
        }

        foreach (static::where('is_locked', true)->get(['pembelian_data']) as $invoice) {
            foreach ($invoice->pembelian_data ?? [] as $item) {
                if (isset($item['pembelian_id']) && (int) $item['pembelian_id'] === (int) $pembelian->id) {
                    return true;
                }
            }
        }

        return false;
    }
}
