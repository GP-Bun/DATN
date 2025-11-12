<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Xác định đường dẫn để redirect nếu chưa đăng nhập.
     */
    protected function redirectTo($request)
    {
        // Nếu request muốn nhận JSON (API / React), trả về JSON
        if ($request->expectsJson()) {
            abort(response()->json([
                'message' => 'Unauthorized – Bạn chưa đăng nhập!'
            ], 401));
        }

        // Nếu là request web thông thường, redirect về login
        return route('login'); // hoặc route admin login nếu cần
    }
}
