<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;
use App\Models\Aset;

class LokasiSeller extends Model
{
    protected $table = 'lokasi_seller';
    protected $fillable = ['gerobak_id', 'seller_id', 'provinsi_id', 'kota_id', 'kecamatan_id', 'kelurahan_id'];

    public function users()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function gerobak()
    {
        return $this->belongsTo(Aset::class, 'gerobak_id');
    }
}
