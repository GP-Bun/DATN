<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    /**
     * Kiểm tra quyền truy cập
     *
     * Sử dụng: middleware('permission:view_products')
     * Hoặc nhiều quyền: middleware('permission:view_products,edit_products')
     */
    public function handle(Request $request, Closure $next, string ...$permissions)
    {
        // /** @var \App\Models\User $user */
        $user = Auth::user();

        // Nếu chưa đăng nhập
        if (!$user) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Chưa đăng nhập'], 401)
                : redirect()->route('admin.login')->with('error', 'Vui lòng đăng nhập');
        }

        // Nếu user không có method hasPermission
        if (!method_exists($user, 'hasPermission')) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Không có quyền truy cập'], 403)
                : abort(403, 'Tài khoản không hỗ trợ phân quyền');
        }

        // Kiểm tra ít nhất 1 quyền phù hợp
        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission)) {
                return $next($request);
            }
        }

        return $request->expectsJson()
            ? response()->json([
                'message' => 'Bạn không có quyền thực hiện hành động này',
                'required_permissions' => $permissions,
            ], 403)
            : back()->with('error', 'Bạn không có quyền thực hiện hành động này');
    }
}
