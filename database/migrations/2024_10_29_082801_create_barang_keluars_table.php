<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('barang_keluars', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_barang')->constrained('barangs')->onDelete('cascade');
            $table->foreignId('id_pelanggan')->constrained('pelanggans')->onDelete('cascade');
            $table->integer('jumlah_keluar');
            $table->decimal('harga_jual', 30, 2);
            $table->decimal('total', 30, 2);
            $table->date('tanggal');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang_keluars');
    }
};
