<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Barang extends Model
{
    protected $fillable = [
        'kode',
        'nama',
        'kategori',
        'satuan',
        'stock_awal',
        'harga_beli',
        'harga_jual',
    ];
}