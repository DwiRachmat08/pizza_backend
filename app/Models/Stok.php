<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Produk;
use App\Models\Aset;

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
}
