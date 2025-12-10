<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Models\Activity;

class AuthController extends Controller
{
    // Đăng ký user
    public function register(Request $request)
    {
        $request->validate([
            'name'     => 'required|string|max:255',
            'email'    => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ], [
            'name.required'     => 'Tên không được để trống',
            'name.string'       => 'Tên phải là chuỗi ký tự',
            'name.max'          => 'Tên không được vượt quá 255 ký tự',

            'email.required'    => 'Email không được để trống',
            'email.email'       => 'Email phải đúng định dạng',
            'email.unique'      => 'Email này đã được sử dụng',

            'password.required' => 'Mật khẩu không được để trống',
            'password.string'   => 'Mật khẩu phải là chuỗi ký tự',
            'password.min'      => 'Mật khẩu phải có ít nhất 6 ký tự',
        ]);

        try {
            $user = User::create([
                'name'     => $request->name,
                'email'    => $request->email,
                'password' => Hash::make($request->password),
                'role'     => 'user',
                'active'   => 1,
            ]);

            // Ghi log hoạt động (không làm fail nếu có lỗi)
            try {
                Activity::create([
                    'user_id'    => $user->id,
                    'action'     => 'register',
                    'description'=> 'Người dùng đã đăng ký tài khoản',
                ]);
            } catch (\Exception $e) {
                // Bỏ qua lỗi Activity, không ảnh hưởng đến đăng ký
            }

            return response()->json([
                'message' => 'Đăng ký thành công',
                'user' => $user
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Có lỗi xảy ra khi đăng ký',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Đăng nhập user
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required'    => 'Email không được để trống',
            'email.email'       => 'Email phải đúng định dạng',
            'password.required' => 'Mật khẩu không được để trống',
            'password.string'   => 'Mật khẩu phải là chuỗi ký tự',
        ]);

        $user = User::where('email', $request->email)
                    ->where('active', 1)
                    ->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Sai tài khoản hoặc mật khẩu'], 401);
        }
        
        try {
            $token = $user->createToken('auth_token')->plainTextToken;

            // Ghi log hoạt động (không làm fail nếu có lỗi)
            try {
                Activity::create([
                    'user_id'    => $user->id,
                    'action'     => 'login',
                    'description'=> 'Người dùng đã đăng nhập hệ thống',
                ]);
            } catch (\Exception $e) {
                // Bỏ qua lỗi Activity, không ảnh hưởng đến đăng nhập
            }

            return response()->json([
                'message'      => 'Đăng nhập thành công',
                'access_token' => $token,
                'token_type'   => 'Bearer',
                'user'         => $user
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Có lỗi xảy ra khi đăng nhập',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    // Cập nhật profile
    public function updateProfile(Request $request)
    {
        $request->validate([
            'name'  => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
        ], [
            'name.string' => 'Tên phải là chuỗi ký tự',
            'name.max'    => 'Tên không được vượt quá 255 ký tự',
            'phone.string'=> 'Số điện thoại phải là chuỗi ký tự',
            'phone.max'   => 'Số điện thoại không được vượt quá 20 ký tự',
        ]);

        $user = $request->user();
        $user->update($request->only('name','phone'));

        // Ghi log hoạt động
        Activity::create([
            'user_id'    => $user->id,
            'action'     => 'update_profile',
            'description'=> 'Người dùng đã cập nhật thông tin cá nhân',
        ]);

        return response()->json(['message'=>'Cập nhật thành công','user'=>$user]);
    }

    // Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        // Ghi log hoạt động
        Activity::create([
            'user_id'    => $request->user()->id,
            'action'     => 'logout',
            'description'=> 'Người dùng đã đăng xuất',
        ]);
        
        return response()->json(['message'=>'Đăng xuất thành công']);
    }
}
