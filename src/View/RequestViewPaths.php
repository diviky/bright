<?php

declare(strict_types=1);

namespace Diviky\Bright\View;

use Illuminate\View\Component;

/**
 * Per-request (and per-Swoole-coroutine) view search paths.
 *
 * Controllers and Livewire components add colocated view directories temporarily.
 * Mutating the shared view finder races under concurrent Octane workers; store
 * paths here and merge them during view resolution (for example in ThemeViewFinder).
 */
final class RequestViewPaths
{
    private const CONTEXT_KEY = 'bright.request_view_paths';

    public static function reset(): void
    {
        Component::forgetFactory();

        if (self::inCoroutine()) {
            $context = self::octaneContextClass();
            $context::delete(self::CONTEXT_KEY);

            return;
        }

        if (app()->bound(RequestViewPathsStore::class)) {
            app(RequestViewPathsStore::class)->locations = [];
        }
    }

    /**
     * @return list<string>
     */
    public static function locations(): array
    {
        return self::store()->locations;
    }

    /**
     * @param  list<string>  $paths
     */
    public static function prependLocations(array $paths): void
    {
        $paths = array_filter($paths);

        if ($paths === []) {
            return;
        }

        $store = self::store();

        foreach ($paths as $path) {
            array_unshift($store->locations, str_replace('//', '/', $path));
        }
    }

    private static function store(): RequestViewPathsStore
    {
        if (self::inCoroutine()) {
            $context = self::octaneContextClass();
            $store = $context::get(self::CONTEXT_KEY);

            if (! $store instanceof RequestViewPathsStore) {
                $store = new RequestViewPathsStore();
                $context::set(self::CONTEXT_KEY, $store);
            }

            return $store;
        }

        return app(RequestViewPathsStore::class);
    }

    private static function inCoroutine(): bool
    {
        return class_exists(\Swoole\Coroutine::class)
            && class_exists(self::octaneContextClass())
            && \Swoole\Coroutine::getCid() > 0;
    }

    /**
     * @return class-string
     */
    private static function octaneContextClass(): string
    {
        return 'Laravel\\Octane\\Swoole\\Coroutine\\Context';
    }

}
