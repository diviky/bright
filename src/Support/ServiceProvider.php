<?php

declare(strict_types=1);

namespace Diviky\Bright\Support;

use Illuminate\Contracts\Foundation\CachesConfiguration;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    /**
     * Merge the given configuration with the existing configuration.
     *
     * @param string $path
     * @param string $key
     * @param bool   $force
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
     * @param string $path
     * @param string $key
     * @param bool   $force
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
     * @param string $path
     * @param string $key
     * @param bool   $force
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
}
