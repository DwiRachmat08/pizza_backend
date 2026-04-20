<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Stok;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StokController extends Controller
{
    public function index()
    {
        // with(['kategori', 'stok']) itu Eager Loading biar gak lemot
        $stoks = Stok::with(['users', 'produk'])->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar Data Stok',
            'data'    => $stoks
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'seller_id' => 'required|integer',
            'produk_id' => 'required|integer',
            'tanggal'   => 'required',
            'stok'      => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $stok = Stok::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Stok berhasil ditambahkan',
            'data'    => $stok
        ], 201);
    }
}
