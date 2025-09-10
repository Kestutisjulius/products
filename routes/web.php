<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\DashboardController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Models\Product;

Route::get('/', function () {
    $products = Product::inRandomOrder()->take(8)->get(['id', 'sku', 'photo']);
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
        'products' => $products,
    ]);
})->name('home');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/products', [ProductController::class, 'ui_index'])->middleware('auth')->name('products.index');
Route::get('/products/{product:sku}', [ProductController::class, 'show'])->name('products.show');
Route::view('/api/info', 'api.info')->name('api.info');

require __DIR__.'/auth.php';
