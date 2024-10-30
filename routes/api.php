<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BarangController;
use App\Http\Controllers\Api\BarangKeluarController;
use App\Http\Controllers\Api\BarangMasukController;
use App\Http\Controllers\Api\PelangganController;
use App\Http\Controllers\Api\PemasokController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum'])->group(function () {

    Route::post('/logout', [AuthController::class, 'logout']);

    Route::apiResource('/users', UserController::class);

    Route::apiResource('/pemasok', PemasokController::class);

    Route::apiResource('/pelanggan', PelangganController::class);

    Route::apiResource('/barang', BarangController::class);

    Route::apiResource('/barang-masuk', BarangMasukController::class);

    Route::apiResource('/barang-keluar', BarangKeluarController::class);
});
