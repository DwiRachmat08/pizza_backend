<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'role_id'       => 'required|integer',
            'name'          => 'required|string|max:255',
            'email'         => 'required|string|max:255',
            'password'      => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            DB::transaction(function () use ($request) {
                $user = User::craete($request->all());
                UserLog::simpan("Menambahkan User baru {$user->nama}", $user);
            });

            return response()->json([
                'success' => true,
                'message' => 'User berhasil ditambahkan'
            ], 201);
        } catch (\Exception $th) {
            return response()->json([
                'success'  => false,
                'message' => 'Gagal tambah User. Transaksi dibatalkan secara otomatis.',
                'error'   => $th->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User tidak ditemukan'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'role_id'       => 'sometimes|required|integer',
            'name'          => 'sometimes|required|string|max:255',
            'email'         => 'sometimes|required|string|max:255',
            'password'      => 'string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        try {
            DB::transaction(function () use ($user, $request) {
                $userLama = $user;
                $user->update($request->all());
                UserLog::simpan("Mengubah User {$user->nama}", ["semula" => $userLama, "menjadi" => $user]);
            });

            return response()->json([
                'success' => true,
                'message' => 'User berhasil diupdate',
                'data'    => $user
            ]);
        } catch (\Exception $th) {
            return response()->json([
                'success'  => false,
                'message' => 'Gagal update User. Transaksi dibatalkan secara otomatis.',
                'error'   => $th->getMessage()
            ], 500);
        }
    }
}
