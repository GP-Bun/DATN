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
// use App\Http\Controllers\HomeController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminReviewController;
use App\Http\Controllers\Api\AddressController;
use App\Http\Controllers\Api\Geo\ProvinceController;
use App\Http\Controllers\Api\Geo\DistrictController;
use App\Http\Controllers\Api\Geo\WardController;
use App\Models\Province;


// Route::get('/home', [HomeController::class, 'index']);
Route::get('/geo-tree', function () {
    return response()->json(['data' => Province::with('districts.wards')->get()]);
});
// Test API
Route::get('/test', fn() => response()->json(['message' => 'API OK!']));

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected user routes
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user-profile', function (Request $request) {
        $user = $request->user();
        $user->load('addresses');

        // Thêm avatar URL nếu có
        if ($user->avatar) {
            $user->avatar_url = \Illuminate\Support\Facades\Storage::url($user->avatar);
        }

        return response()->json([
            'message' => 'Lấy thông tin người dùng thành công!',
            'user' => $user
        ]);
    });

    Route::post('/user-profile', [AuthController::class, 'updateProfile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Address routes
    Route::get('/addresses', [AddressController::class, 'index']);
    Route::post('/addresses', [AddressController::class, 'store']);
    Route::put('/addresses/{address}', [AddressController::class, 'update']);
    Route::delete('/addresses/{address}', [AddressController::class, 'destroy']);
    Route::post('/addresses/{address}/set-default', [AddressController::class, 'setDefault']);
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
Route::get('/coupons/available', [ApiCouponController::class, 'available']); // Lấy danh sách voucher có sẵn
Route::post('/coupons/apply', [ApiCouponController::class, 'apply']); // Áp dụng voucher


// 🟦 PRODUCT API — ĐÃ SỬA ĐÚNG CHUẨN FE React

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
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders', [OrderController::class, 'index']);       // Lấy danh sách đơn hàng của user
    Route::get('/orders/{order}', [OrderController::class, 'show']); // Xem chi tiết đơn hàng
    Route::post('/orders/{order}/confirm-payment', [OrderController::class, 'confirmPayment']); // ✅ Xác nhận thanh toán
});

// ADMIN — quản lý đơn hàng
Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::put('/orders/{order}/status', [OrderController::class, 'updateStatus']); // Cập nhật trạng thái
    Route::delete('/orders/{order}', [OrderController::class, 'destroy']);          // Xóa đơn hàng
});

// COLORS
Route::apiResource('colors', ColorController::class)->only(['index', 'store', 'destroy']);

// HOME API
Route::get('/home', [HomeController::class, 'index']);

// REVIEWS API
Route::prefix('products/{productId}/reviews')->group(function () {
    Route::get('/', [ReviewController::class, 'index']); // Lấy danh sách đánh giá (public)
    Route::post('/', [ReviewController::class, 'store'])->middleware('auth:sanctum'); // Thêm đánh giá (cần đăng nhập)
    Route::delete('/{id}', [ReviewController::class, 'destroy'])->middleware('auth:sanctum'); // Xóa đánh giá (cần auth)
});


Route::middleware(['auth:sanctum', 'admin'])->group(function () {
    Route::get('/admin/users', [AdminUserController::class, 'index']);
    Route::get('/admin/users/{id}', [AdminUserController::class, 'show']);
    Route::get('/admin/users/{id}/orders', [AdminUserController::class, 'orders']);
    Route::get('/admin/users/{id}/reviews', [AdminUserController::class, 'reviews']);
    Route::get('/admin/users/{id}/timeline', [AdminUserController::class, 'timeline']);
    Route::put('/admin/users/{id}/status', [AdminUserController::class, 'updateStatus']);
    Route::put('/admin/users/{id}/role', [AdminUserController::class, 'updateRole']);
    Route::delete('/admin/users/{id}', [AdminUserController::class, 'destroy']);
    Route::post('/admin/users/{id}/restore', [AdminUserController::class, 'restore']);

    // Admin Reviews Management
    Route::prefix('admin/reviews')->group(function () {
        Route::get('/', [AdminReviewController::class, 'index']);
        Route::get('/stats', [AdminReviewController::class, 'stats']);
        Route::get('/{id}', [AdminReviewController::class, 'show']);
        Route::put('/{id}/status', [AdminReviewController::class, 'updateStatus']);
        Route::delete('/{id}', [AdminReviewController::class, 'destroy']);
    });
});

Route::prefix('geo')->group(function () {
    // Lấy danh sách tỉnh/thành (34 tỉnh theo dữ liệu JSON) 
    Route::get('provinces', [ProvinceController::class, 'index']);
    // Lấy danh sách quận/huyện theo province_id 
    Route::get('districts', [DistrictController::class, 'index']);
    // Lấy danh sách xã/phường theo district_id 
    Route::get('wards', [WardController::class, 'index']);
});
