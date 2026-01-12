<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use App\Models\Staff;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Đăng nhập Admin hoặc Nhân viên
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required'    => 'Email không được để trống',
            'email.email'       => 'Email phải là email hợp lệ',
            'password.required' => 'Mật khẩu không được để trống',
        ]);

        // Thử tìm trong bảng admins trước
        $admin = Admin::where('email', $request->email)
            ->where('active', true)
            ->first();

        // Nếu không tìm thấy, thử tìm trong bảng staffs
        $staff = null;
        if (!$admin) {
            $staff = Staff::where('email', $request->email)
                ->where('active', true)
                ->first();
        }

        // Xác định tài khoản phù hợp
        $actor = $admin ?? $staff;
        $accountType = $admin ? 'admin' : ($staff ? 'staff' : null);

        if (!$actor || !Hash::check($request->password, $actor->password)) {
            return response()->json(['message' => 'Sai tài khoản hoặc mật khẩu'], 401);
        }

        $token = $actor->createToken('admin_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type'   => 'Bearer',
            'account_type' => $accountType,
            'user'         => [
                'id'    => $actor->id,
                'name'  => $actor->name,
                'email' => $actor->email,
            ],
            'permissions' => $this->getPermissions($actor),
        ]);
    }

    // Lấy thông tin người dùng hiện tại
    public function me(Request $request)
    {
        $user = $request->user();
        $accountType = $user instanceof Admin ? 'admin' : 'staff';

        return response()->json([
            'account_type' => $accountType,
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
            ],
            'permissions' => $this->getPermissions($user),
        ]);
    }

    // Lấy danh sách quyền của user
    private function getPermissions($user): array
    {
        $allPermissions = [
            // Dashboard
            'view_dashboard',

            // Sản phẩm
            'view_products',
            'create_products',
            'edit_products',
            'delete_products',

            // Danh mục
            'view_categories',
            'manage_categories',

            // Đơn hàng
            'view_orders',
            'update_orders',
            'confirm_orders',
            'delete_orders',

            // Đánh giá
            'view_reviews',
            'reply_reviews',
            'delete_reviews',

            // Mã giảm giá
            'view_coupons',
            'manage_coupons',

            // Thống kê
            'view_statistics',
            'view_revenue',

            // Quản lý tài khoản
            'manage_users',
            'manage_staff',
            'manage_admins',
        ];

        $permissions = [];
        foreach ($allPermissions as $permission) {
            if ($user->hasPermission($permission)) {
                $permissions[] = $permission;
            }
        }

        return $permissions;
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
        return response()->json(['message' => 'Chào mừng bạn đến trang quản trị!']);
    }
}
