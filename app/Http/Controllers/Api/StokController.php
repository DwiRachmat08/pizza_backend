<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aset;
use App\Models\LokasiSeller;
use App\Models\Stok;
use App\Models\StokDetail;
use App\Models\User;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class StokController extends Controller
{
    public function index()
    {
        // with(['kategori', 'stok']) itu Eager Loading biar gak lemot
        $stoks = Stok::with(['users', 'gerobak'])->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar Data Stok',
            'data'    => $stoks
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gerobak_id' => 'required|integer',
            'penjual_id' => 'required|integer',
            'produk_id' => 'required|integer',
            'tanggal'   => 'required',
            'stok'      => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        $stok = Stok::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Stok berhasil ditambahkan',
            'data'    => $stok
        ], 201);
    }

    public function simpanBatch(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gerobak_id'                => 'required|exists:aset,id',
            'penjual_id'                 => 'required|exists:users,id',
            'tanggal'                   => 'required|date_format:Y-m-d',
            'lokasi'                    => 'required|array|min:1',
            'produk'                    => 'required|array|min:1',
            'produk.*.produk_id'        => 'required|exists:produks,id',
            'produk.*.stok'             => 'required|numeric',
        ], [
            'gerobak_id.required'        => 'Gerobak tidak boleh kosong.',
            'gerobak_id.exists'          => 'Gerobak yang dipilih tidak valid.',
            'penjual_id.required'         => 'Penjual tidak boleh kosong.',
            'penjual_id.exists'           => 'Penjual yang dipilih tidak valid.',
            'lokasi.required'            => 'Lokasi Seller tidak boleh kosong.',
            'produk.required'            => 'Daftar Produk tidak boleh kosong.',
            'produk.*.produk_id.exists'  => 'Produk yang dipilih tidak valid.',
            'produk.*.stok.exists'       => 'Jumlah stok tidak valid.'
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
                $waktuSekarang = now();
                $gerobakModel = Aset::find($request->gerobak_id);
                $sellerModel = User::find($request->penjual_id);

                $simpanStok = Stok::create([
                    'gerobak_id'    => $request->gerobak_id,
                    'penjual_id'     => $request->penjual_id,
                    'tanggal'       => date('Y-m-d', strtotime($request->tanggal))
                ]);

                $insertBatch = [];
                foreach ($request->produk as $p) {
                    $insertBatch[] = [
                        'stok_id'   => $simpanStok->id,
                        'produk_id' => $p->produk_id,
                        'stok'      => $p->stok,
                        'created_at'    => $waktuSekarang,
                        'updated_at'    => $waktuSekarang
                    ];
                }

                StokDetail::insert($insertBatch);

                $simpanLokasiSellerBatch = [];
                foreach ($request->lokasi as $l) {
                    $simpanLokasiSellerBatch[] = [
                        'gerobak_id'        => $request->gerobak_id,
                        'penjual_id'         => $request->penjual_id,
                        'tanggal'           => date('Y-m-d', strtotime($request->tanggal)),
                        'provinsi_id'       => $l->provinsi_id ?? null,
                        'kota_id'           => $l->kota_id ?? null,
                        'kecamatan_id'      => $l->kecamatan_id ?? null,
                        'kelurahan_id'      => $l->kelurahan_id ?? null,
                        'created_at'        => $waktuSekarang,
                        'updated_at'        => $waktuSekarang
                    ];
                }

                LokasiSeller::insert($simpanLokasiSellerBatch);
                UserLog::simpan("Menambah stok dan lokasi pada gerobak {$gerobakModel->nama} untuk seller {$sellerModel->name}");
            });

            return response()->json([
                'success'  => true,
                'message' => 'Data stok berhasil disimpan!'
            ], 201);
        } catch (\Exception $th) {
            return response()->json([
                'success'  => false,
                'message' => 'Gagal menyimpan stok. Transaksi dibatalkan.',
                'error'   => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $stok = Stok::find($id);

        if (!$stok) {
            return response()->json([
                'success' => false,
                'message' => 'Stok tidak ditemukan'
            ], 404);
        }

        try {
            DB::transaction(function () use ($stok, $request) {
                $stokLama = $stok;
                $stok->update($request->all());
                UserLog::simpan("Mengubah data stok", ["semula" => $stokLama, "menjadi" => $stok]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Stok berhasil diupdate',
                'data'    => $stok
            ]);
        } catch (\Exception $th) {
            return response()->json([
                'success'  => false,
                'message' => 'Gagal update stok. Transaksi dibatalkan.',
                'error'   => $th->getMessage()
            ], 500);
        }
    }

    public function getProdukByPenjual($id)
    {
        $stoks = Stok::withRealTimeStock($id)
            ->where('penjual_id', $id)
            ->whereDate('tanggal', date('Y-m-d'))
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar Data Stok',
            'data'    => $stoks
        ], 200);
    }
}
