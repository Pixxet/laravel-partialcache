<?php

namespace Pixxet\PartialCache;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Cache;

class PartialCache {

    static $baseCacheKey = null;

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
        if (!config('partialcache.enabled')) {
            return self::notCachedRendering($expression);
        }
        [$path, $dataParams, $varyBy, $ttl] = self::parseExpression($expression);

        $cacheKey = self::getCacheKey($path, $varyBy);
        $ttl = self::prepareTTL($ttl);
        $expression = self::wrapNewExpression($path, $dataParams);

        return "
        <?php 
            \$definedVariables = get_defined_vars();
            echo Cache::remember({$cacheKey}, {$ttl}, function() use (\$definedVariables) {
                extract(\$definedVariables);
                return \$definedVariables['__env']->make({$expression}, \Illuminate\Support\Arr::except(\$definedVariables, ['__data', '__path']))->render();
            });
        ?>\n";
    }

    protected static function parseExpression($expression): array
    {
        $expression = Blade::stripParentheses($expression);
        preg_match('/^([\'"][a-zA-Z.]+[\'"])\s*(?:,\s*(\[.*]))\s*(?:,\s*(.*(?=,)|.*[^,]))?\s*(?:,\s*([\d]+))?$/', $expression, $matches);
        if (!$matches) {
            throw new \RuntimeException('Syntax error');
        }
        // no need for all matches
        unset($matches[0]);

        return array_pad($matches, 4, false);
    }

    /**
     * @param string $view
     * @param string|null $varyBy
     *
     * @return string
     */
    protected static function getCacheKey($view, $varyBy=null): string
    {
        $baseCacheKey = self::$baseCacheKey ?: self::initBaseCacheKey();

        if (!$varyBy) {
            return $view;
        }

        return "'{}.'.{$view}.'-'.{$varyBy}";
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
        if ($ttl === false) {
            $ttl = (int)config('partialcache.default_duration', 60);
        }

        return $ttl;
    }

    /**
     * @param string $path
     * @param string|null $dataParams
     *
     * @return string
     */
    protected static function wrapNewExpression($path, $dataParams=null)
    {
        if (!$dataParams) {
            return $path;
        }

        return "{$path}, {$dataParams}";
    }
}