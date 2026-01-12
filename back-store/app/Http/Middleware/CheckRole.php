<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = Auth::user();

        // Nếu chưa đăng nhập
        if (!$user) {
            return $request->expectsJson()
                ? response()->json(['message' => 'Chưa đăng nhập'], 401)
                : redirect()->route('admin.login')->with('error', 'Vui lòng đăng nhập');
        }

        // Nếu không có role phù hợp
        if (!in_array($user->role, $roles)) {
            return $request->expectsJson()
                ? response()->json([
                    'message' => 'Bạn không có quyền truy cập',
                    'your_role' => $user->role,
                    'required_roles' => $roles,
                ], 403)
                : abort(403, 'Bạn không có quyền truy cập');
        }

        return $next($request);
    }
}
