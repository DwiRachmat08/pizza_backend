<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\Resep;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ResepController extends Controller
{
    public function getResepByIdProduk($id)
    {
        // Cari produknya sekalian muat (load) relasi reseps-nya
        $produk = Produk::with('resep')->find($id);

        // Jika id_produk tidak terdaftar di database
        if (!$produk) {
            return response()->json([
                'success'  => false,
                'message' => 'Produk tidak ditemukan.'
            ], 404);
        }

        return response()->json([
            'success'  => true,
            'message' => 'Berhasil mengambil detail produk beserta resepnya',
            'data'    => $produk
        ], 200);
    }

    public function storeBatch(Request $request)
    {
        // 1. Validasi Array Menggunakan Tanda Asterisk (*)
        $validator = Validator::make($request->all(), [
            'produk_id'                 => 'required|exists:produks,id',
            'resep'                     => 'required|array|min:1',
            'resep.*.aset_id'           => 'required|exists:aset,id',
            'resep.*.satuan_id'         => 'required|exists:satuan,id',
            'resep.*.qty'               => 'required|numeric',
            'resep.*.harga'             => 'required|numeric'
        ], [
            'resep.required'            => 'Daftar Resep tidak boleh kosong.',
            'resep.*.aset_id.exists'    => 'Aset yang dipilih tidak valid.',
            'resep.*.satuan_id.exists'  => 'Satuan yang dipilih tidak valid.'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            // 2. Jalankan Transaksi Otomatis
            DB::transaction(function () use ($request) {
                $produkId = $request->produk_id;
                $produkModel = Produk::findOrFail($produkId);
                $waktuSekarang = now(); // Menyiapkan timestamps manual

                // 3. Petakan (Mapping) data array dari frontend agar sesuai kolom database
                $dataInsert = [];
                $hpp = 0;
                foreach ($request->resep as $bahan) {
                    $dataInsert[] = [
                        'produk_id'      => $produkId,
                        'aset_id'        => $bahan['aset_id'],
                        'satuan_id'      => $bahan['satuan_id'],
                        'qty'            => $bahan['qty'],
                        'harga'          => $bahan['harga'],
                        'keterangan'     => $bahan['keterangan'],
                        'created_at'     => $waktuSekarang,
                        'updated_at'     => $waktuSekarang,
                    ];
                    $hpp += $bahan['harga'];
                }

                // Optional: Hapus resep lama jika menu ini mau di-update/reset total resepnya
                // Resep::where('id_produk', $produkId)->delete();

                // 4. EKSEKUSI BATCH INSERT (Satu Query untuk Semua Data)
                Resep::insert($dataInsert);

                $hppProduk = $produkModel->hpp;
                $marginProduk = $produkModel->margin;
                $hargaProduk = $produkModel->harga;

                $produkModel->hpp = $hpp;
                $produkModel->margin = $hargaProduk - $hpp;
                $produkModel->save();

                UserLog::simpan("Menambah resep untuk produk {$produkModel->nama}");
            });

            return response()->json([
                'success'  => true,
                'message' => 'Data resep menu berhasil disimpan!'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success'  => false,
                'message' => 'Gagal menyimpan resep. Transaksi dibatalkan.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $resepModel = Resep::find($id);
        $produkModel = Produk::find($resepModel->produk_id);

        if (!$resepModel || !$produkModel) {
            return response()->json([
                'success' => false,
                'message' => 'Data Resep/Produk tidak ditemukan'
            ], 404);
        }

        try {
            DB::transaction(function () use ($request, $id, $resepModel, $produkModel) {
                $resepModel->update($request->all());

                $hpp = Resep::where('produk_id', $produkModel->id)->sum('harga');

                $hargaProduk = $produkModel->harga;

                $produkModel->hpp = $hpp;
                $produkModel->margin = $hargaProduk - $hpp;
                $produkModel->save();

                UserLog::simpan("Mengubah data resep produk {$produkModel->nama}");
            });

            return response()->json([
                'success' => true,
                'message' => 'Data Resep berhasil diupdate',
                'data'    => $resepModel
            ], 200);
        } catch (\Exception $th) {
            return response()->json([
                'success'  => false,
                'message' => 'Gagal menyimpan resep. Transaksi dibatalkan.',
                'error'   => $th->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $resepModel = Resep::find($id);
        $produkModel = Produk::find($resepModel->produk_id);

        if (!$resepModel || !$produkModel) {
            return response()->json([
                'success' => false,
                'message' => 'Data Resep/Produk tidak ditemukan'
            ], 404);
        }

        try {
            // 2. Jalankan transaksi database
            DB::transaction(function () use ($resepModel, $produkModel) {
                $resepModel->delete();

                $hpp = Resep::where('produk_id', $produkModel->id)->sum('harga');

                $hargaProduk = $produkModel->harga;

                $produkModel->hpp = $hpp;
                $produkModel->margin = $hargaProduk - $hpp;
                $produkModel->save();

                UserLog::simpan("Hapus resep untuk produk {$produkModel->nama}");
            });

            $resepNewModel = Resep::where(['produk_id' => $produkModel->id])->get();
            return response()->json([
                'success'  => true,
                'message' => 'Aset berhasil dihapus dari resep menu.',
                'data'    => [
                    'produk' => $produkModel,
                    'resep' => $resepNewModel
                ]
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success'  => false,
                'message' => 'Gagal menghapus data aset dari resep menu.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }
}
