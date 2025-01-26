<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BarangKeluar;
use App\Models\MutasiBarang;
use App\Models\StokBarang;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BarangKeluarController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $pagination = $request->pagination ?? 100;
        $search = $request->search ?? '';
        $startDate = $request->start_date ?? '';
        $endDate = $request->end_date ?? '';
        $kategori = $request->kategori ?? '';

        $data = BarangKeluar::when($search, function ($query) use ($search) {
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
        })->with('idBarang', 'idPelanggan')->paginate($pagination);

        return response()->json([
            'success' => true,
            'message' => 'List barang keluar',
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
            'id_pelanggan' => 'required|integer',
            'jumlah_keluar' => 'required|integer',
            'harga_jual' => 'required',
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



            // Update or Create StokBarang
            $stokBarang = StokBarang::where('id_barang', $request->id_barang)->first();

            if ($request->jumlah_keluar > $stokBarang->stok_akhir) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stok barang tidak terpenuhi',
                ], 500);
            }

            // Insert into BarangKeluar
            $barangKeluar = BarangKeluar::create([
                'id_barang' => $request->id_barang,
                'id_pelanggan' => $request->id_pelanggan,
                'jumlah_keluar' => $request->jumlah_keluar,
                'harga_jual' => $request->harga_jual,
                'total' => $request->jumlah_keluar * $request->harga_jual,
                'tanggal' => $request->tanggal,
            ]);

            if ($stokBarang) {
                $stokBarang->barang_keluar += $request->jumlah_keluar;
                $stokBarang->stok_akhir -= $request->jumlah_keluar;
                $stokBarang->save();
            } else {
                StokBarang::create([
                    'id_barang' => $request->id_barang,
                    'barang_masuk' => 0,
                    'barang_keluar' => $request->jumlah_keluar,
                    'stok_akhir' => -$request->jumlah_keluar,
                ]);
            }

            // Update or Create MutasiBarang
            $mutasiBarang = MutasiBarang::where('id_barang', $request->id_barang)
                ->where('tanggal', $request->tanggal)
                ->first();
            if ($mutasiBarang) {
                $mutasiBarang->barang_keluar += $request->jumlah_keluar;
                $mutasiBarang->save();
            } else {
                MutasiBarang::create([
                    'id_barang' => $request->id_barang,
                    'barang_masuk' => 0,
                    'barang_keluar' => $request->jumlah_keluar,
                    'tanggal' => $request->tanggal,
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Barang keluar berhasil disimpan',
                'data' => $barangKeluar
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Barang keluar gagal disimpan',
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
            $barangKeluar = BarangKeluar::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $barangKeluar
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Barang keluar tidak ditemukan',
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
            $barangKeluar = BarangKeluar::findOrFail($id);

            if (!$barangKeluar) {
                return response()->json([
                    'success' => false,
                    'message' => 'Barang keluar tidak ditemukan',
                ], 404);
            }

            $mutasiBarang = MutasiBarang::where('id_barang', $request->id_barang ?? $barangKeluar->id_barang)
                ->where('tanggal', $barangKeluar->tanggal)
                ->first();

            if ($mutasiBarang) {
                $mutasiBarang->update([
                    'barang_keluar' => $mutasiBarang->barang_keluar - $barangKeluar->jumlah_keluar,
                ]);

                if ($mutasiBarang->barang_keluar == 0 && $mutasiBarang->barang_masuk == 0) {
                    $mutasiBarang->delete();
                }
            }

            if ($request->tanggal && $request->tanggal != $barangKeluar->tanggal) {
                $mutasiBarangNewDate = MutasiBarang::where('id_barang', $request->id_barang ?? $barangKeluar->id_barang)
                    ->where('tanggal', $request->tanggal)
                    ->first();

                if ($mutasiBarangNewDate) {
                    $mutasiBarangNewDate->update([
                        'barang_keluar' => $mutasiBarangNewDate->barang_keluar + ($request->jumlah_keluar ?? $barangKeluar->jumlah_keluar),
                    ]);
                } else {
                    MutasiBarang::create([
                        'id_barang' => $request->id_barang ?? $barangKeluar->id_barang,
                        'barang_keluar' => $request->jumlah_keluar ?? $barangKeluar->jumlah_keluar,
                        'barang_masuk' => 0,
                        'tanggal' => $request->tanggal,
                    ]);
                }
            } else {
                MutasiBarang::create([
                    'id_barang' => $request->id_barang ?? $barangKeluar->id_barang,
                    'barang_keluar' => $request->jumlah_keluar ?? $barangKeluar->jumlah_keluar,
                    'barang_masuk' => 0,
                    'tanggal' => $request->tanggal ?? $barangKeluar->tanggal,
                ]);
            }

            // Update StokBarang
            $stokBarang = StokBarang::where('id_barang', $request->id_barang ?? $barangKeluar->id_barang)->first();
            if ($stokBarang) {
                $stokBarang->update([
                    'barang_keluar' => $stokBarang->barang_keluar - $barangKeluar->jumlah_keluar + ($request->jumlah_keluar ?? $barangKeluar->jumlah_keluar),
                    'stok_akhir' => $stokBarang->stok_akhir - $barangKeluar->jumlah_keluar + ($request->jumlah_keluar ?? $barangKeluar->jumlah_keluar)
                ]);
            } else {
                StokBarang::create([
                    'id_barang' => $request->id_barang ?? $barangKeluar->id_barang,
                    'barang_keluar' => $request->jumlah_keluar ?? $barangKeluar->jumlah_keluar,
                    'barang_masuk' => 0,
                    'stok_akhir' => $request->jumlah_keluar ?? $barangKeluar->jumlah_keluar
                ]);
            }

            $barangKeluar->update([
                'id_barang' => $request->id_barang ?? $barangKeluar->id_barang,
                'id_pelanggan' => $request->id_pelanggan ?? $barangKeluar->id_pelanggan,
                'jumlah_keluar' => $request->jumlah_keluar ?? $barangKeluar->jumlah_keluar,
                'harga_satuan' => $request->harga_satuan ?? $barangKeluar->harga_satuan,
                'total' => ($request->jumlah_keluar ?? $barangKeluar->jumlah_keluar) * ($request->harga_satuan ?? $barangKeluar->harga_satuan),
                'tanggal' => $request->tanggal ?? $barangKeluar->tanggal,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Barang keluar berhasil diperbarui',
                'data' => $barangKeluar
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Barang keluar gagal diperbarui',
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
            $barangKeluar = BarangKeluar::findOrFail($id);

            // Update StokBarang
            $stokBarang = StokBarang::where('id_barang', $barangKeluar->id_barang)->first();
            if ($stokBarang) {
                $stokBarang->update([
                    'barang_keluar' => $stokBarang->barang_keluar - $barangKeluar->jumlah_keluar,
                    'stok_akhir' => $stokBarang->stok_akhir + $barangKeluar->jumlah_keluar
                ]);
            }

            // Update MutasiBarang
            $mutasiBarang = MutasiBarang::where('id_barang', $barangKeluar->id_barang)
                ->where('tanggal', $barangKeluar->tanggal)
                ->first();
            if ($mutasiBarang) {
                $newBarangKeluar = $mutasiBarang->barang_keluar - $barangKeluar->jumlah_keluar;
                if ($newBarangKeluar == 0) {
                    $mutasiBarang->delete();
                } else {
                    $mutasiBarang->update([
                        'barang_keluar' => $newBarangKeluar,
                    ]);
                }
            }

            $barangKeluar->delete();

            return response()->json([
                'success' => true,
                'message' => 'Barang keluar berhasil dihapus'
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Barang keluar gagal dihapus',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
