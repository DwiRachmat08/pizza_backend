<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderDetail extends Model
{
    protected $table = 'order_detail';
    protected $fillable = ['order_id', 'produk_id', 'harga', 'qty', 'diskon', 'total'];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
}
