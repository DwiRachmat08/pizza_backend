<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Kategori;
use App\Models\Stok;
use App\Models\Resep;

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
        return $this->hasOne(Stok::class, 'produk_id');
    }

    public function resep()
    {
        return $this->hasMany(Resep::class, 'produk_id', 'id');
    }
}
