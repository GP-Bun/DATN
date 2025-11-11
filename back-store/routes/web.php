<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductVariantController;

// Trang chính redirect tới danh mục
Route::get('/', function () {
    return redirect()->route('categories.index');
});

// CRUD danh mục
Route::resource('categories', CategoryController::class);

// CRUD sản phẩm
Route::resource('products', ProductController::class);

// CRUD biến thể sản phẩm (nested route)
Route::prefix('products/{product}')->group(function () {
    Route::post('/variants', [ProductVariantController::class, 'store'])->name('variants.store');
    Route::put('/variants/{variant}', [ProductVariantController::class, 'update'])->name('variants.update');
    Route::delete('/variants/{variant}', [ProductVariantController::class, 'destroy'])->name('variants.destroy');
});
