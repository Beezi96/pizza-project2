<?php

use App\Http\Controllers\Web\CartController;
use App\Http\Controllers\Web\ProductController;
use Illuminate\Support\Facades\Route;
/*
Route::get('/', function () {
    return view('welcome');
});
*/

Route::get('/', [ProductController::class, 'index'])->name('products.index');
Route::get('/product/{product}', [ProductController::class, 'show'])->name('products.show');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
