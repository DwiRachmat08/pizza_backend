<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RatingProduk extends Model
{
    protected $table = 'rating_produk';
    protected $fillable = ['pembeli_id', 'produk_id', 'rating', 'pesan'];
}
