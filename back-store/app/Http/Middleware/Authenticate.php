<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    protected function redirectTo($request)
    {
        // Nếu request API => trả 401 JSON
        if ($request->expectsJson() || $request->is('api/*')) {
            return null;
        }
    
        // Nếu có login web thì trả về route login
        // Nếu không có thì trả null luôn
        return null;
    }
    
}