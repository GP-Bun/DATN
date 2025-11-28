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

    // Category
    Route::resource('categories', AdminCategoryController::class);

    // Product
    Route::resource('products', AdminProductController::class);

    // Product Variant (nested)
    Route::prefix('products/{product}')->group(function() {
        Route::post('/variants', [AdminProductVariantController::class, 'store'])->name('products.variants.store');
        Route::put('/variants/{variant}', [AdminProductVariantController::class, 'update'])->name('products.variants.update');
        Route::delete('/variants/{variant}', [AdminProductVariantController::class, 'destroy'])->name('products.variants.destroy');
    });

    // show
    Route::get('/admin/products/{product}', [ProductController::class, 'show'])
    ->name('admin.products.show');
    
    // Order
    Route::resource('orders', AdminOrderController::class)->only(['index', 'show', 'update', 'destroy']);

    // Coupon
    Route::resource('coupons', AdminCouponController::class)->only(['index', 'create', 'store', 'destroy']);

    // User (quản lý tài khoản)
    Route::resource('users', AdminUserController::class);

       // Alias "accounts" để sidebar chạy được
    Route::resource('accounts', AdminUserController::class);
});
