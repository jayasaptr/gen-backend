<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BarangMasuk;
use App\Models\MutasiBarang;
use App\Models\StokBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BarangMasukController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pagination = $request->pagination ?? 100;
        $search = $request->search ?? '';
        $startDate = $request->start_date ?? '';
        $endDate = $request->end_date ?? '';
        $kategori = $request->kategori ?? '';

        $data = BarangMasuk::when($search, function ($query) use ($search) {
            $query->whereHas('idBarang', function ($query) use ($search) {
                $query->where('nama', 'like', '%' . $search . '%');
            });
        })->when($startDate, function ($query) use ($startDate) {
            $query->where('tanggal', '>=', $startDate);
        })->when($endDate, function ($query) use ($endDate) {
            $query->where('tanggal', '<=', $endDate);
        })->when($kategori, function ($query) use ($kategori) {
            $query->whereHas('idBarang', function ($query) use ($kategori) {
                $query->where('category', $kategori);
            });
        })->with('idBarang', 'idPemasok')->paginate($pagination);

        return response()->json([
            'success' => true,
            'message' => 'List barang masuk',
            'data' => $data
        ], 200);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_barang' => 'required|integer',
            'id_pemasok' => 'required|integer',
            'jumlah_masuk' => 'required|integer',
            'harga_satuan' => 'required',
            'tanggal' => 'required|date',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Insert into BarangMasuk
            $barangMasuk = BarangMasuk::create([
                'id_barang' => $request->id_barang,
                'id_pemasok' => $request->id_pemasok,
                'jumlah_masuk' => $request->jumlah_masuk,
                'harga_satuan' => $request->harga_satuan,
                'total' => $request->jumlah_masuk * $request->harga_satuan,
                'tanggal' => $request->tanggal,
            ]);

            // Update or Create StokBarang
            $stokBarang = StokBarang::where('id_barang', $request->id_barang)->first();
            if ($stokBarang) {
                $stokBarang->barang_masuk += $request->jumlah_masuk;
                $stokBarang->stok_akhir += $request->jumlah_masuk;
                $stokBarang->save();
            } else {
                StokBarang::create([
                    'id_barang' => $request->id_barang,
                    'barang_masuk' => $request->jumlah_masuk,
                    'barang_keluar' => 0,
                    'stok_akhir' => $request->jumlah_masuk,
                ]);
            }

            // Update or Create MutasiBarang
            $mutasiBarang = MutasiBarang::where('id_barang', $request->id_barang)
                ->where('tanggal', $request->tanggal)
                ->first();
            if ($mutasiBarang) {
                $mutasiBarang->barang_masuk += $request->jumlah_masuk;
                $mutasiBarang->save();
            } else {
                MutasiBarang::create([
                    'id_barang' => $request->id_barang,
                    'barang_masuk' => $request->jumlah_masuk,
                    'barang_keluar' => 0,
                    'tanggal' => $request->tanggal,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Barang masuk berhasil disimpan',
                'data' => $barangMasuk
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Barang masuk gagal disimpan',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $barangMasuk = BarangMasuk::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $barangMasuk
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Barang masuk tidak ditemukan',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        try {
            $barangMasuk = BarangMasuk::findOrFail($id);

            dd($barangMasuk);

            if (!$barangMasuk) {
                return response()->json([
                    'success' => false,
                    'message' => 'Barang masuk tidak ditemukan',
                ], 404);
            }

            $mutasiBarang = MutasiBarang::where('id_barang', $request->id_barang ?? $barangMasuk->id_barang)
                ->where('tanggal', $barangMasuk->tanggal)
                ->first();

            if ($mutasiBarang) {
                $mutasiBarang->update([
                    'barang_masuk' => $mutasiBarang->barang_masuk - $barangMasuk->jumlah_masuk,
                ]);

                if ($mutasiBarang->barang_masuk == 0) {
                    $mutasiBarang->delete();
                }
            }

            if ($request->tanggal && $request->tanggal != $barangMasuk->tanggal) {
                $mutasiBarangNewDate = MutasiBarang::where('id_barang', $request->id_barang ?? $barangMasuk->id_barang)
                    ->where('tanggal', $request->tanggal)
                    ->first();

                if ($mutasiBarangNewDate) {
                    $mutasiBarangNewDate->update([
                        'barang_masuk' => $mutasiBarangNewDate->barang_masuk + ($request->jumlah_masuk ?? $barangMasuk->jumlah_masuk),
                    ]);
                } else {
                    MutasiBarang::create([
                        'id_barang' => $request->id_barang ?? $barangMasuk->id_barang,
                        'barang_masuk' => $request->jumlah_masuk ?? $barangMasuk->jumlah_masuk,
                        'barang_keluar' => 0,
                        'tanggal' => $request->tanggal,
                    ]);
                }
            } else {
                MutasiBarang::create([
                    'id_barang' => $request->id_barang ?? $barangMasuk->id_barang,
                    'barang_masuk' => $request->jumlah_masuk ?? $barangMasuk->jumlah_masuk,
                    'barang_keluar' => 0,
                    'tanggal' => $request->tanggal ?? $barangMasuk->tanggal,
                ]);
            }

            // Update StokBarang
            $stokBarang = StokBarang::where('id_barang', $request->id_barang ?? $barangMasuk->id_barang)->first();
            if ($stokBarang) {
                $stokBarang->update([
                    'barang_masuk' => $stokBarang->barang_masuk - $barangMasuk->jumlah_masuk + ($request->jumlah_masuk ?? $barangMasuk->jumlah_masuk),
                    'stok_akhir' => $stokBarang->stok_akhir - $barangMasuk->jumlah_masuk + ($request->jumlah_masuk ?? $barangMasuk->jumlah_masuk)
                ]);
            } else {
                StokBarang::create([
                    'id_barang' => $request->id_barang ?? $barangMasuk->id_barang,
                    'barang_masuk' => $request->jumlah_masuk ?? $barangMasuk->jumlah_masuk,
                    'barang_keluar' => 0,
                    'stok_akhir' => $request->jumlah_masuk ?? $barangMasuk->jumlah_masuk
                ]);
            }

            $barangMasuk->update([
                'id_barang' => $request->id_barang ?? $barangMasuk->id_barang,
                'id_pemasok' => $request->id_pemasok ?? $barangMasuk->id_pemasok,
                'jumlah_masuk' => $request->jumlah_masuk ?? $barangMasuk->jumlah_masuk,
                'harga_satuan' => $request->harga_satuan ?? $barangMasuk->harga_satuan,
                'total' => ($request->jumlah_masuk ?? $barangMasuk->jumlah_masuk) * ($request->harga_satuan ?? $barangMasuk->harga_satuan),
                'tanggal' => $request->tanggal ?? $barangMasuk->tanggal,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Barang masuk berhasil diperbarui',
                'data' => $barangMasuk
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Barang masuk gagal diperbarui',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        try {
            $barangMasuk = BarangMasuk::findOrFail($id);

            // Update StokBarang
            $stokBarang = StokBarang::where('id_barang', $barangMasuk->id_barang)->first();
            if ($stokBarang) {
                $stokBarang->update([
                    'barang_masuk' => $stokBarang->barang_masuk - $barangMasuk->jumlah_masuk,
                    'stok_akhir' => $stokBarang->stok_akhir - $barangMasuk->jumlah_masuk
                ]);
            }

            // Update MutasiBarang
            $mutasiBarang = MutasiBarang::where('id_barang', $barangMasuk->id_barang)
                ->where('tanggal', $barangMasuk->tanggal)
                ->first();
            if ($mutasiBarang) {
                $newBarangMasuk = $mutasiBarang->barang_masuk - $barangMasuk->jumlah_masuk;
                if ($newBarangMasuk == 0) {
                    $mutasiBarang->delete();
                } else {
                    $mutasiBarang->update([
                        'barang_masuk' => $newBarangMasuk,
                    ]);
                }
            }

            $barangMasuk->delete();

            return response()->json([
                'success' => true,
                'message' => 'Barang masuk berhasil dihapus'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Barang masuk gagal dihapus',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
