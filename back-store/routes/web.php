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
use App\Http\Controllers\Admin\ChatController;

// ==================== Public Routes ====================

Route::get('/login', function () {
    return redirect()->route('admin.login');
})->name('login');

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
Route::prefix('admin')->name('admin.')->middleware(['auth:admin', 'role:admin,staff'])->group(function () {

    // Dashboard
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ==================== CHỈ ADMIN ====================
    Route::middleware('role:admin')->group(function () {
        // Categories
        Route::resource('categories', AdminCategoryController::class)
            ->middleware('permission:manage_categories');

        // Coupons
        Route::resource('coupons', AdminCouponController::class)
            ->only(['index', 'create', 'store', 'edit', 'update', 'destroy'])
            ->middleware('permission:manage_coupons');

        // Users
        Route::resource('users', AdminUserController::class)
            ->middleware('permission:manage_users');
        Route::resource('accounts', AdminUserController::class)
            ->middleware('permission:manage_users');

        // Force delete product
        Route::delete('products/{id}/force-delete', [AdminProductController::class, 'forceDelete'])
            ->name('products.forceDelete')
            ->middleware('permission:delete_products');

        // Delete review
        Route::delete('reviews/{id}', [AdminReviewController::class, 'destroy'])
            ->name('reviews.destroy')
            ->middleware('permission:delete_reviews');
    });

    // ==================== ADMIN & STAFF ====================

    // Products
    Route::get('products/trash', [AdminProductController::class, 'trash'])
        ->name('products.trash')
        ->middleware('permission:edit_products');

    Route::patch('products/{id}/restore', [AdminProductController::class, 'restore'])
        ->name('products.restore')
        ->middleware('permission:edit_products');

    Route::resource('products', AdminProductController::class)
        ->middleware('permission:edit_products');

    // Product Variants
    Route::prefix('products/{product}')->middleware('permission:edit_products')->group(function () {
        Route::post('/variants', [AdminProductVariantController::class, 'store'])->name('products.variants.store');
        Route::put('/variants/{variant}', [AdminProductVariantController::class, 'update'])->name('products.variants.update');
        Route::delete('/variants/{variant}', [AdminProductVariantController::class, 'destroy'])->name('products.variants.destroy');
    });

    // Colors & Sizes
    Route::resource('colors', \App\Http\Controllers\Admin\ColorController::class)
        ->only(['index', 'store', 'destroy'])
        ->middleware('permission:edit_products');

    Route::resource('sizes', \App\Http\Controllers\Admin\SizeController::class)
        ->only(['index', 'store', 'destroy'])
        ->middleware('permission:edit_products');

    // Orders
    Route::get('orders/search', [AdminOrderController::class, 'search'])
        ->name('orders.search')
        ->middleware('permission:view_orders');

    Route::resource('orders', AdminOrderController::class)
        ->only(['index', 'show', 'update', 'destroy'])
        ->middleware('permission:view_orders');

    Route::put('orders/{order}/update-payment', [AdminOrderController::class, 'updatePayment'])
        ->name('orders.updatePayment')
        ->middleware('permission:view_orders');

    // Reviews
    Route::get('reviews', [AdminReviewController::class, 'index'])
        ->name('reviews.index')
        ->middleware('permission:manage_reviews');

    Route::put('reviews/{id}/status', [AdminReviewController::class, 'updateStatus'])
        ->name('reviews.updateStatus')
        ->middleware('permission:manage_reviews');

    // Chat Support
    Route::get('chat', [ChatController::class, 'index'])
        ->name('chat.index')
        ->middleware('permission:chat_support');

    Route::get('chat/{id}/messages', [ChatController::class, 'getMessages'])
        ->name('chat.messages')
        ->middleware('permission:chat_support');

    Route::post('chat/send', [ChatController::class, 'sendMessage'])
        ->name('chat.send')
        ->middleware('permission:chat_support');

    Route::delete('chat/{id}', [ChatController::class, 'destroy'])
        ->name('chat.destroy')
        ->middleware('permission:chat_support');

    // 🔥 API load danh sách
    Route::get('chat/conversations', [ChatController::class, 'conversations'])
        ->middleware('permission:chat_support');

    // ✅ Đánh dấu đã đọc
    Route::post('chat/{id}/read', [ChatController::class, 'markAsRead'])
        ->middleware('permission:chat_support');
});
