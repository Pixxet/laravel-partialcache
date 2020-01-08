<?php

namespace Pixxet\PartialCache;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;

class PartialCache
{
    public static $baseCacheKey = null;

    /**
     * Forget a rendered view.
     *
     * @param string $view
     * @param string $varyBy
     *
     * @return void
     */
    public static function forget($view, $varyBy = null)
    {
        $cacheKey = self::getCacheKey($view, $varyBy);
        Cache::forget($cacheKey);
    }

    /**
     * @param string $expression
     *
     * @return string
     */
    public static function render($expression): string
    {
        return self::renderCache('cache', $expression);
    }

    /**
     * @param string $expression
     *
     * @return string
     */
    public static function renderIf($expression): string
    {
        return self::renderCache('cacheIf', $expression);
    }

    /**
     * @param string $expression
     *
     * @return string
     */
    public static function renderWhen($expression): string
    {
        return self::renderCache('cacheWhen', $expression);
    }

    /**
     * @param array $data
     * @param string $view
     * @param array $mergeData
     * @param string|null $varyBy
     * @param int|null $ttl
     *
     * @return string
     */
    public static function cache(array $data, string $view, array $mergeData = [], string $varyBy = null, int $ttl = null)
    {
        return self::cacheView($data, $view, $mergeData, $varyBy, $ttl);
    }

    /**
     * @param array $data
     * @param string $view
     * @param array $mergeData
     * @param string|null $varyBy
     * @param int|null $ttl
     *
     * @return string
     */
    public static function cacheIf(array $data, string $view, array $mergeData = [], string $varyBy = null, int $ttl = null)
    {
        if (!View::exists($view)) {
            return '';
        }

        return self::cacheView($data, $view, $mergeData, $varyBy, $ttl);
    }

    /**
     * @param array $data
     * @param bool $condition
     * @param string $view
     * @param array $mergeData
     * @param string|null $varyBy
     * @param int|null $ttl
     *
     * @return string
     */
    public static function cacheWhen(array $data, bool $condition, string $view, array $mergeData = [], string $varyBy = null, int $ttl = null)
    {
        if ($condition !== true) {
            return '';
        }

        return self::cacheView($data, $view, $mergeData, $varyBy, $ttl);
    }

    /**
     * @param string $directive
     * @param string $expression
     *
     * @return string
     */
    protected static function renderCache($directive, $expression): string
    {
        if (!config('partialcache.enabled')) {
            return self::notCachedRendering($expression);
        }

        return "\n<?php echo PartialCache::{$directive}(\Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']), {$expression}); ?>\n";
    }

    /**
     * @param array $data
     * @param string $view
     * @param array $mergeData
     * @param string|null $varyBy
     * @param int|null $ttl
     *
     * @return string
     */
    protected static function cacheView(array $data, string $view, array $mergeData = [], string $varyBy = null, int $ttl = null)
    {
        $cacheKey = self::getCacheKey($view, $varyBy);
        $ttl = self::prepareTTL($ttl);

        return Cache::remember($cacheKey, $ttl, function() use ($view, $data, $mergeData) {
            return View::make($view, $data, $mergeData)->render();
        });
    }

    /**
     * @param string      $view
     * @param string|null $varyBy
     *
     * @return string
     */
    protected static function getCacheKey($view, $varyBy = null): string
    {
        $baseCacheKey = self::$baseCacheKey ?: self::initBaseCacheKey();

        if (!$varyBy) {
            return $view;
        }

        return "'{$baseCacheKey}.'.{$view}.'-'.{$varyBy}";
    }

    /**
     * @return string
     */
    protected static function initBaseCacheKey(): string
    {
        self::$baseCacheKey = config('partialcache.key') ?? '';

        return self::$baseCacheKey;
    }

    /**
     * @param string $expression
     *
     * @return string
     */
    protected static function notCachedRendering($expression): string
    {
        return "
            <?php
               return \$__env->make({$expression}, \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render();
            ?>\n";
    }

    /**
     * @param int $ttl
     *
     * @return int
     */
    protected static function prepareTTL($ttl): int
    {
        if (!$ttl && $ttl !== 0) {
            $ttl = (int) config('partialcache.default_duration', 60);
        }

        return $ttl;
    }

    /**
     * @param string      $view
     * @param string|null $dataParams
     *
     * @return string
     */
    protected static function wrapNewExpression($view, $dataParams = null)
    {
        if (!$dataParams) {
            return $view;
        }

        return "{$view}, {$dataParams}";
    }
}
