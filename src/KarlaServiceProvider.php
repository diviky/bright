<?php

namespace Karla;

use Illuminate\Support\ServiceProvider;

class KarlaServiceProvider extends ServiceProvider
{
    public function boot(PermissionRegistrar $permissionLoader)
    {
        $this->publishes([
            __DIR__ . '/../config/permission.php' => config_path('permission.php'),
            __DIR__ . '/../config/karla.php' => config_path('karla.php'),
        ], 'config');
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/permission.php', 'permission');
        $this->mergeConfigFrom(__DIR__ . '/../config/karla.php', 'karla');

        $this->loadViewsFrom(__DIR__ . '/../views/', 'karla');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations/');
    }
}
