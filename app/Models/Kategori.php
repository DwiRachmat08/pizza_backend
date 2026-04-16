<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class Kategori extends Model
{
    protected $fillable = ['nama_kategori', 'slug', 'tipe_kategori', 'prioritas'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }
}
