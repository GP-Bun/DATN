<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Api\CouponController as ApiCouponController;
use App\Http\Controllers\Api\ProductController as ApiProductController;
use App\Http\Controllers\Api\ColorController;
use App\Http\Controllers\Api\CartController; 
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\HomeController;
// use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\OrderController;


// Route::get('/home', [HomeController::class, 'index']);

// Test API
Route::get('/test', fn() => response()->json(['message' => 'API OK!']));

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected user routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user-profile', fn(Request $request) => response()->json([
        'message' => 'Lấy thông tin người dùng thành công!',
        'user' => $request->user()
    ]));

    Route::post('/user-profile', [AuthController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Admin routes
Route::prefix('admin')->group(function () {
    Route::post('login', [AdminAuthController::class, 'login']);
    Route::post('register', [AdminAuthController::class, 'register']);

    Route::middleware(['auth:sanctum', 'admin'])->group(function () {
        Route::get('dashboard', [AdminAuthController::class, 'dashboard']);
        Route::post('logout', [AdminAuthController::class, 'logout']);
        Route::get('profile', fn(Request $request) => response()->json(['admin' => $request->user()]));
    });
});

// Coupon API
Route::post('/coupons/apply', [ApiCouponController::class, 'apply']);


// =====================================================
// 🟦 PRODUCT API — ĐÃ SỬA ĐÚNG CHUẨN FE React
// =====================================================
Route::prefix('products')->group(function () {

    // PUBLIC API
    Route::get('/', [ApiProductController::class, 'index']);
    Route::get('/{id}', [ApiProductController::class, 'show']);

    // ADMIN – cần login + quyền admin
    Route::middleware(['auth:sanctum', 'admin'])->group(function () {

        Route::post('/',             [ApiProductController::class, 'store']);
        Route::put('/{id}',          [ApiProductController::class, 'update']);
        Route::delete('/{id}',       [ApiProductController::class, 'destroy']);

        Route::get('/trash/list',    [ApiProductController::class, 'trash']);
        Route::post('/restore/{id}', [ApiProductController::class, 'restore']);
        Route::delete('/force-delete/{id}', [ApiProductController::class, 'forceDelete']);
    });
});

// CART API (PUBLIC)
Route::middleware('auth:sanctum')->prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'index']);       
    Route::post('/', [CartController::class, 'add']);        
    Route::put('/{item}', [CartController::class, 'update']); 
    Route::delete('/{item}', [CartController::class, 'remove']); 
    Route::delete('/', [CartController::class, 'clear']);    
});


// CHECKOUT API
Route::post('/checkout', [CheckoutController::class, 'checkout'])->middleware('auth:sanctum');

// USER — cần đăng nhập
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/orders', [OrderController::class, 'index']);       // Lấy danh sách đơn hàng của user
    Route::get('/orders/{order}', [OrderController::class, 'show']); // Xem chi tiết đơn hàng
});

// ADMIN — quản lý đơn hàng
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::put('/orders/{order}/status', [OrderController::class, 'updateStatus']); // Cập nhật trạng thái
    Route::delete('/orders/{order}', [OrderController::class, 'destroy']);          // Xóa đơn hàng
});

// COLORS
Route::apiResource('colors', ColorController::class)->only(['index','store','destroy']);

// HOME API
Route::get('/home', [HomeController::class, 'index']);

// REVIEWS API
Route::prefix('products/{productId}/reviews')->group(function () {
    Route::get('/', [ReviewController::class, 'index']); // Lấy danh sách đánh giá (public)
    Route::post('/', [ReviewController::class, 'store']); // Thêm đánh giá (có thể không cần auth)
    Route::delete('/{id}', [ReviewController::class, 'destroy'])->middleware('auth:sanctum'); // Xóa đánh giá (cần auth)
});
