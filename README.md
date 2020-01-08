# Laravel Cache Partial Blade Directive

[![Latest Version on Packagist](https://img.shields.io/packagist/v/pixxet/laravel-partialcache.svg?style=flat-square)](https://packagist.org/packages/pixxet/laravel-partialcache)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![StyleCI](https://styleci.io/repos/232058414/shield?branch=master)](https://styleci.io/repos/232058414)
[![Total Downloads](https://img.shields.io/packagist/dt/pixxet/laravel-partialcache.svg?style=flat-square)](https://packagist.org/packages/pixxet/laravel-partialcache)

This package provides a Blade directive for Laravel >=6.0 to cache rendered partials in Laravel.

## Install

You can install the package via Composer:

```bash
$ composer require pixxet/laravel-partialcache
```

In Laravel 6.0 the package's service provider and facade will be registered automatically. In older versions of Laravel, you must register them manually:

```php
// config/app.php

'providers' => [
  ...
  Pixxet\PartialCache\PartialCacheServiceProvider::class,
],

'aliases' => [
  ...
  'PartialCache' => Pixxet\PartialCache\PartialCacheFacade::class,
],
```

*The facade is optional, but the rest of this guide assumes you're using it.*

Optionally publish the config files:

```bash
$ php artisan vendor:publish --provider="Pixxet\PartialCache\PartialCacheServiceProvider"
```

## Usage

The package registers a blade directive, `@cache`. The cache directive accepts the same arguments as `@include`, plus optional parameters for the amount of minutes a view should be cached for, a key unique to the rendered view, and a cache tag for the rendered view. If no minutes are provided, the view will be remembered until you manually remove it from the cache.

Note that this caches the rendered html, not the rendered php like blade's default view caching.

```
{{-- Simple example --}}
@cache('footer.section.partial')

{{-- With extra view data --}}
@cache('products.card', ['product' => $category->products->first()])

{{-- With an added key (cache entry will be partialcache.user.profile.{$user->id}) --}}
@cache('user.profile', null, $user->id)

{{-- For a certain time --}}
{{-- (cache will invalidate in 60 minutes in this example, set null to remember forever) --}}
@cache('homepage.news', null, null, 60)
```

### Clearing The PartialCache

You can forget a partialcache entry with `PartialCache::forget($view, $key)`.

```php
PartialCache::forget('user.profile', $user->id);
```

If you want to flush all entries, you'll need to clear your entire cache.

### Configuration

Configuration isn't necessary, but there are three options specified in the config file:

- `partialcache.enabled`: Fully enable or disable the cache. Defaults to `true`.
- `partialcache.directive`: The name of the blade directive to register. Defaults to `cache`.
- `partialcache.key`: The base key that used for cache entries. Defaults to `partialcache`.
- `partialcache.default_duration`: The default cache duration in minutes, set `null` to remember forever. Defaults to `null`.

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security

If you discover any security related issues, please email me@omarsharkeyeh.me instead of using the issue tracker.

## Postcardware

You're free to use this package.

## Credits
- [Spatie](https://github.com/spatie)
- [Sebastian De Deyne](https://github.com/sebastiandedeyne)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.