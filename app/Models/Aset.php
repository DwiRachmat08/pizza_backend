<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Satuan;

class Aset extends Model
{
    protected $table = 'aset';
    protected $fillable = ['kategori_aset_id', 'nama', 'merk', 'qty', 'satuan_id', 'harga', 'qty_ecer', 'satuan_ecer_id', 'harga_ecer', 'keterangan', 'aktif'];

    public function satuan()
    {
        return $this->belongsTo(Satuan::class, 'satuan_id');
    }

    public function satuan_ecer()
    {
        return $this->belongsTo(Satuan::class, 'satuan_ecer_id');
    }
}
