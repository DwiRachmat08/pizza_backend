<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cart extends Model
{
    protected $table = 'cart';
    protected $fillable = ['gerobak_id', 'penjual_id', 'pembeli_id', 'produk_id', 'qty'];

    public function penjual()
    {
        return $this->belongsTo(User::class, 'penjual_id');
    }

    public function pembeli()
    {
        return $this->belongsTo(User::class, 'pembeli_id');
    }

    public function gerobak()
    {
        return $this->belongsTo(Aset::class, 'gerobak_id');
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id');
    }
}
