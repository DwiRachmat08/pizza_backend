<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aset;
use App\Models\KategoriAset;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class KategoriAsetController extends Controller
{
    public function index()
    {
        // with(['kategori', 'stok']) itu Eager Loading biar gak lemot
        $kategoriAsets = KategoriAset::where(['aktif' => true])->orderBy('nama', 'asc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar Kategori Aset',
            'data'    => $kategoriAsets
        ], 200);
    }

    public function store(Request $request)
    {
        // Validasi input untuk API
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:100|unique:kategori_aset,nama',
            'slug' => 'required|string|max:100|unique:kategori_aset,slug'
        ], [
            'nama.required' => 'Nama Kategori Aset wajib diisi.',
            'nama.unique'   => 'Nama Kategori Aset sudah ada di database.',
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
                $kategoriAset = KategoriAset::create($request->all());
                UserLog::simpan("Membuat Kategori Aset baru dengan nama {$kategoriAset->nama}", $kategoriAset);
            });

            return response()->json([
                'success' => true,
                'message' => 'Data Kategori Aset berhasil ditambahkan'
            ], 201); // 201 Created
        } catch (\Exception $th) {
            return response()->json([
                'success'  => false,
                'message' => 'Gagal tambah Kategori Aset. Transaksi dibatalkan secara otomatis.',
                'error'   => $th->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $kategoriAset = KategoriAset::find($id);

        if (!$kategoriAset) {
            return response()->json([
                'success' => false,
                'message' => 'Data Kategori Aset tidak ditemukan'
            ], 404); // 404 Not Found
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail Kategori Aset ditemukan',
            'data'    => $kategoriAset
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $kategoriAset = KategoriAset::find($id);

        if (!$kategoriAset) {
            return response()->json([
                'success' => false,
                'message' => 'Data Kategori Aset tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'nama' => 'sometimes|required|string|max:100|unique:kategori_aset,nama',
            'slug' => 'sometimes|required|string|max:100|unique:kategori_aset,slug'
        ], [
            'nama.required' => 'Nama Kategori Aset wajib diisi.',
            'nama.unique'   => 'Nama Kategori Aset sudah ada di database.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            DB::transaction(function () use ($kategoriAset, $request) {
                $kategoriAsetLama = $kategoriAset;

                $dataUpdate = $request->all();
                if ($request->has('aktif')) {
                    $dataUpdate['aktif'] = $request->boolean('aktif');
                }
                // Jalankan update
                $kategoriAset->update($dataUpdate);
                UserLog::simpan("Mengubah data Kategori Aset", ["semula" => $kategoriAsetLama, "menjadi" => $kategoriAset]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Data Kategori Aset berhasil diupdate'
            ], 200);
        } catch (\Exception $th) {
            return response()->json([
                'success'  => false,
                'message' => 'Gagal update Kategori Aset. Transaksi dibatalkan secara otomatis.',
                'error'   => $th->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $kategoriAset = KategoriAset::find($id);
        $AsetByKategoriAset = Aset::where(['kategori_aset_id' => $id])->get();

        if (!$kategoriAset) {
            return response()->json([
                'success' => false,
                'message' => 'Data Kategori Aset tidak ditemukan'
            ], 404);
        }

        if ($AsetByKategoriAset) {
            return response()->json([
                'success' => false,
                'message' => 'Masih ada Aset yang menggunakan Kategori Aset ini'
            ], 404);
        }

        try {
            DB::transaction(function () use ($kategoriAset) {
                $kategoriAsetLama = $kategoriAset;
                // Jalankan delete
                $kategoriAset->delete();
                UserLog::simpan("Menghapus data Kategori Aset", ["semula" => $kategoriAsetLama, "menjadi" => []]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Data Kategori Aset berhasil dihapus'
            ], 200);
        } catch (\Exception $th) {
            return response()->json([
                'success'  => false,
                'message' => 'Gagal hapus Kategori Aset. Transaksi dibatalkan secara otomatis.',
                'error'   => $th->getMessage()
            ], 500);
        }
    }
}
