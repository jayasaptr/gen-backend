<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangKeluar extends Model
{
    protected $fillable = [
        'id_barang',
        'id_pemasok',
        'jumlah_keluar',
        'harga_jual',
        'total',
        'tanggal'
    ];
}
