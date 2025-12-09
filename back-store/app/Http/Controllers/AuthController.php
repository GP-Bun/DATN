<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

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

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'role'     => 'user',
            'active'   => 1,
        ]);

        return response()->json(['user' => $user], 201);
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
        

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'user'         => $user
        ]);
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

        return response()->json(['message'=>'Cập nhật thành công','user'=>$user]);
    }

    // Logout
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message'=>'Đăng xuất thành công']);
    }
}
