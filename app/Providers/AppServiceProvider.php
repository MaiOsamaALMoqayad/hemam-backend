<?php

namespace App\Providers;

use App\Models\Camp; // أضف هذا السطر
use App\Observers\CampObserver; // أضف هذا السطر
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 1. Force Arabic as default
        app()->setLocale('ar');



        // 3. Rate Limiting للـ API العام
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->ip());
        });

        // 4. Rate Limiting للـ Contact Form (3 requests في الدقيقة)
        RateLimiter::for('contact', function (Request $request) {
            return Limit::perMinute(3)->by($request->ip());
        });

        // 5. Rate Limiting للـ Trainer Application (2 requests في الساعة)
        RateLimiter::for('trainer-application', function (Request $request) {
            return Limit::perHour(2)->by($request->ip());
        });

        // 6. Rate Limiting للـ Consultation Form (3 requests في الساعة)
        RateLimiter::for('consultation', function (Request $request) {
            $key = $request->ip();
            if ($request->has('whatsapp')) {
                $key .= ':' . $request->whatsapp;
            }
            return Limit::perHour(3)->by($key);
        });

        // 7. Rate Limiting للـ Admin APIs
        RateLimiter::for('admin', function (Request $request) {
            return Limit::perMinute(100)->by($request->user()?->id ?: $request->ip());
        });
    }
}
