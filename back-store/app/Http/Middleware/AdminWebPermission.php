<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

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
        /** @var \App\Models\User $user */
        $user = Auth::user();

        if (!$user) {
            return redirect()->route('admin.login')
                ->with('error', 'Vui lòng đăng nhập để tiếp tục');
        }

        // Admin có toàn quyền
        if ($user->role === 'admin') {
            return $next($request);
        }

        // Nếu là staff thì kiểm tra quyền
        if ($user->role === 'staff') {
            foreach ($permissions as $permission) {
                if (!method_exists($user, 'hasPermission') || !$user->hasPermission($permission)) {
                    return back()->with('error', 'Bạn không có quyền thực hiện hành động này');
                }
            }
            return $next($request);
        }

        // Nếu không phải admin hoặc staff
        return abort(403, 'Tài khoản không hợp lệ');
    }
}
