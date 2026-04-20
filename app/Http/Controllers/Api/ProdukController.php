<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProdukController extends Controller
{
    /**
     * Ambil semua data produk beserta kategori dan stoknya
     */
    public function index()
    {
        // with(['kategori', 'stok']) itu Eager Loading biar gak lemot
        $produks = Produk::with(['kategori', 'stok' => function ($query) {
            $query->whereDate('created_at', date('Y-m-d'));
        }])->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar Data Produk',
            'data'    => $produks
        ], 200);
    }

    /**
     * Ambil detail satu produk berdasarkan ID
     */
    public function show($id)
    {
        $produk = Produk::with(['kategori', 'stok'])->find($id);

        if ($produk) {
            return response()->json([
                'success' => true,
                'message' => 'Detail Data Produk',
                'data'    => $produk
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Produk Tidak Ditemukan!',
        ], 404);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'kategori_id' => 'required|integer',
            'nama_produk' => 'required|string|max:255',
            'slug'        => 'required|string|max:255',
            'taste_note'  => 'nullable|string',
            'deskripsi'   => 'nullable|string',
            'hpp'         => 'required|integer',
            'margin'      => 'required|integer',
            'harga'       => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $produk = Produk::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil ditambahkan',
            'data'    => $produk
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $produk = Produk::find($id);

        if (!$produk) {
            return response()->json(['message' => 'Produk tidak ditemukan'], 404);
        }

        $produk->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Produk berhasil diupdate',
            'data'    => $produk
        ]);
    }
}
