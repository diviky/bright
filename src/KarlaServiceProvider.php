<?php

namespace Karla;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use Karla\Routing\Redirector;
use Karla\Routing\Resolver;
use Karla\Traits\Provider;

class KarlaServiceProvider extends ServiceProvider
{
    use Provider;

    public function boot()
    {
        $this->publishes([
            __DIR__ . '/../config/permission.php' => config_path('permission.php'),
            __DIR__ . '/../config/karla.php' => config_path('karla.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../resources/assets/js' => resource_path('assets/js'),
        ], 'resources');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations/');
        $this->loadViewsFrom(__DIR__ . '/../resources/views/', 'karla');

        Schema::defaultStringLength(191);
        $this->directive();
        $this->macros();
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/permission.php', 'permission');
        $this->mergeConfigFrom(__DIR__ . '/../config/karla.php', 'karla');

        $this->redirect();
        $this->app->bind('Illuminate\Routing\Contracts\ControllerDispatcher', 'Karla\Routing\ControllerDispatcher');

        $this->app->singleton('resolver', function ($app) {
            return new Resolver($app);
        });
    }

    public function redirect()
    {
        $this->app->singleton('redirect', function ($app) {
            $redirector = new Redirector($app['url']);
            // If the session is set on the application instance, we'll inject it into
            // the redirector instance. This allows the redirect responses to allow
            // for the quite convenient "with" methods that flash to the session.
            if (isset($app['session.store'])) {
                $redirector->setSession($app['session.store']);
            }
            return $redirector;
        });
    }
}
