<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aset;
use App\Models\Pembelian;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class AsetController extends Controller
{
    public function index()
    {
        // with(['kategori', 'stok']) itu Eager Loading biar gak lemot
        $asets = Aset::with('satuan', 'satuan_ecer')->where(['aktif' => true])->orderBy('nama', 'asc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar Data Aset',
            'data'    => $asets
        ], 200);
    }

    public function store(Request $request)
    {
        // Validasi input untuk API
        $validator = Validator::make($request->all(), [
            'kategori_aset_id' => 'required|numeric',
            'merk' => 'required|string|max:100',
            'nama' => 'required|string|max:100',
            'qty' => 'required|numeric',
            'satuan_id' => 'required|numeric',
            'harga' => 'required|numeric',
            'qty_ecer' => 'required|numeric',
            'satuan_ecer_id' => 'required|numeric',
            'harga_ecer' => 'required|numeric'
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
            $simpanData = DB::transaction(function () use ($request) {
                // cek data aset
                $asetLama = Aset::where('nama', $request->nama)
                    ->where('merk', $request->merk)
                    ->where('harga', $request->harga)
                    ->first();

                if ($asetLama) {
                    // jika ada data, maka update data
                    $asetLama->qty = $asetLama->qty + $request->qty;
                    $asetLama->save();

                    $simpanAset = $asetLama;
                    $status = 'Stok Aset berhasil ditambahkan.';
                } else {
                    // jika tidak ada data, maka insert baru
                    $asetBaru = Aset::create($request->all());

                    $simpanAset = $asetBaru;
                    $status = 'Aset baru berhasil didaftarkan.';
                }

                // insert ke Pembelian
                $simpanPembelian = Pembelian::create([
                    'aset_id' => $simpanAset->id,
                    'satuan_id' => $request->satuan_id,
                    'qty' => $request->qty,
                    'harga' => $request->harga,
                    'keterangan' => $request->keterangan
                ]);
                UserLog::simpan("Menambah/membeli aset baru", ["nama" => $simpanAset->nama, "merk" => $simpanAset->merk, "harga" => $simpanAset->harga]);

                return [
                    'aset' => $simpanAset,
                    'pembelian' => $simpanPembelian,
                    'pesan_status' => $status
                ];
            });

            return response()->json([
                'success' => true,
                'message' => $simpanData['pesan_status'] . ' Beserta history pembeliannya!',
                'data'    => [
                    'aset' => $simpanData['aset'],
                    'pembelian' => $simpanData['pembelian']
                ]
            ], 201);
        } catch (\Exception $th) {
            //throw $th;
            return response()->json([
                'success'  => false,
                'message' => 'Gagal memproses data aset. Transaksi dibatalkan secara otomatis.',
                'error'   => $th->getMessage()
            ], 500);
        }
    }

    public function show($id)
    {
        $aset = Aset::with('satuan')->where(['id' => $id])->get();

        if (!$aset) {
            return response()->json([
                'success' => false,
                'message' => 'Data Aset tidak ditemukan'
            ], 404); // 404 Not Found
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail Aset ditemukan',
            'data'    => $aset
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $aset = Aset::find($id);

        if (!$aset) {
            return response()->json([
                'success' => false,
                'message' => 'Data Aset tidak ditemukan'
            ], 404);
        }

        // Validasi, abaikan nama_satuan milik ID ini sendiri
        $validator = Validator::make($request->all(), [
            'kategori_aset_id' => 'sometimes|required|numeric',
            'merk' => 'sometimes|required|string|max:100',
            'nama' => 'sometimes|required|string|max:100',
            'qty' => 'sometimes|required|numeric',
            'satuan_id' => 'sometimes|required|numeric',
            'harga' => 'sometimes|required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            DB::transaction(function () use ($request, $aset) {
                $asetLama = $aset;

                $dataUpdate = $request->all();
                if ($request->has('aktif')) {
                    $dataUpdate['aktif'] = $request->boolean('aktif');
                }

                // Jalankan update
                $aset->update($dataUpdate);

                UserLog::simpan("Mengubah data aset", [
                    "Semula" => $asetLama,
                    "menjadi" => $aset
                ]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Data Aset berhasil diupdate',
                'data'    => $aset
            ], 200);
        } catch (\Exception $th) {
            return response()->json([
                'success'  => false,
                'message' => 'Gagal update data aset. Transaksi dibatalkan secara otomatis.',
                'error'   => $th->getMessage()
            ], 500);
        }
    }

    public function getAsetByKategoriAset($id)
    {
        $asets = Aset::with('satuan', 'satuan_ecer')->where(['aktif' => true, 'kategori_aset_id' => $id])->orderBy('nama', 'asc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar Data Aset',
            'data'    => $asets
        ], 200);
    }
}
