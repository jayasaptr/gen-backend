<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pelanggan extends Model
{
    //$table->string('nama');
    // $table->string('email');
    // $table->text('address');
    // $table->string('phone');

    protected $fillable = [
        'nama',
        'email',
        'address',
        'phone',
    ];
}
