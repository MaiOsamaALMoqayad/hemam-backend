<?php

namespace App\Helpers;

use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

class SettingsHelper
{
    public static function getAll(): array
    {
        return Cache::remember('settings:all', 3600, function () {
            $settings = Setting::all();
            $result = [];
            $locale = app()->getLocale();

            foreach ($settings as $setting) {
                $value = $setting->value;

                if (is_string($value)) {
                    $value = json_decode($value, true);
                }

                $result[$setting->key] = $value[$locale] ?? $value['ar'] ?? null;
            }

            return $result;
        });
    }

    public static function clearCache(): void
    {
        Cache::forget('settings:all');
    }
}
