<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StokBarang extends Model
{
    protected $fillable = [
        'id_barang',
        'barang_masuk',
        'barang_keluar',
        'stok_akhir'
    ];
}
