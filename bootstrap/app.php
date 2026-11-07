<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->api(prepend: [
            \App\Http\Middleware\AuthenticateFromCookie::class,
        ]);

        // The public site (Vercel) proxies /api/* here, so every request
        // arrives from a Vercel IP. Trust X-Forwarded-For so $request->ip()
        // (and the per-IP rate limits) see the real visitor. Only that header:
        // trusting X-Forwarded-Host would make generated URLs (image_url etc.)
        // point at www.rowwadhimam.org, which doesn't serve /storage.
        $middleware->trustProxies(
            at: '*',
            headers: Request::HEADER_X_FORWARDED_FOR,
        );

        $middleware->throttleApi();
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
