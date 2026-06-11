<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LokasiSeller;
use App\Models\UserLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class LokasiSellerController extends Controller
{
    public function index()
    {
        $lokasiSeller = LokasiSeller::with('users', 'gerobak')->whereDate('tanggal', date('Y-m-d'))->orderBy('penjual_id', 'asc')->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar Lokasi Penjual',
            'data'    => $lokasiSeller
        ], 200);
    }

    public function getPenjualByLokasiId($id)
    {
        $getSeller = LokasiSeller::with('users', 'gerobak')->where('provinsi_id', $id)
            ->orWhere('kota_id', $id)
            ->orWhere('kecamatan_id', $id)
            ->orWhere('kelurahan_id', $id)
            ->get();

        return response()->json([
            'success' => true,
            'message' => 'Daftar Lokasi Penjual',
            'data'    => $getSeller
        ], 200);
    }

    public function show($id)
    {
        $lokasiModel = LokasiSeller::with(['users', 'gerobak'])->find($id);

        if ($lokasiModel) {
            return response()->json([
                'success' => true,
                'message' => 'Detail Data Lokasi Penjual',
                'data'    => $lokasiModel
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Lokasi Penjual Tidak Ditemukan!',
        ], 404);
    }

    public function update(Request $request, $id)
    {
        $lokasiModel = LokasiSeller::with(['users', 'gerobak'])->find($id);

        if (!$lokasiModel) {
            return response()->json([
                'success' => false,
                'message' => 'Data Lokasi Penjual tidak ditemukan'
            ], 404);
        }

        try {
            DB::transaction(function () use ($request, $id, $lokasiModel) {
                $lokasiLama = $lokasiModel;
                $lokasiModel->update($request->all());

                UserLog::simpan("Mengubah data lokasi Penjual", ["semula" => $lokasiLama, "menjadi" => $lokasiModel]);
            });

            return response()->json([
                'success' => true,
                'message' => 'Data Lokasi Penjual berhasil diupdate'
            ], 200);
        } catch (\Exception $th) {
            return response()->json([
                'success'  => false,
                'message' => 'Gagal menyimpan Lokasi Penjual. Transaksi dibatalkan.',
                'error'   => $th->getMessage()
            ], 500);
        }
    }

    public function destroy($id)
    {
        $lokasiModel = LokasiSeller::with('users', 'gerobak')->find($id);

        if (!$lokasiModel) {
            return response()->json([
                'success' => false,
                'message' => 'Data Lokasi Penjual tidak ditemukan'
            ], 404);
        }

        try {
            // 2. Jalankan transaksi database
            DB::transaction(function () use ($lokasiModel) {
                $lokasiLamaModel = $lokasiModel;
                $lokasiModel->delete();

                UserLog::simpan("Hapus Lokasi Penjual untuk User {$lokasiLamaModel->users->nama} dan gerobak {$lokasiLamaModel->gerobak->nama}", ["semula" => $lokasiLamaModel, "menjadi" => $lokasiModel]);
            });

            return response()->json([
                'success'  => true,
                'message' => 'Lokasi Penjual berhasil dihapus.',
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success'  => false,
                'message' => 'Gagal menghapus Lokasi Penjual.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    public function updateLokasiPenjual(Request $request)
    {
        $penjual_id = Auth::id();
        $lokasiPenjualModel = LokasiSeller::where('penjual_id', $penjual_id)
            ->whereDate('tanggal', date('Y-m-d'))
            ->first();

        if (!$lokasiPenjualModel) {
            return response()->json([
                'success' => false,
                'message' => 'Data Lokasi Penjual tidak ditemukan'
            ], 404);
        }

        try {
            DB::transaction(function () use ($lokasiPenjualModel, $request) {
                $lokasiLamaPenjual = $lokasiPenjualModel;
                $lat_penjual = $request->lat;
                $long_penjual = $request->long;

                $lokasiPenjualModel->lat_penjual = $lat_penjual;
                $lokasiPenjualModel->long_penjual = $long_penjual;
                $lokasiPenjualModel->save();

                UserLog::simpan('Update lokasi penjual', ["semula" => $lokasiLamaPenjual, "menjadi" => $lokasiPenjualModel]);
            });

            return response()->json([
                'success'  => true,
                'message' => 'Lokasi Penjual berhasil diupdate.',
            ], 200);
        } catch (\Exception $th) {
            return response()->json([
                'success'  => false,
                'message' => 'Gagal update Lokasi Penjual. Transaksi dibatalkan.',
                'error'   => $th->getMessage()
            ], 500);
        }
    }

    public function getPenjualByLatLong(Request $request)
    {
        $lat = $request->lat;
        $lng = $request->long;

        $keyMaps = '65c30205349b2239842347lpt855845';
        // 1. Hit maps.co
        $geoResponse = Http::get("https://geocode.maps.co/reverse?lat={$lat}&lon={$lng}&api_key=" . $keyMaps)->json();
        $address = $geoResponse['address'] ?? [];

        $province = strtoupper($address['state'] ?? '');
        $allProvinces = Http::get("https://dwirachmat08.github.io/api-wilayah-indonesia/api/provinces.json")->json();
        $ketemuProvinsi = collect($allProvinces)->first(function ($value) use ($province) {
            return strtoupper($value['name']) === $province;
        });

        if (!$ketemuProvinsi) {
            return response()->json([
                'success'  => false,
                'message' => 'Provinsi tidak sinkron dengan sistem'
            ], 404);
        }

        $idKetemuProvinsi = $ketemuProvinsi['id'];

        $suburbRaw = $address['municipality'] ?? $address['city_district']; // Nama Kecamatan, misal: "Klojen"

        // 2. DETEKSI APAKAH USER DI KOTA ATAU KABUPATEN
        $namaKotaEmsifaTarget = '';
        if (array_key_exists('city', $address)) {
            // Jika ada key 'city', berarti ini wilayah KOTA
            $namaKotaEmsifaTarget = 'KOTA ' . strtoupper($address['city']);
        } elseif (array_key_exists('county', $address) || array_key_exists('regency', $address)) {
            // Jika masuk county/regency, berarti wilayah KABUPATEN
            $namaKabupatenRaw = $address['county'] ?? $address['regency'];

            // Membersihkan kata "Regency" jika maps.co mengembalikan "Malang Regency"
            $namaKabupatenMurni = str_replace(' Regency', '', $namaKabupatenRaw);

            $namaKotaEmsifaTarget = 'KABUPATEN ' . strtoupper($namaKabupatenMurni); // Menjadi: "KABUPATEN MALANG"
        }

        if (empty($namaKotaEmsifaTarget)) {
            return response()->json([
                'success'  => false,
                'message' => 'Format daerah tidak dikenali oleh GPS'
            ], 400);
        }

        // 3. Ambil semua data kota se-Indonesia dari API Emsifa
        $allRegencies = Http::get("https://dwirachmat08.github.io/api-wilayah-indonesia/api/regencies/{$idKetemuProvinsi}.json")->json();

        // // 4. PENCARIAN PRESISI (Bukan pakai str_contains lagi, tapi exact match '===')
        $ketemuKota = collect($allRegencies)->first(function ($value) use ($namaKotaEmsifaTarget) {
            return strtoupper($value['name']) === $namaKotaEmsifaTarget;
        });

        if (!$ketemuKota) {
            return response()->json([
                'success'  => false,
                'message' => 'Kota/Kabupaten tidak sinkron dengan sistem'
            ], 404);
        }

        $idKetemuKota = $ketemuKota['id'];

        // 5. Tarik kecamatan di dalam ID Kota/Kab tersebut
        $allDistricts = Http::get("https://dwirachmat08.github.io/api-wilayah-indonesia/api/districts/{$idKetemuKota}.json")->json();

        // 6. Cari kecamatannya
        $namaKecamatanMurni = strtoupper($suburbRaw);
        $ketemuKecamatan = collect($allDistricts)->first(function ($value) use ($namaKecamatanMurni) {
            return strtoupper($value['name']) === $namaKecamatanMurni;
        });

        if (!$ketemuKecamatan) {
            return response()->json([
                'success'  => false,
                'message' => 'Kecamatan tidak ditemukan'
            ], 404);
        }

        // 7. Selesai! Dapet ID Kecamatan
        $idKetemuKecamatan = $ketemuKecamatan['id'];

        $getSeller = LokasiSeller::with('users', 'gerobak')
            ->where('kecamatan_id', $idKetemuKecamatan)
            ->where('tanggal', date('Y-m-d'))
            ->get();

        return response()->json([
            'success'  => true,
            'message'   => 'Alamat ditemukan',
            'data_alamat' => ["alamat" => $address, "provinsi" => $ketemuProvinsi, "kota" => $ketemuKota, "kecamatan" => $ketemuKecamatan],
            'data_penjual' => $getSeller
        ]);
    }
}
