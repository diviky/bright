<?php

namespace Karla\Providers;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    protected function path()
    {
        return __DIR__ . '/../..';
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->mergeConfigFrom($this->path() . '/config/permission.php', 'permission');
    }
}
