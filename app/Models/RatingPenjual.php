<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RatingPenjual extends Model
{
    protected $table = 'rating_penjual';
    protected $fillable = ['pembeli_id', 'penjual_id', 'rating', 'pesan'];
}
