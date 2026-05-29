<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Produk;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class KategoriController extends Controller
{
    public function index()
    {
        // with(['kategori', 'stok']) itu Eager Loading biar gak lemot
        $kategori = Kategori::where(['aktif' => true])->orderBy('prioritas', 'asc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar Kategori Menu',
            'data'    => $kategori
        ], 200);
    }

    public function store(Request $request)
    {
        // Validasi input untuk API
        $validator = Validator::make($request->all(), [
            'nama_kategori' => 'required|string|max:100|unique:kategori,nama_kategori',
            'slug' => 'required|string|max:100|unique:kategori,slug'
        ], [
            'nama_kategori.required' => 'Nama Kategori Menu wajib diisi.',
            'nama_kategori.unique'   => 'Nama Kategori Menu sudah ada di database.',
        ]);

        // Jika validasi gagal, kembalikan response error 422
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422); // 422 Unprocessable Entity
        }

        try {
            DB::transaction(function () use ($request) {
                // Simpan ke database
                $kategori = Kategori::create($request->all());
                UserLog::simpan("Membuat Kategori Menu baru dengan nama {$kategori->nama_kategori}", $kategori);
            });

            return response()->json([
                'success' => true,
                'message' => 'Data Kategori Menu berhasil ditambahkan'
            ], 201); // 201 Created
        } catch (\Exception $th) {
            return response()->json([
                'success'  => false,
                'message' => 'Gagal tambah Kategori Menu. Transaksi dibatalkan secara otomatis.',
                'error'   => $th->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $kategori = Kategori::find($id);

        if (!$kategori) {
            return response()->json([
                'success' => false,
                'message' => 'Data Kategori Menu tidak ditemukan'
            ], 404); // 404 Not Found
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail Kategori Menu ditemukan',
            'data'    => $kategori
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $kategori = Kategori::find($id);

        if (!$kategori) {
            return response()->json([
                'success' => false,
                'message' => 'Data Kategori Menu tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama_kategori' => 'sometimes|required|string|max:100|unique:kategori,nama_kategori',
            'slug' => 'sometimes|required|string|max:100|unique:kategori,slug'
        ], [
            'nama_kategori.required' => 'Nama Kategori Menu wajib diisi.',
            'nama_kategori.unique'   => 'Nama Kategori Menu sudah ada di database.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            DB::transaction(function () use ($kategori, $request) {
                $kategoriLama = $kategori;

                $dataUpdate = $request->all();
                if ($request->has('aktif')) {
                    $dataUpdate['aktif'] = $request->boolean('aktif');
                }
                // Jalankan update
                $kategori->update($dataUpdate);
                UserLog::simpan("Mengubah data Kategori Menu", ["semula" => $kategoriLama, "menjadi" => $kategori]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Data Kategori Menu berhasil diupdate'
            ], 200);
        } catch (\Exception $th) {
            return response()->json([
                'success'  => false,
                'message' => 'Gagal update Kategori Menu. Transaksi dibatalkan secara otomatis.',
                'error'   => $th->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $kategori = Kategori::find($id);
        $produkByKategori = Produk::where(['kategori_id' => $id])->get();

        if (!$kategori) {
            return response()->json([
                'success' => false,
                'message' => 'Data Kategori Menu tidak ditemukan'
            ], 404);
        }

        if ($produkByKategori) {
            return response()->json([
                'success' => false,
                'message' => 'Masih ada Produk yang menggunakan Kategori Menu ini'
            ], 404);
        }

        try {
            DB::transaction(function () use ($kategori) {
                $kategoriLama = $kategori;
                // Jalankan delete
                $kategori->delete();
                UserLog::simpan("Menghapus data Kategori Menu", ["semula" => $kategoriLama, "menjadi" => []]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Data Kategori Menu berhasil dihapus'
            ], 200);
        } catch (\Exception $th) {
            return response()->json([
                'success'  => false,
                'message' => 'Gagal hapus Kategori Menu. Transaksi dibatalkan secara otomatis.',
                'error'   => $th->getMessage()
            ], 500);
        }
    }
}
