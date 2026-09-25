<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keuangan extends Model
{
    use HasFactory;

    protected $table = 'keuangans';

    protected $fillable = [
        'tanggal_input',
        'tempat_aset',
        'hutang',
        'total_bersih_aset',
    ];

    protected $casts = [
        'tempat_aset' => 'array',
        'hutang' => 'array',
    ];
}
