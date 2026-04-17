<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Produk;

class Stok extends Model
{
    protected $fillable = ['user_id', 'produk_id', 'tanggal', 'stok'];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function produk()
    {
        return $this->hasMany(Produk::class);
    }
}
