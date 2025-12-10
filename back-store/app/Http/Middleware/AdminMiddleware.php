<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Nếu user tồn tại và role = admin thì cho qua
        if ($user && $user->role === 'admin') {
            return $next($request);
        }

        // Nếu không phải admin thì chặn lại
        return response()->json([
            'message' => 'Chỉ admin mới được truy cập khu vực này.'
        ], 403);
    }
}
