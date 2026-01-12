<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Admin;
use App\Models\Staff;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Chưa đăng nhập'
            ], 401);
        }

        // Kiểm tra tài khoản có active không
        if (isset($user->active) && !$user->active) {
            return response()->json([
                'message' => 'Tài khoản đã bị khóa'
            ], 403);
        }

        // Cho phép Admin (super_admin hoặc admin)
        if ($user instanceof Admin) {
            return $next($request);
        }

        // Cho phép Staff (manager hoặc staff)
        if ($user instanceof Staff) {
            return $next($request);
        }

        // Nếu không thuộc Admin hoặc Staff thì chặn
        return response()->json([
            'message' => 'Chỉ admin hoặc nhân viên mới được truy cập khu vực này.'
        ], 403);
    }
}
