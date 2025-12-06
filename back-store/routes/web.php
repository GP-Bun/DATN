<?php

use Illuminate\Support\Facades\Route;

// ==================== Public Controllers ====================
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductVariantController;

// ==================== Admin Controllers ====================
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\ProductVariantController as AdminProductVariantController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\CouponController as AdminCouponController;
use App\Http\Controllers\Admin\UserController as AdminUserController;




// ==================== Public Routes ====================

// Trang chính redirect tới danh mục
Route::get('/', function () {
    return redirect()->route('categories.index');
});

// CRUD danh mục (public)
Route::resource('categories', CategoryController::class);

// CRUD sản phẩm (public)
Route::resource('products', ProductController::class);

// CRUD biến thể sản phẩm (nested, public)
Route::prefix('products/{product}')->group(function () {
    Route::post('/variants', [ProductVariantController::class, 'store'])->name('variants.store');
    Route::put('/variants/{variant}', [ProductVariantController::class, 'update'])->name('variants.update');
    Route::delete('/variants/{variant}', [ProductVariantController::class, 'destroy'])->name('variants.destroy');
});

// ==================== Admin Routes ====================
Route::prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Categories
    Route::resource('categories', AdminCategoryController::class);

    // ==================== Products Trash & Restore ====================
    // Trang thùng rác
    Route::get('products/trash', [AdminProductController::class, 'trash'])
        ->name('products.trash');

    // Khôi phục
    Route::patch('products/{id}/restore', [AdminProductController::class, 'restore'])
        ->name('products.restore');

    // Xóa vĩnh viễn
    Route::delete('products/{id}/force-delete', [AdminProductController::class, 'forceDelete'])
        ->name('products.forceDelete');

    // Products
    Route::resource('products', AdminProductController::class);



    // Product Variants (nested)
    Route::prefix('products/{product}')->group(function () {
        Route::post('/variants', [AdminProductVariantController::class, 'store'])->name('products.variants.store');
        Route::put('/variants/{variant}', [AdminProductVariantController::class, 'update'])->name('products.variants.update');
        Route::delete('/variants/{variant}', [AdminProductVariantController::class, 'destroy'])->name('products.variants.destroy');
    });

    // Colors
    Route::resource('colors', \App\Http\Controllers\Admin\ColorController::class)
        ->only(['index', 'store', 'destroy']);

    // Sizes
    Route::resource('sizes', \App\Http\Controllers\Admin\SizeController::class)->only(['index', 'store', 'destroy']);

    // Orders
    Route::resource('orders', AdminOrderController::class)->only(['index', 'show', 'update', 'destroy']);

    // Coupons
    Route::resource('coupons', AdminCouponController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);

    // Users
    Route::resource('users', AdminUserController::class);

    // Alias "accounts" để sidebar chạy được
    Route::resource('accounts', AdminUserController::class);
});
