<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Produk;
use App\Models\StokDetail;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class OrderController extends Controller
{
    public function checkoutPesanan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'gerobak_id' => 'required|integer',
            'penjual_id' => 'required|integer',
            'pembeli_id' => 'sometimes|integer',
            'metode_pembayaran' => 'required|string',
            'status_pembayaran' => 'required|string',
            'status_order' => 'required|string',
            'produk'    => 'required|array|min:1',
            'produk.*.produk_id'  => 'required|exists:produks,id',
            'produk.*.harga'  => 'required|numeric',
            'produk.*.qty'  => 'required|numeric',
            'produk.*.diskon'  => 'required|numeric',
            'produk.*.total'  => 'required|numeric',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        $validated = $validator->validated();
        $sekarang = date('Y-m-d');

        try {
            $orderTransaction = DB::transaction(function () use ($request) {

                // cek validasi produk
                $$saveOrder = [];
                $isSisaStokAman = true;
                $pesanSisaStokAman = '';
                foreach ($request['produk'] as $item) {
                    $produkModel = Produk::find($item['produk_id']);

                    $sisaStok = StokDetail::sisaStokLock(
                        $request['penjual_id'],
                        $produkModel->id
                    );

                    if ($sisaStok['stok_sisa'] < $item['qty']) {
                        $isSisaStokAman = false;
                        $pesanSisaStokAman = "Produk {$produkModel->nama_produk} telah habis.";
                    }
                }

                if ($isSisaStokAman) {
                    $grandDiskon = collect($request['produk'])->sum('diskon');
                    $grandTotal = collect($request['produk'])->sum('total');

                    $saveOrder = Order::create([
                        'gerobak_id'        => $request['gerobak_id'],
                        'penjual_id'        => $request['penjual_id'],
                        'pembeli_id'        => $request['pembeli_id'] ?? null,
                        'metode_pembayaran' => $request['metode_pembayaran'],
                        'tipe_pembayaran'   => $request['tipe_pembayaran'] ?? null,
                        'status_pembayaran' => $request['status_pembayaran'],
                        'status_penjual'    => null, // Default NULL
                        'status_order'      => $request['status_order'],
                        'lat_pembeli'       => $request['lat_pembeli'] ?? null,
                        'long_pembeli'      => $request['long_pembeli'] ?? null,
                        'alamat_pembeli'    => $request['alamat_pembeli'] ?? null,
                        'note_pembeli'      => $request['note_pembeli'] ?? null,
                        'total'             => $grandTotal - $grandDiskon,
                        'diskon'            => $grandDiskon,
                        'subtotal'          => $grandTotal,
                    ]);

                    $orderDetailsData = [];
                    foreach ($request['produk'] as $val) {
                        $orderDetailsData[] = [
                            'order_id'   => $saveOrder->id, // Mengunci Relasi ID hasil insert barusan
                            'produk_id'  => $val['produk_id'],
                            'harga'      => $val['harga'],
                            'qty'        => $val['qty'],
                            'diskon'     => $val['diskon'],
                            'total'      => $val['total'],
                            'created_at' => now(),
                            'updated_at' => now(),
                        ];
                    }

                    $saveOrder->orderDetail()->insert($orderDetailsData);

                    UserLog::simpan("Membuat pesanan baru dengan kode pesanan {$saveOrder->kode_pesanan}", $saveOrder);
                }

                $data = [
                    'isSisaStokAman' => $isSisaStokAman,
                    'pesanSisaStokAman' => $pesanSisaStokAman,
                    'saveOrder' => $saveOrder
                ];

                return $data;
            });

            return response()->json([
                'success'  => $orderTransaction['isSisaStokAman'],
                'message' => $orderTransaction['isSisaStokAman'] ? 'Checkout Pesanan berhasil.' : $orderTransaction['pesanSisaStokAman'],
                'data' => $orderTransaction['saveOrder']
            ], 200);
        } catch (\Exception $th) {
            return response()->json([
                'success'  => false,
                'message' => 'Gagal checkout pesanan. Transaksi dibatalkan.',
                'error'   => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $orderModel = Order::find($id);

        if (!$orderModel) {
            return response()->json([
                'success' => false,
                'message' => 'Data Pesanan tidak ditemukan'
            ], 404);
        }

        try {
            $transaction = DB::transaction(function () use ($request, $id, $orderModel) {
                $orderLama = $orderModel;
                $orderModel->update($request->all());

                UserLog::simpan("Mengubah data pesanan {$orderModel->kode_pesanan}", ["semula" => $orderLama, "menjadi" => $orderModel]);

                return $orderModel;
            });

            return response()->json([
                'success' => true,
                'message' => 'Data Pesanan berhasil diupdate',
                'data'    => $transaction
            ], 200);
        } catch (\Exception $th) {
            return response()->json([
                'success'  => false,
                'message' => 'Gagal update pesanan. Transaksi dibatalkan.',
                'error'   => $th->getMessage()
            ], 500);
        }
    }
}
