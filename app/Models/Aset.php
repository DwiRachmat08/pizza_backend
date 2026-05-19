<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Satuan;

class Aset extends Model
{
    protected $table = 'aset';
    protected $fillable = ['kategori_aset_id', 'nama', 'merk', 'qty', 'satuan_id', 'harga', 'keterangan'];

    public function satuan()
    {
        return $this->belongsTo(Satuan::class, 'satuan_id');
    }
}
