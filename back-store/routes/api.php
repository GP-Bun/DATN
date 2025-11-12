<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Admin\AuthController as AdminAuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| Tất cả các route ở đây sẽ được truy cập qua đường dẫn bắt đầu bằng /api
| Ví dụ: http://127.0.0.1:8000/api/test
|--------------------------------------------------------------------------
*/

//  Route kiểm tra kết nối API
Route::get('/test', function () {
    return response()->json(['message' => 'API OK!']);
});

//  Route test public (không cần đăng nhập)
Route::get('/products', function () {
    return response()->json([
        ['id' => 1, 'name' => 'Nike Air Force 1', 'price' => 3200000],
        ['id' => 2, 'name' => 'Adidas Superstar', 'price' => 2800000],
        ['id' => 3, 'name' => 'Converse Chuck Taylor', 'price' => 1500000],
    ]);
});

// Đăng ký và đăng nhập (public routes)
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

//  Các route cần đăng nhập (bảo vệ bằng Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user-profile', function (Request $request) {
        return response()->json([
            'message' => 'Lấy thông tin người dùng thành công!',
            'user' => $request->user()
        ]);
    });

    Route::post('/user-profile', [AuthController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

// Admin Routes

// ---------------- Admin ----------------
Route::prefix('admin')->group(function () {
    Route::post('login', [AdminAuthController::class, 'login']);
    Route::post('register', [AdminAuthController::class, 'register']);
});

Route::prefix('admin')->middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('dashboard', [AdminAuthController::class, 'dashboard']);
    Route::post('logout', [AdminAuthController::class, 'logout']);
    Route::get('profile', function (Request $request) {
        return response()->json(['admin' => $request->user()]);
    });
});
