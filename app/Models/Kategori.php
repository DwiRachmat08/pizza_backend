<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Produk;

class Kategori extends Model
{
    protected $table = 'kategori';
    protected $fillable = ['nama_kategori', 'slug', 'tipe_kategori', 'prioritas'];

    public function produks()
    {
        return $this->hasMany(Produk::class);
    }
}
