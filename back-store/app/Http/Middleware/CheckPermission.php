<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

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
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Chưa đăng nhập',
            ], 401);
        }

        // Kiểm tra user có method hasPermission không
        if (!method_exists($user, 'hasPermission')) {
            return response()->json([
                'message' => 'Không có quyền truy cập',
            ], 403);
        }

        // Kiểm tra ít nhất 1 quyền phù hợp
        foreach ($permissions as $permission) {
            if ($user->hasPermission($permission)) {
                return $next($request);
            }
        }

        return response()->json([
            'message' => 'Bạn không có quyền thực hiện hành động này',
            'required_permissions' => $permissions,
        ], 403);
    }
}
