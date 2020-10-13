<?php

namespace Karla\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Karla\Contracts\UtilInterface;
use Karla\Routing\Redirector;
use Karla\Routing\Resolver;
use Karla\Services\Auth\AccessTokenGuard;
use Karla\Services\Auth\AuthTokenGuard;
use Karla\Services\Auth\CredentialsGuard;
use Karla\Services\Auth\Providers\AccessTokenProvider;
use Karla\Services\Auth\Providers\AuthTokenProvider;
use Karla\Services\Auth\Providers\CredentialsProvider;
use Karla\Support\ServiceProvider;
use Karla\Traits\Provider;
use Karla\Util\Util;

class KarlaServiceProvider extends ServiceProvider
{
    use Provider;

    protected function path()
    {
        return __DIR__ . '/../..';
    }

    public function boot()
    {
        $this->directive();
        $this->macros();
        $this->validates();

        $this->loadRoutesFrom($this->path() . '/routes/web.php');
        $this->loadRoutesFrom($this->path() . '/routes/api.php');

        $this->replaceConfigRecursive($this->path() . '/config/auth.php', 'auth');

        $this->mergeConfigFrom($this->path() . '/config/charts.php', 'charts');
        // $this->mergeConfigFrom($this->path() . '/config/karla.php', 'karla');
        // $this->mergeConfigFrom($this->path() . '/config/permission.php', 'permission');
        $this->mergeConfigFrom($this->path() . '/config/theme.php', 'theme');

        $this->loadViewsFrom($this->path() . '/resources/views/', 'karla');

        $this->registerMiddlewares();
        $this->registerEvents();

        if ($this->app->runningInConsole()) {
            $this->console();
        }
    }

    public function register()
    {
        Schema::defaultStringLength(191);

        $this->mergeConfigFrom($this->path() . '/config/karla.php', 'karla');
        $this->mergeConfigFrom($this->path() . '/config/permission.php', 'permission');

        $this->redirect();
        $this->auth();
        $this->binds();
        $this->app->bind('Illuminate\Routing\Contracts\ControllerDispatcher', 'Karla\Routing\ControllerDispatcher');

        $this->app->singleton('resolver', function ($app) {
            return new Resolver($app);
        });
    }

    public function binds()
    {
        $this->app->singleton(UtilInterface::class, function ($app) {
            return new Util();
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

    protected function console()
    {
        $this->publishes([
            $this->path() . '/config/charts.php'     => config_path('charts.php'),
            $this->path() . '/config/permission.php' => config_path('permission.php'),
            $this->path() . '/config/karla.php'      => config_path('karla.php'),
            $this->path() . '/config/theme.php'      => config_path('theme.php'),
        ], 'karla-config');

        $this->publishes([
            $this->path() . '/resources/assets/js' => resource_path('js'),
        ], 'karla-assets');

        $this->publishes([
            $this->path() . '/resources/app' => base_path(),
        ], 'karla-setup');

        $this->publishes([
            $this->path() . '/resources/views'  => resource_path('views/vendor/karla'),
            $this->path() . '/resources/vendor' => resource_path('views'),
        ], 'karla-views');

        $this->publishes([
            $this->path() . '/database/migrations' => database_path('migrations'),
        ], 'karla-migrations');

        //$this->loadMigrationsFrom($this->path() . '/database/migrations');
    }

    protected function auth()
    {
        Auth::extend('access_token', function ($app, $name, array $config) {
            // automatically build the DI, put it as reference
            $userProvider = app(AccessTokenProvider::class);
            $request      = app('request');

            return new AccessTokenGuard($userProvider, $request, $config);
        });

        Auth::extend('auth_token', function ($app, $name, array $config) {
            // automatically build the DI, put it as reference
            $userProvider = app(AuthTokenProvider::class);
            $request      = app('request');

            return new AuthTokenGuard($userProvider, $request, $config);
        });

        Auth::extend('credentials', function ($app, $name, array $config) {
            $userProvider = app(CredentialsProvider::class);
            $request      = app('request');

            return new CredentialsGuard($userProvider, $request, $config);
        });
    }

    /**
     * Register the Authentication Log's events.
     */
    protected function registerEvents()
    {
        $events = $this->app['config']->get('karla.events');

        if (\is_array($events)) {
            foreach ($events as $event => $listeners) {
                foreach ($listeners as $listener) {
                    Event::listen($event, $listener);
                }
            }
        }
    }

    protected function registerMiddlewares()
    {
        $router = $this->app['router'];

        $middlewares = $this->app['config']->get('karla.middlewares');

        if (is_array($middlewares)) {
            foreach ($middlewares as $name => $value) {
                $router->aliasMiddleware($name, $value);
            }

            $router->pushMiddlewareToGroup('web', 'ajax');
            $router->pushMiddlewareToGroup('web', 'theme');

            $router->pushMiddlewareToGroup('api', 'accept');
            $router->pushMiddlewareToGroup('api', 'api.response');

            $router->pushMiddlewareToGroup('rest', 'accept');
            $router->pushMiddlewareToGroup('rest', 'api.response');

            //$router->pushMiddlewareToGroup('rest', 'auth:api,access_token,access_token,credentials');
        }

    }
}
