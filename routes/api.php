<?php

use App\Http\Controllers\Api\AsetController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\KategoriAsetController;
use App\Http\Controllers\Api\KategoriController;
use App\Http\Controllers\Api\KonfigurasiController;
use App\Http\Controllers\Api\LokasiSellerController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProdukController;
use App\Http\Controllers\Api\ResepController;
use App\Http\Controllers\Api\SatuanController;
use App\Http\Controllers\Api\StokController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::get('/produks', [ProdukController::class, 'index']);
Route::get('/produks/{id}', [ProdukController::class, 'show']);
Route::get('/stoks', [StokController::class, 'index']);
Route::get('/lokasiPenjual/getPenjualByLatLong', [LokasiSellerController::class, 'getPenjualByLatLong']);

// konfigurasi
Route::get('/konfigurasi', [KonfigurasiController::class, 'index']);

// Route terproteksi token harus login dulu
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // get user by token aktif
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // PENJUAL
    Route::middleware('role:penjual')->group(function () {
        // lokasi
        Route::post('/lokasiPenjual/updateLokasiPenjual', [LokasiSellerController::class, 'updateLokasiPenjual']);

        // order
        Route::post('order/checkoutPesanan', [OrderController::class, 'checkoutPesanan']);
        Route::post('order/updatePesanan', [OrderController::class, 'update']);
    });

    // PEMBELI
    Route::middleware('role:pembeli')->group(function () {
        // Keranjang
        Route::get('/keranjang/getKeranjangPembeli', [CartController::class, 'getCartPembeli']);
        Route::post('/keranjang/simpanKeranjang', [CartController::class, 'store']);
        Route::put('/keranjang/simpanKeranjang/{id}', [CartController::class, 'update']);
        Route::delete('/keranjang/hapusKeranjang/{id}', [CartController::class, 'destroy']);

        // lokasi
        Route::get('/lokasiPenjual/getPenjualByLokasiId/{id}', [LokasiSellerController::class, 'getPenjualByLokasiId']);

        // produk
        Route::get('/stoks/getProdukByPenjual/{id}', [StokController::class, 'getProdukByPenjual']);

        // order
        Route::post('order/checkoutPesanan', [OrderController::class, 'checkoutPesanan']);
    });

    // ADMIN ONLY
    Route::middleware('role:admin')->group(function () {
        // load all user penjual
        Route::get('/user/penjual', [UserController::class, 'getPenjual']);
        Route::post('/user', [UserController::class, 'store']);
        Route::put('/user/{id}', [UserController::class, 'update']);

        // produk
        Route::post('/produks', [ProdukController::class, 'store']);
        Route::put('/produks/{id}', [ProdukController::class, 'update']);

        // stok
        Route::post('/stoks', [StokController::class, 'store']);
        Route::post('/stoks/simpanBatch', [StokController::class, 'simpanBatch']);

        // satuan
        Route::resource('/satuan', SatuanController::class);

        // aset
        Route::resource('/aset', AsetController::class);
        Route::get('/getAsetByKategoriAset/{id}', [AsetController::class, 'getAsetByKategoriAset']);

        // kategori menu
        Route::resource('/kategoriMenu', KategoriController::class);

        // kategori aset
        Route::resource('/kategoriAset', KategoriAsetController::class);

        // resep
        Route::get('/resep/produk/{id}', [ResepController::class, 'getResepByIdProduk']);
        Route::post('/resep/simpanResepBatch', [ResepController::class, 'storeBatch']);
        Route::put('/resep/updateResep/{id}', [ResepController::class, 'update']);
        Route::delete('/resep/hapusResep/{id}', [ResepController::class, 'destroy']);

        // lokasi seller
        Route::get('/lokasiPenjual', [LokasiSellerController::class, 'index']);
        Route::get('/lokasiPenjual/show/{id}', [LokasiSellerController::class, 'show']);
        Route::put('/lokasiPenjual/update/{id}', [LokasiSellerController::class, 'update']);
        Route::delete('/lokasiPenjual/delete/{id}', [LokasiSellerController::class, 'destroy']);

        // konfigurasi
        Route::post('/konfigurasi/simpan', [KonfigurasiController::class, 'store']);
        Route::put('/konfigurasi/update/{id}', [KonfigurasiController::class, 'update']);
    });
});

// Route::apiResource('produks', ProdukController::class);
