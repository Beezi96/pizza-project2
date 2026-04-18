<?php

use App\Http\Controllers\Api\AdminOrderController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


Route::get('/health', function() {
    return response()->json([
        'status' => 'ok',
    ]);
});


Route::get('/products', [ProductController::class, 'index'])->name('api.products.index');
Route::get('/products/{id}', [ProductController::class, 'show'])->name('api.products.show');

Route::middleware(['auth:api', 'admin'])->group(function () {
    Route::post('/products', [ProductController::class, 'store'])->name('api.products.store');
    Route::put('/products/{id}', [ProductController::class, 'update'])->name('api.products.update');
    Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('api.products.destroy');
});

Route::middleware('auth:api')->group(function () {
    Route::get('/cart', [CartController::class, 'index'])->name('api.cart.index');
    Route::post('/cart/items', [CartController::class, 'store'])->name('api.cart.store');
    Route::put('/cart/items/{id}', [CartController::class, 'update'])->name('api.cart.update');
    Route::delete('/cart/items/{id}', [CartController::class, 'destroy'])->name('api.cart.destroy');

    Route::post('/orders', [OrderController::class, 'store'])->name('api.order.store');
    Route::get('/orders', [OrderController::class, 'index'])->name('api.order.index');
    Route::get('/orders/{id}', [OrderController::class, 'show'])->name('api.order.show');
});

Route::prefix('admin')->middleware(['auth:api', 'admin'])->group(function () {
    Route::get('/orders', [AdminOrderController::class, 'index'])->name('admin.order.index');
    Route::get('/orders/{id}', [AdminOrderController::class, 'show'])->name('admin.order.show');
    Route::put('/orders/{id}/status', [AdminOrderController::class, 'updateStatus'])->name('admin.order.status');
});

Route::middleware(['auth:api', 'admin'])->get('/admin/test', function () {
    return response()->json([
        'message' => 'Welcome Admin!',
    ]);
});


Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register'])->name('api.auth.register');
    Route::post('/login', [AuthController::class, 'login'])->name('api.auth.login');

    Route::middleware('auth:api')->group(function () {
        Route::post('/me', [AuthController::class, 'me'])->name('api.auth.me');
        Route::post('/logout', [AuthController::class, 'logout'])->name('api.auth.logout');
        Route::post('/refresh', [AuthController::class, 'refresh'])->name('api.auth.refresh');
    });
});
