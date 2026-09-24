<?php

namespace App\Services;

use Closure;
use Illuminate\Support\Facades\Cache;

class HierarchyCache
{
    private const KEY = 'hierarchy.structure.v6';

    public static function remember(Closure $callback): array
    {
        return Cache::rememberForever(self::KEY, $callback);
    }

    public static function forget(): void
    {
        Cache::forget(self::KEY);
        Cache::forever('hierarchy.statistics.version', (int) Cache::get('hierarchy.statistics.version', 1) + 1);
    }

    public static function statistics(int $categoryId, ?string $section = null, ?string $month = null, ?int $gradeId = null, Closure $callback): mixed
    {
        $version = Cache::get('hierarchy.statistics.version', 1);
        $key = sprintf('hierarchy.statistics.v%s.category.%d.%s.%s.%s', $version, $categoryId, $section ?? 'all', $month ?? 'all', $gradeId ?? 'all');

        return Cache::rememberForever($key, $callback);
    }
}
