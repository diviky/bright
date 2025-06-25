<?php

declare(strict_types=1);

namespace Diviky\Bright\Concerns;

use Illuminate\Contracts\Foundation\CachesConfiguration;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait ServiceProviderExtra
{
    /**
     * Merge the given configuration with the existing configuration.
     *
     * @param  string  $path
     * @param  string  $key
     * @param  bool  $force
     */
    protected function mergeConfigRecursive($path, $key, $force = false): void
    {
        if (!($this->app instanceof CachesConfiguration && $this->app->configurationIsCached())) {
            $config = $this->app['config']->get($key, []);

            if ($force) {
                $config = \array_merge_recursive($config, require $path);
            } else {
                $config = \array_merge_recursive(require $path, $config);
            }

            $this->app['config']->set($key, $config);
        }
    }

    /**
     * Merge the given configuration with the existing configuration.
     *
     * @param  string  $path
     * @param  string  $key
     * @param  bool  $force
     */
    protected function mergeConfigRecursiveDistinct($path, $key, $force = false): void
    {
        if (!($this->app instanceof CachesConfiguration && $this->app->configurationIsCached())) {
            $config = $this->app['config']->get($key, []);

            if ($force) {
                $config = static::arrayMergeRecursiveDistinct($config, require $path);
            } else {
                $config = static::arrayMergeRecursiveDistinct(require $path, $config);
            }

            $this->app['config']->set($key, $config);
        }
    }

    protected static function arrayMergeRecursiveDistinct(array $array1, array $array2): array
    {
        $merged = $array1;

        foreach ($array2 as $key => &$value) {
            if (is_array($value) && isset($merged[$key]) && is_array($merged[$key])) {
                // Check if both arrays are non-associative (indexed arrays)
                if (!Arr::isAssoc($value) && !Arr::isAssoc($merged[$key])) {
                    // For indexed arrays, merge and reindex with array_values to prevent index conflicts
                    $merged[$key] = array_values(array_unique(array_merge($merged[$key], $value)));
                } elseif (!Arr::isAssoc($value) || !Arr::isAssoc($merged[$key])) {
                    // If one is associative and one is not, treat as indexed array merge
                    $merged[$key] = array_values(array_unique(array_merge(
                        array_values($merged[$key]),
                        array_values($value)
                    )));
                } else {
                    // Both are associative arrays, merge recursively
                    $merged[$key] = static::arrayMergeRecursiveDistinct($merged[$key], $value);
                }
            } else {
                $merged[$key] = $value;
            }
        }

        return $merged;
    }

    /**
     * Merge the given configuration with the existing configuration.
     *
     * @param  string  $path
     * @param  string  $key
     * @param  bool  $force
     */
    protected function replaceConfigRecursive($path, $key, $force = false): void
    {
        if (!($this->app instanceof CachesConfiguration && $this->app->configurationIsCached())) {
            $config = $this->app['config']->get($key, []);

            if ($force) {
                $config = \array_replace_recursive($config, require $path);
            } else {
                $config = \array_replace_recursive(require $path, $config);
            }

            $this->app['config']->set($key, $config);
        }
    }

    /**
     * Merge the given configuration with the existing configuration.
     *
     * @param  string  $path
     * @param  string  $key
     * @param  bool  $force
     */
    protected function mergeConfigFrom($path, $key, $force = false): void
    {
        if (!($this->app instanceof CachesConfiguration && $this->app->configurationIsCached())) {
            $config = $this->app->make('config');

            if ($force) {
                $config->set($key, \array_merge(
                    $config->get($key, []),
                    require $path
                ));
            } else {
                $config->set($key, \array_merge(
                    require $path,
                    $config->get($key, [])
                ));
            }
        }
    }

    /**
     * Register factories.
     *
     * @param  array|string  $namespace
     */
    protected function registerFactoriesFor($namespace): void
    {
        $namespaces = !is_array($namespace) ? [$namespace] : $namespace;

        Factory::guessFactoryNamesUsing(function (string $modelName) use ($namespaces) {
            foreach ($namespaces as $namespace) {
                if (Str::startsWith($modelName, $namespace . '\\')) {
                    $namespace = explode('\\', $modelName);

                    return $namespace[0] . '\\' . $namespace[1] . '\\Database\\Factories\\' . class_basename($modelName) . 'Factory';
                }
            }

            return 'Database\\Factories\\' . class_basename($modelName) . 'Factory';
        });
    }

    protected function loadChanelsFrom($path)
    {
        require $path;
    }

    protected function loadFileFrom($path)
    {
        require $path;
    }
}
