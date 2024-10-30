<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangMasuk extends Model
{

    protected $fillable = [
        'id_barang',
        'id_pemasok',
        'jumlah_masuk',
        'harga_satuan',
        'total',
        'tanggal'
    ];

    public function idBarang()
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id');
    }

    public function idPemasok()
    {
        return $this->belongsTo(Pemasok::class, 'id_pemasok', 'id');
    }
}
