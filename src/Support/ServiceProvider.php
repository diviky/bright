<?php

namespace Diviky\Bright\Support;

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
        $config = $this->app['config']->get($key, []);

        if ($force) {
            $config = \array_merge_recursive($config, require $path);
        } else {
            $config = \array_merge_recursive(require $path, $config);
        }

        $this->app['config']->set($key, $config);
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
        $config = $this->app['config']->get($key, []);

        if ($force) {
            $config = \array_replace_recursive($config, require $path);
        } else {
            $config = \array_replace_recursive(require $path, $config);
        }

        $this->app['config']->set($key, $config);
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
        $config = $this->app['config']->get($key, []);

        if ($force) {
            $config = \array_merge($config, require $path);
        } else {
            $config = \array_merge(require $path, $config);
        }

        $this->app['config']->set($key, $config);
    }
}
