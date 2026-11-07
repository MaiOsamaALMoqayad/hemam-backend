<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PageContent extends Model
{
    public const CACHE_KEY = 'page_contents:all';

    protected $fillable = ['key', 'type', 'value', 'updated_by'];

    /**
     * Every saved value as [key => value], limited to keys that are still
     * in config/page_content.php (a key dropped from the config disappears
     * from the site instead of lingering).
     */
    public static function allValues(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            $allowed = array_keys(config('page_content.keys', []));

            return self::whereIn('key', $allowed)->pluck('value', 'key')->all();
        });
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
