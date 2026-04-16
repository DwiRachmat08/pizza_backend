<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Kategori;

class Product extends Model
{
    protected $fillable = [
        'ketegori_id',
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
}
