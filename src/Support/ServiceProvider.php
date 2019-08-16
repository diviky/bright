<?php

namespace Karla\Support;

use Illuminate\Support\ServiceProvider as LaravelServiceProvider;

class ServiceProvider extends LaravelServiceProvider
{
    /**
     * Merge the given configuration with the existing configuration.
     *
     * @param string $path
     * @param string $key
     */
    protected function mergeConfigRecursive($path, $key)
    {
        $config = $this->app['config']->get($key, []);

        $this->app['config']->set($key, \array_merge_recursive(require $path, $config));
    }
}
