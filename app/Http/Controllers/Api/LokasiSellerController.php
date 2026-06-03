<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LokasiSeller;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LokasiSellerController extends Controller
{
    public function index()
    {
        $lokasiSeller = LokasiSeller::with('users', 'gerobak')->orderBy('nama', 'asc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar Lokasi Seller',
            'data'    => $lokasiSeller
        ], 200);
    }

    public function getSellerByLokasiId($id)
    {
        $getSeller = LokasiSeller::with('users', 'gerobak')->where('provinsi_id', $id)
            ->orWhere('kota_id', $id)
            ->orWhere('kecamatan_id', $id)
            ->orWhere('kelurahan_id', $id)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar Lokasi Seller',
            'data'    => $getSeller
        ], 200);
    }

    public function show($id)
    {
        $lokasiModel = LokasiSeller::with(['users', 'gerobak'])->find($id);

        if ($lokasiModel) {
            return response()->json([
                'success' => true,
                'message' => 'Detail Data Lokasi Seller',
                'data'    => $lokasiModel
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Lokasi Seller Tidak Ditemukan!',
        ], 404);
    }

    public function update(Request $request, $id)
    {
        $lokasiModel = LokasiSeller::with(['users', 'gerobak'])->find($id);

        if (!$lokasiModel) {
            return response()->json([
                'success' => false,
                'message' => 'Data Lokasi Seller tidak ditemukan'
            ], 404);
        }

        try {
            DB::transaction(function () use ($request, $id, $lokasiModel) {
                $lokasiLama = $lokasiModel;
                $lokasiModel->update($request->all());

                UserLog::simpan("Mengubah data lokasi seller", ["semula" => $lokasiLama, "menjadi" => $lokasiModel]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Data Lokasi Seller berhasil diupdate'
            ], 200);
        } catch (\Exception $th) {
            return response()->json([
                'success'  => false,
                'message' => 'Gagal menyimpan Lokasi Seller. Transaksi dibatalkan.',
                'error'   => $th->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $lokasiModel = LokasiSeller::with('users', 'gerobak')->find($id);

        if (!$lokasiModel) {
            return response()->json([
                'success' => false,
                'message' => 'Data Lokasi Seller tidak ditemukan'
            ], 404);
        }

        try {
            // 2. Jalankan transaksi database
            DB::transaction(function () use ($lokasiModel) {
                $lokasiLamaModel = $lokasiModel;
                $lokasiModel->delete();

                UserLog::simpan("Hapus Lokasi Seller untuk User {$lokasiLamaModel->users->nama} dan gerobak {$lokasiLamaModel->gerobak->nama}", ["semula" => $lokasiLamaModel, "menjadi" => $lokasiModel]);
            });

            return response()->json([
                'success'  => true,
                'message' => 'Lokasi Seller berhasil dihapus.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success'  => false,
                'message' => 'Gagal menghapus Lokasi Seller.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
