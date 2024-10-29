<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BarangMasuk extends Model
{
    //$table->foreignId('id_barang')->constrained('barangs')->onDelete('cascade');
    // $table->foreignId('id_pemasok')->constrained('pemasoks')->onDelete('cascade');
    // $table->integer('jumlah_masuk');
    // $table->decimal('harga_satuan', 10, 2);
    // $table->decimal('total', 10, 2);
    // $table->date('tanggal');

    protected $fillable = [
        'id_barang',
        'id_pemasok',
        'jumlah_masuk',
        'harga_satuan',
        'total',
        'tanggal'
    ];
}
