<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Konfigurasi;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use PHPUnit\Event\Test\Failed;

class KonfigurasiController extends Controller
{
    public function index()
    {
        $data = Konfigurasi::get();

        return response()->json([
            'success' => true,
            'message' => 'Data Konfigurasi',
            'data'    => $data
        ], 200);
    }

    public function store(Request $request)
    {
        // Validasi input untuk API
        $validator = Validator::make($request->all(), [
            'nama_app' => 'required|string|unique:konfigurasi,nama_app'
        ], [
            'nama_app.required' => 'Nama App wajib diisi.',
            'nama_app.unique'   => 'Nama App sudah ada di database.',
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
            $count = Konfigurasi::count();

            if ($count > 1) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data Konfigurasi sudah ada'
                ], 201); // 201 Created
            }

            DB::transaction(function () use ($request) {
                // Simpan ke database
                $konfigurasi = Konfigurasi::create($request->all());
                UserLog::simpan("Membuat Konfigurasi baru", $konfigurasi);
            });

            return response()->json([
                'success' => true,
                'message' => 'Data Konfigurasi berhasil ditambahkan'
            ], 201); // 201 Created
        } catch (\Exception $th) {
            return response()->json([
                'success'  => false,
                'message' => 'Gagal tambah Konfigurasi. Transaksi dibatalkan secara otomatis.',
                'error'   => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $konfigurasi = Konfigurasi::find($id);

        if (!$konfigurasi) {
            return response()->json([
                'success' => false,
                'message' => 'Data Konfigurasi tidak ditemukan'
            ], 404);
        }

        try {
            DB::transaction(function () use ($konfigurasi, $request) {
                $konfigurasiLama = $konfigurasi;

                $dataUpdate = $request->all();

                // Jalankan update
                $konfigurasi->update($dataUpdate);
                UserLog::simpan("Mengubah data Konfigurasi", ["semula" => $konfigurasiLama, "menjadi" => $konfigurasi]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Data Konfigurasi berhasil diupdate'
            ], 200);
        } catch (\Exception $th) {
            return response()->json([
                'success'  => false,
                'message' => 'Gagal update Konfigurasi. Transaksi dibatalkan secara otomatis.',
                'error'   => $th->getMessage()
            ], 500);
        }
    }
}
