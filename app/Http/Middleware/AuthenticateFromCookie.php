<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class AuthenticateFromCookie
{
    public function handle(Request $request, Closure $next): Response
    {
        try {
            // إذا في Authorization header، استخدمه (للـ Insomnia/Postman)
            if ($request->bearerToken()) {
                return $next($request);
            }

            // جيب التوكن من الـ Cookie
            $token = $request->cookie('admin_token');

            if ($token) {
                // فك التشفير إذا كان مشفر (لحماية الـ "|" حرف)
                $token = urldecode($token);
                
                // أضف التوكن للـ Authorization header
                $request->headers->set('Authorization', 'Bearer ' . $token);

                Log::debug('Token loaded from cookie', [
                    'has_token' => !empty($token),
                    'token_prefix' => substr($token, 0, 10) . '...'
                ]);
            }

            return $next($request);

        } catch (\Exception $e) {
            Log::error('AuthenticateFromCookie Error: ' . $e->getMessage());
            return $next($request);
        }
    }
}
