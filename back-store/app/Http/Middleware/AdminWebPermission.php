<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Admin;
use App\Models\Staff;

class AdminWebPermission
{
    /**
     * Kiểm tra quyền truy cập cho nhân viên trên web admin
     *
     * Nhân viên KHÔNG được:
     * - Thống kê doanh thu
     * - Quản lý danh mục (tạo/xóa)
     * - Quản lý mã giảm giá
     * - Quản lý tài khoản
     */
    public function handle(Request $request, Closure $next, string ...$permissions): Response
    {
        $accountType = session('admin_account_type');
        $userId = session('admin_user_id');

        // Admin có tất cả quyền
        if ($accountType === 'admin') {
            return $next($request);
        }

        // Lấy staff từ database
        $staff = Staff::find($userId);

        if (!$staff) {
            return redirect()->route('admin.login')
                ->with('error', 'Phiên đăng nhập hết hạn');
        }

        // Kiểm tra quyền
        foreach ($permissions as $permission) {
            if (!$staff->hasPermission($permission)) {
                return back()->with('error', 'Bạn không có quyền thực hiện hành động này');
            }
        }

        return $next($request);
    }
}
