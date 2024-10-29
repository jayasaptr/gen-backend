<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MutasiBarang extends Model
{
    protected $fillable = [
        'id_barang',
        'barang_masuk',
        'barang_keluar',
        'tanggal'
    ];
}
