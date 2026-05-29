<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Stok;
use App\Models\Produk;

class StokDetail extends Model
{
    protected $table = 'stok_detail';
    protected $fillable = ['stok_id', 'produk_id', 'stok'];

    public function stok()
    {
        return $this->belongsTo(Stok::class);
    }

    public function produk()
    {
        return $this->belongsTo(Produk::class);
    }
}
