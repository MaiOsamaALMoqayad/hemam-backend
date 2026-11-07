<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PageContent extends Model
{
    public const CACHE_KEY = 'page_contents:all';

    /** Folder on the public disk holding uploaded content images. */
    public const IMAGE_DIR = 'page-content';

    protected $fillable = ['key', 'type', 'value', 'updated_by'];

    /** The config entry for a key, or null when the key isn't editable. */
    public static function definition(string $key): ?array
    {
        $keys = config('page_content.keys', []);

        return $keys[$key] ?? null;
    }

    /**
     * Saved rows as [key => ['type' => ..., 'value' => ...]], limited to keys
     * still in config/page_content.php (a key dropped from the config
     * disappears from the site instead of lingering).
     */
    public static function rows(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, function () {
            $allowed = array_keys(config('page_content.keys', []));

            return self::whereIn('key', $allowed)
                ->get(['key', 'type', 'value'])
                ->mapWithKeys(fn ($row) => [$row->key => ['type' => $row->type, 'value' => $row->value]])
                ->all();
        });
    }

    /**
     * Saved values as the site consumes them: text as a string, a link as
     * {label, url}, an image as its absolute URL. URLs are built per request
     * (not cached), so they follow the host the API is served from.
     */
    public static function publicValues(): array
    {
        $values = [];

        foreach (self::rows() as $key => $row) {
            $values[$key] = match ($row['type']) {
                'link'  => json_decode($row['value'], true) ?: ['label' => '', 'url' => ''],
                'list'  => json_decode($row['value'], true) ?: [],
                'image' => asset('storage/' . $row['value']),
                default => $row['value'],
            };
        }

        return $values;
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
