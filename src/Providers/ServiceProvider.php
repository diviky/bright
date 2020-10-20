<?php

namespace Diviky\Bright\Providers;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register the application services.
     */
    public function boot()
    {
    }

    protected function path()
    {
        return __DIR__ . '/../..';
    }
}
