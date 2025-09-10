<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductStockController;
use App\Http\Controllers\API\AuthTokenController;

// Login -> gauti tokeną
Route::post('/auth/token', [AuthTokenController::class, 'store']);

// Logout -> panaikinti tokeną
Route::post('/auth/logout', [AuthTokenController::class, 'destroy'])
    ->middleware('auth:sanctum');

// Apsaugotas API
Route::middleware(['redirect.api.browser', 'auth:sanctum'])->group(function () {
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product:sku}/stock', [ProductStockController::class, 'show'])
        ->name('api.products.stock.show');
});

Route::view('/info', 'api.info')->name('api.info');


