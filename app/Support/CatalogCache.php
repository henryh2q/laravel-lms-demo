<?php

namespace App\Support;

use Closure;
use Illuminate\Support\Facades\Cache;

/**
 * Versioned cache for public catalog pages: writes bump the version, so stale
 * entries are simply never read again (no tag support needed in the store).
 */
class CatalogCache
{
    private const VERSION_KEY = 'catalog:version';

    public static function remember(string $key, Closure $callback, int $seconds = 600): mixed
    {
        $version = Cache::rememberForever(self::VERSION_KEY, fn () => 1);

        return Cache::remember("catalog:v{$version}:{$key}:".app()->getLocale(), $seconds, $callback);
    }

    public static function bump(): void
    {
        Cache::forever(self::VERSION_KEY, ((int) Cache::get(self::VERSION_KEY, 1)) + 1);
    }
}
