<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    /**
     * Ambil semua data produk beserta kategori dan stoknya
     */
    public function index()
    {
        // with(['kategori', 'stok']) itu Eager Loading biar gak lemot
        $produks = Produk::with(['kategori', 'stok'])->get();

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
}
