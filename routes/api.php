<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProdukController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login'])->name('login');

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
    });
});

Route::apiResource('produks', ProdukController::class);
