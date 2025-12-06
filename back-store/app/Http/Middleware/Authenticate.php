<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo($request)
    {
        // Nếu request là API → trả về JSON 401
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'Unauthorized – Bạn chưa đăng nhập!'
            ], 401);
        }

        // Nếu là request web → redirect về trang login web
        return route('login');
    }
}