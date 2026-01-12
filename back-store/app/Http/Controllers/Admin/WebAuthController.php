<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\Staff;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class WebAuthController extends Controller
{
    /**
     * Hiển thị form đăng nhập
     */
    public function showLoginForm()
    {
        // Nếu đã đăng nhập rồi thì chuyển đến dashboard
        if (session('admin_logged_in')) {
            return redirect()->route('admin.dashboard');
        }

        return view('admin.auth.login');
    }

    /**
     * Xử lý đăng nhập
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ], [
            'email.required' => 'Vui lòng nhập email',
            'email.email' => 'Email không hợp lệ',
            'password.required' => 'Vui lòng nhập mật khẩu',
            'password.min' => 'Mật khẩu phải có ít nhất 6 ký tự',
        ]);

        // Tìm trong bảng admins trước
        $admin = Admin::where('email', $request->email)
            ->where('active', true)
            ->first();

        // Nếu không tìm thấy, tìm trong bảng staffs
        $staff = null;
        if (!$admin) {
            $staff = Staff::where('email', $request->email)
                ->where('active', true)
                ->first();
        }

        $user = $admin ?? $staff;
        $accountType = $admin ? 'admin' : ($staff ? 'staff' : null);

        // Kiểm tra mật khẩu
        if (!$user || !Hash::check($request->password, $user->password)) {
            return back()
                ->withInput($request->only('email'))
                ->with('error', 'Email hoặc mật khẩu không đúng');
        }

        // Lưu thông tin đăng nhập vào session
        session([
            'admin_logged_in' => true,
            'admin_user_id' => $user->id,
            'admin_user_name' => $user->name,
            'admin_user_email' => $user->email,
            'admin_account_type' => $accountType,
        ]);

        // Regenerate session để bảo mật
        $request->session()->regenerate();

        return redirect()->route('admin.dashboard')
            ->with('success', 'Đăng nhập thành công! Chào mừng ' . $user->name);
    }

    /**
     * Đăng xuất
     */
    public function logout(Request $request)
    {
        // Xóa thông tin session
        session()->forget([
            'admin_logged_in',
            'admin_user_id',
            'admin_user_name',
            'admin_user_email',
            'admin_account_type',
        ]);

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('admin.login')
            ->with('success', 'Đã đăng xuất thành công');
    }
}
