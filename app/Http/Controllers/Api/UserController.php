<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function getPenjual()
    {
        // Mengambil user yang punya role dengan nama 'penjual'
        $penjual = User::whereHas('role', function ($query) {
            $query->where('slug', 'penjual');
        })->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar Semua Penjual',
            'data'    => $penjual
        ], 200);
    }
}
