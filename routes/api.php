<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProdukController;
use App\Http\Controllers\Api\StokController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::get('/produks', [ProdukController::class, 'index']);
Route::get('/produks/{id}', [ProdukController::class, 'show']);
Route::get('/stoks', [StokController::class, 'index']);

// Route terproteksi token harus login dulu
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // get user by token aktif
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // ADMIN ONLY
    Route::middleware('role:admin')->group(function () {
        // load all user penjual
        Route::get('/user/penjual', [UserController::class, 'getPenjual']);

        Route::post('/produks', [ProdukController::class, 'store']);
        Route::put('/produks/{id}', [ProdukController::class, 'update']);

        Route::post('/stoks', [StokController::class, 'store']);
    });
});

// Route::apiResource('produks', ProdukController::class);
