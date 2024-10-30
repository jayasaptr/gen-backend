<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangKeluar extends Model
{
    protected $fillable = [
        'id_barang',
        'id_pelanggan',
        'jumlah_keluar',
        'harga_jual',
        'total',
        'tanggal'
    ];

    public function idBarang()
    {
        return $this->belongsTo(Barang::class, 'id_barang', 'id');
    }

    public function idPelanggan()
    {
        return $this->belongsTo(Pelanggan::class, 'id_pelanggan', 'id');
    }
}
