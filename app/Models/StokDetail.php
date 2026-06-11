<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Stok;
use App\Models\Produk;
use Illuminate\Support\Facades\DB;

class StokDetail extends Model
{
    protected $table = 'stok_detail';
    protected $fillable = ['stok_id', 'produk_id', 'qty'];

    public function stok()
    {
        return $this->belongsTo(Stok::class);
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }

    public function orderDetails()
    {
        return $this->hasMany(OrderDetail::class, 'produk_id', 'produk_id');
    }

    public static function sisaStokLock($penjualId, $produkId, $tanggal = null)
    {
        $targetTanggal = $tanggal ?? date('Y-m-d');

        $stokDetail = self::join('stoks', 'stok_detail.stok_id', '=', 'stoks.id')
            ->where('stoks.penjual_id', $penjualId)
            ->whereDate('stoks.tanggal', $targetTanggal)
            ->where('stok_detail.produk_id', $produkId)
            ->select('stok_detail.*')
            ->lockForUpdate()
            ->first();

        $qtyTerjual = DB::table('order_detail')
            ->join('order', 'order_detail.order_id', '=', 'order.id')
            ->where('order.penjual_id', $penjualId)
            ->where('order_detail.produk_id', $produkId)
            ->whereDate('order.created_at', $targetTanggal)
            ->where('order.status_pembayaran', 'Pembayaran Berhasil')
            ->whereIn('order.status_order', ['Pending', 'Selesai'])
            ->lockForUpdate() // Mengunci baris data orderan yang sedang dihitung
            ->sum('order_detail.qty');

        // 3. Kalkulasi Sisa Stok
        $stokAwal = $stokDetail->qty ?? 0;
        $sisaStok = $stokAwal - $qtyTerjual;

        $data = [
            'stok_awal' => $stokAwal,
            'stok_terjual' => $qtyTerjual,
            'stok_sisa' => $sisaStok
        ];

        return $data;
    }
}
