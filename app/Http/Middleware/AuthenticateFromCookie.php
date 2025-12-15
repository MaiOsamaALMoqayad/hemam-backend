<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateFromCookie
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // إذا في Authorization header، استخدمه (للـ API testing)
        if ($request->bearerToken()) {
            return $next($request);
        }

        // إذا ما في، جيب التوكن من الـ Cookie
     if ($token = $request->cookie('admin_token')) {
    Log::info('Cookie token found: '.$token);
    $request->headers->set('Authorization', 'Bearer ' . $token);
}


        return $next($request);
    }
}
