<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Helpers\SettingsHelper;
use Illuminate\Support\Facades\Log;

class SettingController extends Controller
{
    /**
     * Display all settings.
     *
     * GET /api/v1/settings
     *
     * Response:
     * {
     *   "site_name": "همم للتطوير والتدريب",
     *   "about_us": "نحن مؤسسة تهدف إلى...",
     *   "contact_email": "info@hemam.com",
     *   "contact_phone": "+970599123456",
     *   "facebook_url": "https://facebook.com/hemam",
     *   "instagram_url": "https://instagram.com/hemam",
     *   "twitter_url": "https://twitter.com/hemam"
     * }
     */
    public function index()
    {
        try {
            // استخدام SettingsHelper للحصول على كل الإعدادات
            // البيانات محفوظة في Cache تلقائياً داخل الـ Helper
            $settings = SettingsHelper::getAll();

            return response()->json($settings);

        } catch (\Throwable $e) {
            Log::error('Settings API Error: ' . $e->getMessage());

            return response()->json([
                'status' => false,
                'message' => 'حدث خطأ أثناء جلب الإعدادات',
            ], 500);
        }
    }
}