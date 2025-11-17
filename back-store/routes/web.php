<?php

use Illuminate\Support\Facades\Route;

// Controllers
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductVariantController;

use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ProductVariantController as AdminProductVariantController;

use App\Http\Controllers\OrderController;
use App\Http\Controllers\CouponController;

// ===== Public Routes =====

// Trang chính redirect tới danh mục
Route::get('/', function () {
    return redirect()->route('categories.index');
});

// CRUD danh mục (public)
Route::resource('categories', CategoryController::class);

// CRUD sản phẩm (public)
Route::resource('products', ProductController::class);

// CRUD biến thể sản phẩm (nested route, public)
Route::prefix('products/{product}')->group(function () {
    Route::post('/variants', [ProductVariantController::class, 'store'])->name('variants.store');
    Route::put('/variants/{variant}', [ProductVariantController::class, 'update'])->name('variants.update');
    Route::delete('/variants/{variant}', [ProductVariantController::class, 'destroy'])->name('variants.destroy');
});

// ===== Admin Routes =====
Route::prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // CRUD danh mục (admin)
    Route::resource('categories', AdminCategoryController::class);

    // CRUD sản phẩm (admin)
    Route::resource('products', AdminProductController::class);

    // CRUD biến thể sản phẩm (admin)
    Route::prefix('products/{product}')->group(function () {
        Route::post('/variants', [AdminProductVariantController::class, 'store'])->name('products.variants.store');
        Route::put('/variants/{variant}', [AdminProductVariantController::class, 'update'])->name('products.variants.update');
        Route::delete('/variants/{variant}', [AdminProductVariantController::class, 'destroy'])->name('products.variants.destroy');
    });
});

// ===== Quản lý đơn hàng =====
Route::resource('orders', OrderController::class)->only(['index', 'show', 'update', 'destroy']);

// ===== Quản lý mã giảm giá =====
Route::resource('coupons', CouponController::class)->only(['index', 'create', 'store', 'destroy']);
