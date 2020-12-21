<?php

namespace Diviky\Bright\Providers;

use Diviky\Bright\Database\Sharding\MapManager;
use Diviky\Bright\Database\Sharding\ShardManager;
use Illuminate\Support\ServiceProvider;

class ShardingServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        $this->publishes([
            __DIR__ . '/config/sharding.php' => config_path('sharding.php'),
        ], 'bright:config');
    }

    /**
     * Register the application services.
     */
    public function register()
    {
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
}
