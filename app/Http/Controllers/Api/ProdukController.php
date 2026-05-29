<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            DB::transaction(function () use ($request) {
                $produk = Produk::create($request->all());
                UserLog::simpan("Menambahkan Produk baru {$produk->nama}", $produk);
            });

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil ditambahkan'
            ], 201);
        } catch (\Exception $th) {
            return response()->json([
                'success'  => false,
                'message' => 'Gagal tambah Produk. Transaksi dibatalkan secara otomatis.',
                'error'   => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $produk = Produk::find($id);

        if (!$produk) {
            return response()->json([
                'success' => false,
                'message' => 'Produk tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'kategori_id' => 'sometimes|required|integer',
            'nama_produk' => 'sometimes|required|string|max:255',
            'slug'        => 'sometimes|required|string|max:255',
            'taste_note'  => 'nullable|string',
            'deskripsi'   => 'nullable|string',
            'hpp'         => 'sometimes|required|integer',
            'margin'      => 'sometimes|required|integer',
            'harga'       => 'sometimes|required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            DB::transaction(function () use ($produk, $request) {
                $produkLama = $produk;
                $produk->update($request->all());
                UserLog::simpan("Mengubah Produk {$produk->nama}", ["semula" => $produkLama, "menjadi" => $produk]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil diupdate',
                'data'    => $produk
            ]);
        } catch (\Exception $th) {
            return response()->json([
                'success'  => false,
                'message' => 'Gagal update Produk. Transaksi dibatalkan secara otomatis.',
                'error'   => $th->getMessage()
            ], 500);
        }
    }
}
