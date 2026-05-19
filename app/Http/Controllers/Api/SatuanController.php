<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Satuan;
use Illuminate\Support\Facades\Validator;

class SatuanController extends Controller
{
    public function index()
    {
        // with(['kategori', 'stok']) itu Eager Loading biar gak lemot
        $satuans = Satuan::where(['aktif' => true])->orderBy('nama', 'asc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar Master Satuan',
            'data'    => $satuans
        ], 200);
    }

    public function store(Request $request)
    {
        // Validasi input untuk API
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:100|unique:satuan,nama'
        ], [
            'nama.required' => 'Nama satuan wajib diisi.',
            'nama.unique'   => 'Nama satuan sudah ada di database.',
        ]);

        // Jika validasi gagal, kembalikan response error 422
        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422); // 422 Unprocessable Entity
        }

        // Simpan ke database
        $satuan = Satuan::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Data Satuan berhasil ditambahkan',
            'data'    => $satuan
        ], 201); // 201 Created
    }

    public function show($id)
    {
        $satuan = Satuan::find($id);

        if (!$satuan) {
            return response()->json([
                'success' => false,
                'message' => 'Data Satuan tidak ditemukan'
            ], 404); // 404 Not Found
        }

        return response()->json([
            'success' => true,
            'message' => 'Detail Satuan ditemukan',
            'data'    => $satuan
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $satuan = Satuan::find($id);

        if (!$satuan) {
            return response()->json([
                'success' => false,
                'message' => 'Data Satuan tidak ditemukan'
            ], 404);
        }

        // Validasi, abaikan nama_satuan milik ID ini sendiri
        $validator = Validator::make($request->all(), [
            'nama' => 'required|string|max:100|unique:satuan,nama,' . $id
        ], [
            'nama.required' => 'Nama satuan wajib diisi.',
            'nama.unique'   => 'Nama satuan sudah ada di database.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors'  => $validator->errors()
            ], 422);
        }

        // Jalankan update
        $satuan->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Data Satuan berhasil diupdate',
            'data'    => $satuan
        ], 200);
    }

    public function destroy($id)
    {
        $satuan = Satuan::find($id);

        if (!$satuan) {
            return response()->json([
                'success' => false,
                'message' => 'Data Satuan tidak ditemukan'
            ], 404);
        }

        $satuan->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data Satuan berhasil dihapus'
        ], 200);
    }
}
