<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LokasiSeller;
use Illuminate\Http\Request;

class LokasiSellerController extends Controller
{
    public function index()
    {
        $lokasiSeller = LokasiSeller::with('users', 'gerobak')->orderBy('nama', 'asc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar Lokasi Seller',
            'data'    => $lokasiSeller
        ], 200);
    }

    public function getSellerByLokasiId($id)
    {
        $getSeller = LokasiSeller::with('users', 'gerobak')->where('provinsi_id', $id)
            ->orWhere('kota_id', $id)
            ->orWhere('kecamatan_id', $id)
            ->orWhere('kelurahan_id', $id)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar Lokasi Seller',
            'data'    => $getSeller
        ], 200);
    }
}
