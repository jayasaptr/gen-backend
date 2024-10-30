<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pemasok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class PemasokController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pagination = $request->pagination ?? 100;
        $search = $request->search ?? '';

        $user = Pemasok::where('nama', 'like', "%$search%")->paginate($pagination);

        return response()->json([
            'success' => true,
            'message' => 'List Pemasok',
            'data' => $user
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
        $validate = Validator::make($request->all(), [
            'nama' => 'required',
            'email' => 'required',
            'phone' => 'required',
            'address' => 'required',
            'bank_type' => 'required',
            'bank_number' => 'required',
            'tax_number' => 'required',
        ]);

        if ($validate->fails()) {
            return response()->json([
                'message' => $validate->errors()->first()
            ], 422);
        }

        $user = Pemasok::create([
            'nama' => $request->nama,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'bank_type' => $request->bank_type,
            'bank_number' => $request->bank_number,
            'tax_number' => $request->tax_number,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pemasok created',
            'data' => $user
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $pemasok = Pemasok::find($id);

        if (!$pemasok) {
            return response()->json([
                'success' => false,
                'message' => 'Pemasok not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail Pemasok',
            'user' => $pemasok,
        ], 200);
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
        $pemasok = Pemasok::find($id);

        if (!$pemasok) {
            return response()->json([
                'success' => false,
                'message' => 'Pemasok not found',
            ], 404);
        }

        $pemasok->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Pemasok Updated',
            'user' => $pemasok,
        ], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $pemasok = Pemasok::find($id);

        if (!$pemasok) {
            return response()->json([
                'success' => false,
                'message' => 'Pemasok not found',
            ], 404);
        }

        $pemasok->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pemasok Deleted',
        ], 200);
    }
}
