<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ProductController;
use App\Http\Controllers\Admin\ProductVariantController;

use App\Http\Controllers\OrderController;
use App\Http\Controllers\CouponController;

Route::get('/', function () {
    return 'Home page';
});

Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // CRUD cho danh mục
    Route::resource('categories', CategoryController::class);

    // CRUD cho sản phẩm
    Route::resource('products', ProductController::class);
});

// Quản lý đơn hàng 
Route::resource('orders', OrderController::class)->only(['index', 'show', 'update', 'destroy']);

// Quản lý mã giảm giá
Route::resource('coupons', CouponController::class)->only(['index', 'create', 'store', 'destroy']);
