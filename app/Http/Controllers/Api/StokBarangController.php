<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StokBarang;
use Illuminate\Http\Request;

class StokBarangController extends Controller
{
    public function index(Request $request)
    {
        $pagination = $request->pagination ?? 100;
        $search = $request->search ?? '';
        $stock_akhir = $request->stock_akhir; // Tidak memberikan default nilai kosong untuk mendeteksi null

        $stockBarang = StokBarang::with('idBarang')
            ->when($search, function ($query) use ($search) {
                // Filter berdasarkan nama barang
                $query->whereHas('idBarang', function ($query) use ($search) {
                    $query->where('nama', 'like', '%' . $search . '%');
                });
            })
            ->when(!is_null($stock_akhir), function ($query) use ($stock_akhir) {
                // Filter berdasarkan stok_akhir jika ada
                $query->where('stok_akhir', $stock_akhir);
            }, function ($query) {
                // Jika tidak ada filter stok_akhir, tampilkan stok_akhir > 0
                $query->where('stok_akhir', '>', 0);
            })
            ->paginate($pagination);

        return response()->json([
            'success' => true,
            'message' => 'List Stok Barang',
            'data' => $stockBarang
        ], 200);
    }
}
