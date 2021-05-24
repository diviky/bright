<?php

declare(strict_types=1);

namespace Diviky\Bright\Providers;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    /**
     * Register the application services.
     */
    public function boot(): void
    {
    }

    protected function path(): string
    {
        return __DIR__ . '/../..';
    }
}
