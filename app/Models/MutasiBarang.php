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

    public function idBarang()
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id');
    }
}
