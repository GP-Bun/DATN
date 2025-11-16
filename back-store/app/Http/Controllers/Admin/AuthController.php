<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // =================== Đăng ký Admin ===================
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:admins,email',
            'password' => 'required|string|min:6',
        ]);

        $admin = Admin::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json(['admin' => $admin], 201);
    }

    // =================== Đăng nhập Admin ===================
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $admin = Admin::where('email', $request->email)->first();

        // Email không phải admin
        if (!$admin) {
            return response()->json(['message' => 'Chỉ admin mới được đăng nhập'], 403);
        }

        // Kiểm tra mật khẩu
        if (!Hash::check($request->password, $admin->password)) {
            return response()->json(['message' => 'Mật khẩu không đúng'], 401);
        }

        $token = $admin->createToken('admin_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'admin' => $admin
        ]);
    }

    // =================== Logout ===================
    public function logout(Request $request)
    {
        // Lấy admin hiện tại từ Sanctum
        $admin = Auth::guard('sanctum')->user();
        if ($admin) {
            $admin->currentAccessToken()->delete();
        }

        return response()->json(['message' => 'Đăng xuất thành công']);
    }

    // =================== Dashboard Admin (ví dụ) ===================
    public function dashboard()
    {
        return response()->json(['message' => 'Chào mừng Admin!']);
    }
}
