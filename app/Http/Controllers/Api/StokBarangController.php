<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Barang;
use App\Models\StokBarang;
use Illuminate\Http\Request;

class StokBarangController extends Controller
{
    public function index(Request $request)
    {
        $pagination = $request->pagination ?? 100;
        $search = $request->search ?? '';
        $stockAkhir = $request->stock_akhir;

        // Ambil data barang dengan stok dan filter berdasarkan stok_akhir jika diperlukan
        $data = Barang::with(['stokBarang' => function ($query) use ($stockAkhir) {
            if (isset($stockAkhir)) {
                if ($stockAkhir) {
                    $query->whereNotNull('stok_akhir'); // Ambil stok yang != null
                } else {
                    $query->where('stok_akhir', '=', 0); // Ambil stok yang == 0
                }
            }
        }])
            ->when($search, function ($query) use ($search) {
                $query->where('nama', 'like', '%' . $search . '%');
            })
            ->paginate($pagination);

        // Transformasi data untuk menambahkan stok_akhir, tanpa menyertakan stokBarang
        $data->getCollection()->transform(function ($barang) {
            $stok = $barang->stokBarang->stok_akhir ?? 0; // Set stok_akhir = 0 jika tidak ada stok
            unset($barang->stokBarang); // Hapus properti stokBarang
            $barang->stok_akhir = $stok; // Tambahkan properti stok_akhir
            return $barang;
        });

        return response()->json([
            'success' => true,
            'message' => 'List Barang dengan Stok Akhir',
            'data' => $data
        ], 200);
    }
}
