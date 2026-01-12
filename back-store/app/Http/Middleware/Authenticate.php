<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo($request): ?string
{
    if ($request->expectsJson() || $request->is('api/*')) {
        return null;
    }

    // Nếu URL bắt đầu bằng /admin thì redirect về trang đăng nhập admin
    if ($request->is('admin/*')) {
        return route('admin.login');
    }

    // Nếu là người dùng thường (nếu có)
    return route('login');
}

    
}