<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminWebAuth
{
    /**
     * Kiểm tra đăng nhập cho trang admin web
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Kiểm tra session đăng nhập
        if (!session('admin_logged_in')) {
            return redirect()->route('admin.login')
                ->with('error', 'Vui lòng đăng nhập để truy cập trang quản trị');
        }

        return $next($request);
    }
}
