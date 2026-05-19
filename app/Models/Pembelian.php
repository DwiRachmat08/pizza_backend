<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pembelian extends Model
{
    protected $table = 'pembelian';
    protected $fillable = ['aset_id', 'satuan_id', 'qty', 'harga', 'keterangan'];
}
