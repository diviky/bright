<?php

declare(strict_types=1);

namespace Diviky\Bright\Tests;

use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    protected function setUp(): void
    {
        parent::setUp();
    }

    protected function getPackageProviders($app)
    {
        return [
            \Diviky\Bright\Providers\BrightServiceProvider::class,
            \Diviky\Bright\Providers\ShardingServiceProvider::class,
            \Diviky\Bright\Providers\DatabaseServiceProvider::class,
            \Diviky\Bright\Providers\ServiceProvider::class,
            \Diviky\Bright\Providers\ViewServiceProvider::class,
        ];
    }

    // Rely on Testbench to register core framework providers

    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Set up basic Laravel configuration for package testing
        $app['config']->set('app.name', 'Laravel');
        $app['config']->set('app.key', 'base64:m+pDa0MKS1KpMlxzzdVEaqFHysv3IPhrx/3TFSWBqJA=');
        $app['config']->set('app.debug', true);
        $app['config']->set('cache.default', 'array');
        $app['config']->set('session.driver', 'array');

        // Ensure database connection is properly configured
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
        ]);

        // Load package configuration
        $app['config']->set('bright', require __DIR__ . '/../config/bright.php');

        // Ensure router and view are booted so facades are available
        $app->make('router');
        $app->make('view');
        $app->make('validator');
    }
}
