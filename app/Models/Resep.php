<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Produk;
use App\Models\Aset;
use App\Models\Satuan;

class Resep extends Model
{
    protected $table = 'resep';
    protected $fillable = ['produk_id', 'bahan_id', 'satuan_id', 'qty', 'harga', 'keterangan'];

    public function produk()
    {
        return $this->belongsTo(Produk::class, 'produk_id', 'id');
    }

    public function aset()
    {
        return $this->belongsTo(Aset::class, 'aset_id', 'id');
    }

    public function satuan()
    {
        return $this->belongsTo(Satuan::class, 'satuan_id', 'id');
    }
}
