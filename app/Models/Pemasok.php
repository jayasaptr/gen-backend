<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pemasok extends Model
{
    protected $fillable = [
        'nama',
        'email',
        'phone',
        'address',
        'bank_type',
        'bank_number',
        'tax_number',
    ];

    public function barangMasuk()
    {
        return $this->hasMany(BarangMasuk::class, 'id_pemasok');
    }
}
