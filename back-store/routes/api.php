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
Route::prefix('cart')->group(function () {
    Route::get('/', [CartController::class, 'index']);
    Route::post('/', [CartController::class, 'add']);
    Route::put('/{item}', [CartController::class, 'update']);
    Route::delete('/{item}', [CartController::class, 'remove']);
    Route::delete('/', [CartController::class, 'clear']);
});

// CHECKOUT API
Route::post('/checkout', [CheckoutController::class, 'checkout']);

// COLORS
Route::apiResource('colors', ColorController::class)->only(['index','store','destroy']);
