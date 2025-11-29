<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'key',
        'value',
        'type',
        'group',
    ];

    protected $casts = [
        'value' => 'array',
        'updated_at' => 'datetime',
    ];

    /**
     * Get setting by key
     */
    public static function get($key, $default = null)
    {
        return Cache::remember("setting:{$key}", 3600, function () use ($key, $default) {
            $setting = self::where('key', $key)->first();

            if (!$setting) {
                return $default;
            }

            $locale = app()->getLocale();
            return $setting->value[$locale] ?? $setting->value['ar'] ?? $default;
        });
    }

    /**
     * Set setting value
     */
    public static function set($key, $value, $type = 'text', $group = 'general')
    {
        $setting = self::updateOrCreate(
            ['key' => $key],
            [
                'value' => $value,
                'type' => $type,
                'group' => $group,
            ]
        );

        Cache::forget("setting:{$key}");
        Cache::forget('settings:all');

        return $setting;
    }

    /**
     * Get all settings
     */
    public static function getAll()
    {
        return Cache::remember('settings:all', 3600, function () {
            $settings = self::all();
            $result = [];
            $locale = app()->getLocale();

            foreach ($settings as $setting) {
                $result[$setting->key] = $setting->value[$locale] ?? $setting->value['ar'] ?? null;
            }

            return $result;
        });
    }

    /**
     * Clear cache
     */
    public static function clearCache()
    {
        Cache::forget('settings:all');
        self::all()->each(function ($setting) {
            Cache::forget("setting:{$setting->key}");
        });
    }
}
