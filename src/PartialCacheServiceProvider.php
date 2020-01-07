<?php

namespace Pixxet\PartialCache;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

class PartialCacheServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../resources/config/partialcache.php', 'partialcache');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../resources/config/partialcache.php' => config_path('partialcache.php'),
        ], 'config');

        $directive = config('partialcache.directive', 'cache');

        Blade::directive($directive, [PartialCache::class, 'render']);
    }
}
