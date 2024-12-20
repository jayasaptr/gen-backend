<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\MutasiBarang;
use Illuminate\Http\Request;

class MutasiController extends Controller
{
    public function index(Request $request)
    {
        $pagination = $request->pagination ?? 100;
        $search = $request->search ?? '';
        $startDate = $request->start_date ?? '';
        $endDate = $request->end_date ?? '';
        $kategori = $request->kategori ?? '';

        $data = MutasiBarang::when($search, function ($query) use ($search) {
            $query->whereHas('idBarang', function ($query) use ($search) {
                $query->where('nama', 'like', '%' . $search . '%');
            });
        })->when($startDate, function ($query) use ($startDate) {
            $query->where('tanggal', '>=', $startDate);
        })->when($endDate, function ($query) use ($endDate) {
            $query->where('tanggal', '<=', $endDate);
        })->when($kategori, function ($query) use ($kategori) {
            $query->whereHas('idBarang', function ($query) use ($kategori) {
                $query->where('kategori', $kategori);
            });
        })->with('idBarang')->paginate($pagination);

        return response()->json([
            'success' => true,
            'message' => 'List mutasi barang',
            'data' => $data
        ], 200);
    }
}
