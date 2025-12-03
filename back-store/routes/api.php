<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;
use App\Http\Controllers\Api\CouponController as ApiCouponController;

// Test API
Route::get('/test', fn() => response()->json(['message'=>'API OK!']));

// Public routes
Route::get('/products', fn() => response()->json([
    ['id'=>1,'name'=>'Nike Air Force 1','price'=>3200000],
    ['id'=>2,'name'=>'Adidas Superstar','price'=>2800000],
    ['id'=>3,'name'=>'Converse Chuck Taylor','price'=>1500000],
]));

Route::post('/register', [AuthController::class,'register']);
Route::post('/login', [AuthController::class,'login']);

// Protected user routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user-profile', fn(Request $request) => response()->json([
        'message'=>'Lấy thông tin người dùng thành công!',
        'user'=>$request->user()
    ]));

    Route::post('/user-profile', [AuthController::class,'updateProfile']);
    Route::post('/logout', [AuthController::class,'logout']);
});

// Admin routes
Route::prefix('admin')->group(function () {
    Route::post('login', [AdminAuthController::class,'login']);
    Route::post('register', [AdminAuthController::class,'register']);

    Route::middleware(['auth:sanctum','admin'])->group(function () {
        Route::get('dashboard', [AdminAuthController::class,'dashboard']);
        Route::post('logout', [AdminAuthController::class,'logout']);
        Route::get('profile', fn(Request $request) => response()->json(['admin'=>$request->user()]));
    });
});

// Coupon API
Route::post('/coupons/apply', [ApiCouponController::class,'apply']);
