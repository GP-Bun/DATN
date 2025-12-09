<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Đăng ký Admin
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ], [
            'name.required' => 'Tên không được để trống',
            'name.string'   => 'Tên phải là chuỗi ký tự',
            'name.max'      => 'Tên không được vượt quá 255 ký tự',

            'email.required' => 'Email không được để trống',
            'email.email'    => 'Email phải là email hợp lệ',
            'email.unique'   => 'Email đã tồn tại',

            'password.required' => 'Mật khẩu không được để trống',
            'password.string'   => 'Mật khẩu phải là chuỗi ký tự',
            'password.min'      => 'Mật khẩu phải có ít nhất 6 ký tự',
        ]);

        $admin = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'admin',
            'active'   => 1,
        ]);

        return response()->json(['admin' => $admin], 201);
    }

    // Đăng nhập Admin
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required'    => 'Email không được để trống',
            'email.email'       => 'Email phải là email hợp lệ',
            'password.required' => 'Mật khẩu không được để trống',
            'password.string'   => 'Mật khẩu phải là chuỗi ký tự',
        ]);

        $admin = User::where('email', $request->email)
            ->where('role', 'admin')
            ->where('active', 1)
            ->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json(['message' => 'Sai tài khoản hoặc mật khẩu'], 401);
        }
        

        $token = $admin->createToken('admin_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'admin'        => $admin
        ]);
    }

    // Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Đăng xuất thành công']);
    }

    // Dashboard
    public function dashboard()
    {
        return response()->json(['message' => 'Chào mừng Admin!']);
    }
}
