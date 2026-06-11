<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Satuan;

class Aset extends Model
{
    protected $table = 'aset';
    protected $fillable = ['kategori_aset_id', 'nama', 'merk', 'qty', 'satuan_id', 'harga', 'qty_ecer', 'satuan_ecer_id', 'harga_ecer', 'keterangan', 'aktif', 'kode_gerobak'];

    protected static function booted()
    {
        static::creating(function ($aset) {
            $kategoriAsetGerobak = 3;

            if (intval($aset->kategori_aset_id) === $kategoriAsetGerobak) {
                $currentCount = Aset::where('kategori_aset_id', $kategoriAsetGerobak)->count();
                $nextNumber = $currentCount + 1;

                $aset->kode_gerobak = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);
            } else {
                $aset->kode_gerobak = null;
            }
        });
    }

    public function satuan()
    {
        return $this->belongsTo(Satuan::class, 'satuan_id');
    }

    public function satuan_ecer()
    {
        return $this->belongsTo(Satuan::class, 'satuan_ecer_id');
    }
}
