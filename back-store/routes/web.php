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
use App\Http\Controllers\Admin\ReviewController as AdminReviewController;
use App\Http\Controllers\Admin\WebAuthController;


// ==================== Public Routes ====================

// Trang chính chuyển đến login admin
Route::get('/', function () {
    return redirect()->route('admin.login');
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

// ==================== Admin Auth Routes (không cần đăng nhập) ====================
Route::prefix('admin')->name('admin.')->group(function () {
    Route::get('login', [WebAuthController::class, 'showLoginForm'])->name('login');
    Route::post('login', [WebAuthController::class, 'login'])->name('login.submit');
    Route::post('logout', [WebAuthController::class, 'logout'])->name('logout');
});

// ==================== Admin Routes (cần đăng nhập) ====================
Route::prefix('admin')->name('admin.')->middleware('admin.web.auth')->group(function () {

    // Dashboard - Nhân viên chỉ xem dashboard đơn giản, Admin xem đầy đủ thống kê
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ==================== QUYỀN CHỈ ADMIN ====================
    // Categories - chỉ admin được tạo/xóa
    Route::resource('categories', AdminCategoryController::class)
        ->middleware('admin.web.permission:manage_categories');

    // Coupons - chỉ admin
    Route::resource('coupons', AdminCouponController::class)
        ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
        ->middleware('admin.web.permission:manage_coupons');

    // Users - chỉ admin
    Route::resource('users', AdminUserController::class)
        ->middleware('admin.web.permission:manage_users');
    Route::resource('accounts', AdminUserController::class)
        ->middleware('admin.web.permission:manage_users');

    // ==================== QUYỀN ADMIN VÀ NHÂN VIÊN ====================

    // Products - nhân viên được thêm/sửa
    Route::get('products/trash', [AdminProductController::class, 'trash'])
        ->name('products.trash');
    Route::patch('products/{id}/restore', [AdminProductController::class, 'restore'])
        ->name('products.restore');
    Route::delete('products/{id}/force-delete', [AdminProductController::class, 'forceDelete'])
        ->name('products.forceDelete')
        ->middleware('admin.web.permission:delete_products'); // Chỉ admin được xóa vĩnh viễn
    Route::resource('products', AdminProductController::class);

    // Product Variants (nested)
    Route::prefix('products/{product}')->group(function () {
        Route::post('/variants', [AdminProductVariantController::class, 'store'])->name('products.variants.store');
        Route::put('/variants/{variant}', [AdminProductVariantController::class, 'update'])->name('products.variants.update');
        Route::delete('/variants/{variant}', [AdminProductVariantController::class, 'destroy'])->name('products.variants.destroy');
    });

    // Colors & Sizes
    Route::resource('colors', \App\Http\Controllers\Admin\ColorController::class)
        ->only(['index', 'store', 'destroy']);
    Route::resource('sizes', \App\Http\Controllers\Admin\SizeController::class)
        ->only(['index', 'store', 'destroy']);

    // Orders - nhân viên được xem và cập nhật
    Route::get('orders/search', [AdminOrderController::class, 'search'])->name('orders.search');
    Route::resource('orders', AdminOrderController::class)->only(['index', 'show', 'update', 'destroy']);
    Route::put('orders/{order}/update-payment', [AdminOrderController::class, 'updatePayment'])
        ->name('orders.updatePayment');

    // Reviews - nhân viên được xem và phản hồi
    Route::get('reviews', [AdminReviewController::class, 'index'])->name('reviews.index');
    Route::put('reviews/{id}/status', [AdminReviewController::class, 'updateStatus'])->name('reviews.updateStatus');
    Route::delete('reviews/{id}', [AdminReviewController::class, 'destroy'])
        ->name('reviews.destroy')
        ->middleware('admin.web.permission:delete_reviews'); // Chỉ admin được xóa

    // Chat Support
    Route::get('chat', [App\Http\Controllers\Admin\ChatController::class, 'index'])->name('chat.index');
    Route::get('chat/{id}/messages', [App\Http\Controllers\Admin\ChatController::class, 'getMessages'])->name('chat.messages');
    Route::post('chat/send', [App\Http\Controllers\Admin\ChatController::class, 'sendMessage'])->name('chat.send');
    Route::delete('chat/{id}', [App\Http\Controllers\Admin\ChatController::class, 'destroy'])->name('chat.destroy');
});
