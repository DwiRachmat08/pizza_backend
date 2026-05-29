<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aset;
use App\Models\Resep;
use Illuminate\Http\Request;
use App\Models\Satuan;
use App\Models\UserLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class SatuanController extends Controller
{
    public function index()
    {
        // with(['kategori', 'stok']) itu Eager Loading biar gak lemot
        $satuans = Satuan::where(['aktif' => true])->orderBy('id', 'asc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar Master Satuan',
            'data'    => $satuans
        ], 200);
    }

    public function store(Request $request)
    {
        // Validasi input untuk API
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:100|unique:satuan,nama'
        ], [
            'nama.required' => 'Nama satuan wajib diisi.',
            'nama.unique'   => 'Nama satuan sudah ada di database.',
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
                $satuan = Satuan::create($request->all());
                UserLog::simpan("Menambah satuan baru dengan nama {$satuan->nama}");
            });

            return response()->json([
                'success' => true,
                'message' => 'Data Satuan berhasil ditambahkan'
            ], 201); // 201 Created
        } catch (\Exception $th) {
            return response()->json([
                'success'  => false,
                'message' => 'Gagal menyimpan satuan. Transaksi dibatalkan.',
                'error'   => $th->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $satuan = Satuan::find($id);

        if (!$satuan) {
            return response()->json([
                'success' => false,
                'message' => 'Data Satuan tidak ditemukan'
            ], 404); // 404 Not Found
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail Satuan ditemukan',
            'data'    => $satuan
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $satuan = Satuan::find($id);

        if (!$satuan) {
            return response()->json([
                'success' => false,
                'message' => 'Data Satuan tidak ditemukan'
            ], 404);
        }

        // Validasi, abaikan nama_satuan milik ID ini sendiri
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:100|unique:satuan,nama,' . $id
        ], [
            'nama.required' => 'Nama satuan wajib diisi.',
            'nama.unique'   => 'Nama satuan sudah ada di database.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            DB::transaction(function () use ($request, $satuan) {
                $namaLama = $satuan->nama;
                // Jalankan update
                $satuan->update($request->all());
                UserLog::simpan("Mengubah satuan dari {$namaLama} menjadi {$satuan->nama}", ["semula" => $namaLama, "menjadi" => $satuan->nama]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Data Satuan berhasil diupdate'
            ], 200);
        } catch (\Exception $th) {
            return response()->json([
                'success'  => false,
                'message' => 'Gagal menyimpan satuan. Transaksi dibatalkan.',
                'error'   => $th->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $satuan = Satuan::find($id);
        $asetBySatuan = Aset::where(['satuan_id' => $id])->orWhere(['satuan_ecer_id' => $id])->get();
        $resepBySatuan = Resep::where(['satuan_id' => $id])->get();

        if (!$satuan) {
            return response()->json([
                'success' => false,
                'message' => 'Data Satuan tidak ditemukan'
            ], 404);
        }

        if ($asetBySatuan || $resepBySatuan) {
            return response()->json([
                'success' => false,
                'message' => 'Masih ada Aset/Resep yang menggunakan Satuan ini'
            ], 404);
        }

        try {
            DB::transaction(function () use ($satuan) {
                $namaLama = $satuan->nama;
                $satuan->delete();
                UserLog::simpan("Menghapus satuan {$namaLama}");
            });

            return response()->json([
                'success' => true,
                'message' => 'Data Satuan berhasil dihapus'
            ], 200);
        } catch (\Exception $th) {
            return response()->json([
                'success'  => false,
                'message' => 'Gagal menghapus satuan. Transaksi dibatalkan.',
                'error'   => $th->getMessage()
            ], 500);
        }
    }
}
