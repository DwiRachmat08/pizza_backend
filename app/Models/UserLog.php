<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class UserLog extends Model
{
    protected $table = 'user_log';
    protected $fillable = ['user_id', 'ip_address', 'aktivitas', 'perubahan'];

    public static function simpan($aktivitas, $detail = null)
    {
        return self::create([
            'user_id'          => Auth::id() ?? 1,
            'ip_address'       => request()->ip(),
            'aktivitas'        => $aktivitas,
            'perubahan'        => $detail ? json_encode($detail) : null,
        ]);
    }
}
