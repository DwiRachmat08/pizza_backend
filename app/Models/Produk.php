<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Kategori;
use App\Models\Stok;

class Produk extends Model
{
    protected $fillable = [
        'kategori_id',
        'nama_produk',
        'slug',
        'taste_note',
        'deskripsi',
        'hpp',
        'margin',
        'harga',
        'is_available'
    ];

    public function kategori()
    {
        return $this->belongsTo(Kategori::class);
    }

    public function stok()
    {
        // Karena satu produk punya satu baris stok
        return $this->hasOne(Stok::class, 'produk_id');
    }
}
