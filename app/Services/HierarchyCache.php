<?php

namespace App\Services;

use Closure;
use Illuminate\Support\Facades\Cache;

class HierarchyCache
{
    private const KEY = 'hierarchy.structure.v4';

    public static function remember(Closure $callback): array
    {
        return Cache::rememberForever(self::KEY, $callback);
    }

    public static function forget(): void
    {
        Cache::forget(self::KEY);
    }
}
