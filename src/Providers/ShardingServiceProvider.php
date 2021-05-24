<?php

declare(strict_types=1);

namespace Diviky\Bright\Providers;

use Diviky\Bright\Database\Sharding\MapManager;
use Diviky\Bright\Database\Sharding\ShardManager;
use Illuminate\Support\ServiceProvider;

/**
 * @SuppressWarnings(PHPMD)
 */
class ShardingServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->console();
        }
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom($this->path() . '/config/sharding.php', 'sharding');

        $this->registerConnectionManager();
        $this->app->bind('bright.shardmanager', function () {
            return new ShardManager(
                $this->app['bright.shard.mapmanager']
            );
        });
    }

    protected function path(): string
    {
        return __DIR__ . '/../..';
    }

    /**
     * Register the bindings for the ConnectionManager.
     */
    protected function registerConnectionManager(): void
    {
        $this->app->bind('bright.shard.mapmanager', function ($app) {
            $map = config('sharding.map');

            return new MapManager($map);
        });
    }

    protected function console(): void
    {
        $this->publishes([
            __DIR__ . '/config/sharding.php' => config_path('sharding.php'),
        ], 'bright:config');
    }
}
