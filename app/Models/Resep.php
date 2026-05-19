<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Resep extends Model
{
    protected $table = 'resep';
    protected $fillable = ['produk_id', 'bahan_id', 'satuan_id', 'qty', 'keterangan'];
}
