<?php

namespace Diviky\Bright\Providers;

use Diviky\Bright\Database\Sharding\MapManager;
use Diviky\Bright\Database\Sharding\ShardManager;
use Illuminate\Support\ServiceProvider;

class ShardingServiceProvider extends ServiceProvider
{
    protected function path()
    {
        return __DIR__ . '/../..';
    }

    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->console();
        }
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->mergeConfigFrom($this->path() . '/config/sharding.php', 'sharding');

        $this->registerConnectionManager();
        $this->app->bind('bright.shardmanager', function () {
            return new ShardManager(
                $this->app['bright.shard.mapmanager']
            );
        });
    }

    /**
     * Register the bindings for the ConnectionManager.
     */
    protected function registerConnectionManager()
    {
        $this->app->bind('bright.shard.mapmanager', function ($app) {
            $map = config('sharding.map');

            return new MapManager($map);
        });
    }

    protected function console()
    {
        $this->publishes([
            __DIR__ . '/config/sharding.php' => config_path('sharding.php'),
        ], 'bright:config');
    }
}
