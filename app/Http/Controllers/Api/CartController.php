<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\Produk;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    public function getKeranjangPembeli()
    {
        $data = Cart::with('gerobak', 'penjual', 'produk')->where(['pembeli_id' => Auth::id()])->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar Keranjang',
            'data'    => $data
        ], 200);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gerobak_id'    => 'required|integer',
            'penjual_id'    => 'required|integer',
            'pembeli_id'    => 'required|integer',
            'produk_id'     => 'required|integer',
            'qty'           => 'required|integer',
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
                $produkModel = Produk::find($request->produk_id);

                // cek Apakah sudah ada
                $cekCart = Cart::where([
                    'gerobak_id' => $request->gerobak_id,
                    'penjual_id' => $request->penjual_id,
                    'pembeli_id' => $request->pembeli_id,
                    'produk_id' => $request->produk_id
                ])->first();

                if (!$cekCart) {
                    $cart = Cart::create($request->all());
                    UserLog::simpan("Menambahkan Produk {$produkModel->nama_produk} ke Keranjang", $cart);
                } else {
                    $cartLama = $cekCart;

                    $cekCart->update($request->all());
                    UserLog::simpan("Mengubah Keranjang", ["semula" => $cartLama, "menjadi" => $cekCart]);
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Produk berhasil ditambahkan'
            ], 201);
        } catch (\Exception $th) {
            return response()->json([
                'success'  => false,
                'message' => 'Gagal menyimpan ke Keranjang. Transaksi dibatalkan.',
                'error'   => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $cart = Cart::find($id);

        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Cart tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'gerobak_id'    => 'sometimes|required|integer',
            'penjual_id'    => 'sometimes|required|integer',
            'pembeli_id'    => 'sometimes|required|integer',
            'produk_id'     => 'sometimes|required|integer',
            'qty'           => 'sometimes|required|integer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            DB::transaction(function () use ($cart, $request) {
                $cartLama = $cart;

                $cart->update($request->all());
                UserLog::simpan("Mengubah Keranjang", ["semula" => $cartLama, "menjadi" => $cart]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Keranjang berhasil diupdate',
                'data'    => $cart
            ]);
        } catch (\Exception $th) {
            return response()->json([
                'success'  => false,
                'message' => 'Gagal update Keranjang. Transaksi dibatalkan secara otomatis.',
                'error'   => $th->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $cart = Cart::with('produk')->find($id);

        if (!$cart) {
            return response()->json([
                'success' => false,
                'message' => 'Data Keranjang tidak ditemukan'
            ], 404);
        }

        try {
            DB::transaction(function () use ($cart) {
                $namaLama = $cart->produk->nama_produk;
                $cart->delete();
                UserLog::simpan("Menghapus Keranjang dengan produk {$namaLama}");
            });

            return response()->json([
                'success' => true,
                'message' => 'Data Keranjang berhasil dihapus'
            ], 200);
        } catch (\Exception $th) {
            return response()->json([
                'success'  => false,
                'message' => 'Gagal menghapus keranjang. Transaksi dibatalkan.',
                'error'   => $th->getMessage()
            ], 500);
        }
    }
}
