<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    /**
     * Kiểm tra role của user
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  ...$roles  Các role được phép truy cập
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string ...$roles)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Chưa đăng nhập',
            ], 401);
        }

        // Lấy role của user
        $userRole = $user->role ?? null;

        if (!$userRole || !in_array($userRole, $roles)) {
            return response()->json([
                'message' => 'Bạn không có quyền truy cập trang này',
                'your_role' => $userRole,
                'required_roles' => $roles,
            ], 403);
        }

        return $next($request);
    }
}
