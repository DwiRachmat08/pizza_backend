<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    // protected $table = 'order';
    protected $fillable = ['gerobak_id', 'penjual_id', 'pembeli_id', 'kode_pesanan', 'no_antrian', 'metode_pembayaran', 'tipe_pembayaran', 'status_pembayaran', 'status_penjual', 'status_order', 'lat_pembeli', 'long_pembeli', 'alamat_pembeli', 'note_pembeli', 'total', 'diskon', 'subtotal', 'token_midtrans'];

    protected static function booted()
    {
        static::creating(function ($query) {
            // PPP[kode_gerobak][kode_penjual][tanggal][no_transaksi]
            $tanggal = date('dmY');
            $gerobakModel = Aset::find($query->gerobak_id);
            $penjualModel = User::find($query->penjual_id);
            $countTransaksiPenjual = Order::where('penjual_id', $query->penjual_id)->whereDate('created_at', date('Y-m-d'))->count();
            $no_transaksi = str_pad($countTransaksiPenjual + 1, 3, '0', STR_PAD_LEFT);

            $kode_pesanan = 'PPP' . $gerobakModel->kode_gerobak . $penjualModel->kode_penjual . $tanggal . $no_transaksi;

            $query->kode_pesanan = $kode_pesanan;
            $query->no_antrian = $no_transaksi;
        });
    }

    public function gerobak()
    {
        return $this->belongsTo(Aset::class, 'gerobak_id');
    }

    public function penjual()
    {
        return $this->belongsTo(User::class, 'penjual_id');
    }

    public function pembeli()
    {
        return $this->belongsTo(User::class, 'pembeli_id');
    }

    public function orderDetail()
    {
        return $this->hasMany(OrderDetail::class, 'order_id', 'id');
    }
}
