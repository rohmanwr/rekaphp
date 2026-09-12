<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Handphone extends Model
{
    use HasFactory;
    protected $fillable = ['brand', 'tipe', 'harga_beli', 'harga_jual', 'stok'];
}
