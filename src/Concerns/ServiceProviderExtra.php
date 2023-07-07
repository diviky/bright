<?php

declare(strict_types=1);

namespace Diviky\Bright\Concerns;

use Illuminate\Contracts\Foundation\CachesConfiguration;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

trait ServiceProviderExtra
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

    /**
     * Register factories.
     *
     * @param array|string $namespace
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
}
