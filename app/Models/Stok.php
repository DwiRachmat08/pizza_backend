<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Produk;
use App\Models\Aset;
use Illuminate\Database\Eloquent\Builder;

class Stok extends Model
{
    protected $fillable = ['gerobak_id', 'penjual_id', 'tanggal'];

    public function users()
    {
        return $this->belongsTo(User::class, 'penjual_id');
    }

    public function gerobak()
    {
        return $this->belongsTo(Aset::class, 'gerobak_id');
    }

    public function detail()
    {
        return $this->hasMany(StokDetail::class, 'stok_id');
    }

    public function scopeWithRealTimeStock(Builder $query, $penjualId, $tanggal = null)
    {

        $targetTanggal = $tanggal ?? date('Y-m-d');

        return $query->with([
            'users',
            'gerobak',
            'detail' => function ($subQuery) use ($penjualId, $targetTanggal) {
                $subQuery->withSum(['orderDetails as qty_terjual' => function ($q) use ($penjualId, $targetTanggal) {
                    $q->whereHas('order', function ($o) use ($penjualId, $targetTanggal) {
                        $o->where('penjual_id', $penjualId)
                            ->whereDate('created_at', $targetTanggal)
                            ->where(function ($query) {
                                $query->where('status_pembayaran', 'Pembayaran Berhasil')
                                    ->whereIn('status_order', ['Pending', 'Selesai']);
                            })
                            ->orWhere(function ($query) {
                                $query->where('status_pembayaran', 'Belum Melakukan Pembayaran')
                                    ->where('status_order', 'Pending')
                                    ->where('created_at', '>=', now()->subMinutes(15)); // Batas booking 15 menit
                            });
                    });
                }], 'qty');
            }
        ]);
    }
}
