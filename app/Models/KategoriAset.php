<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriAset extends Model
{
    protected $table = 'kategori_aset';
    protected $fillable = ['nama', 'slug', 'aktif'];
}
