<?php

declare(strict_types=1);

namespace Diviky\Bright\Providers;

use Diviky\Bright\Console\Commands\GeoipUpdate;
use Diviky\Bright\Console\Commands\Migrate;
use Diviky\Bright\Console\Commands\Rollback;
use Diviky\Bright\Console\Commands\Setup;
use Diviky\Bright\Contracts\UtilInterface;
use Diviky\Bright\Routing\ControllerDispatcher;
use Diviky\Bright\Routing\Redirector;
use Diviky\Bright\Routing\Resolver;
use Diviky\Bright\Services\Auth\AccessTokenGuard;
use Diviky\Bright\Services\Auth\AuthTokenGuard;
use Diviky\Bright\Services\Auth\CredentialsGuard;
use Diviky\Bright\Services\Auth\Providers\AccessProvider;
use Diviky\Bright\Support\ServiceProvider;
use Diviky\Bright\Traits\Provider;
use Diviky\Bright\Util\Util;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;

/**
 * @SuppressWarnings(PHPMD)
 */
class BrightServiceProvider extends ServiceProvider
{
    use Provider;

    public function boot(Filesystem $filesystem): void
    {
        $this->directive();
        $this->macros();
        $this->validates();

        $this->loadRoutesFrom($this->path() . '/routes/web.php');
        $this->loadRoutesFrom($this->path() . '/routes/api.php');

        $this->replaceConfigRecursive($this->path() . '/config/auth.php', 'auth');

        $this->loadViewsFrom($this->path() . '/resources/views/', 'bright');

        $this->registerMiddlewares();
        $this->registerEvents();

        if ($this->app->runningInConsole()) {
            $this->console($filesystem);
        }
    }

    public function register(): void
    {
        Schema::defaultStringLength(191);

        $this->mergeConfigFrom($this->path() . '/config/bright.php', 'bright');
        $this->mergeConfigFrom($this->path() . '/config/charts.php', 'charts');
        $this->mergeConfigFrom($this->path() . '/config/theme.php', 'theme');
        $this->mergeConfigFrom($this->path() . '/config/permission.php', 'permission');

        $this->authGuards();
        $this->registerModelBindings();

        $this->app->singleton('resolver', function ($app) {
            return new Resolver($app);
        });
    }

    public function registerModelBindings(): void
    {
        $this->app->bind(UtilInterface::class, function ($app) {
            return new Util();
        });

        $this->app->bind('Illuminate\Routing\Contracts\ControllerDispatcher', ControllerDispatcher::class);

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

    protected function path(): string
    {
        return __DIR__ . '/../..';
    }

    protected function console(Filesystem $filesystem): void
    {
        $this->publishes([
            $this->path() . '/config/charts.php' => config_path('charts.php'),
            //$this->path() . '/config/permission.php'  => config_path('permission.php'),
            $this->path() . '/config/bright.php' => config_path('bright.php'),
            $this->path() . '/config/theme.php' => config_path('theme.php'),
            $this->path() . '/config/sharding.php' => config_path('sharding.php'),
        ], 'bright-config');

        $this->publishes([
            $this->path() . '/resources/assets/js' => resource_path('js'),
        ], 'bright-assets-js');

        $this->publishes([
            $this->path() . '/resources/assets/app.js' => resource_path('js/app.js'),
        ], 'bright-assets-app');

        $this->publishes([
            $this->path() . '/resources/app' => base_path(),
        ], 'bright-setup');

        $this->publishes([
            $this->path() . '/resources/views' => resource_path('views/vendor/bright'),
        ], 'bright-views-auth');

        $this->publishes([
            $this->path() . '/resources/vendor' => resource_path('views/vendor'),
        ], 'bright-views-vendor');

        $this->publishes($this->getMigrationFiles($filesystem), 'bright-migrations');

        $this->publishes([
            $this->path() . '/database/seeders' => database_path('seeders'),
        ], 'bright-seeders');

        $this->commands([
            Setup::class,
            Migrate::class,
            Rollback::class,
            GeoipUpdate::class,
        ]);
    }

    protected function authGuards(): void
    {
        Auth::extend('access_token', function ($app, $name, array $config) {
            // automatically build the DI, put it as reference
            $userProvider = app(AccessProvider::class);
            $request = app('request');

            return new AccessTokenGuard($userProvider, $request, $config);
        });

        Auth::extend('auth_token', function ($app, $name, array $config) {
            // automatically build the DI, put it as reference
            $userProvider = app(AccessProvider::class);
            $request = app('request');

            return new AuthTokenGuard($userProvider, $request, $config);
        });

        Auth::extend('credentials', function ($app, $name, array $config) {
            $userProvider = app(AccessProvider::class);
            $request = app('request');

            return new CredentialsGuard($userProvider, $request, $config);
        });
    }

    /**
     * Register the Authentication Log's events.
     */
    protected function registerEvents(): void
    {
        $events = $this->app['config']->get('bright.events');

        if (\is_array($events)) {
            foreach ($events as $event => $listeners) {
                foreach ($listeners as $listener) {
                    Event::listen($event, $listener);
                }
            }
        }
    }

    protected function registerMiddlewares(): void
    {
        $router = $this->app['router'];

        $middlewares = $this->app['config']->get('bright.middlewares');

        if (\is_array($middlewares)) {
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

    /**
     * Returns existing migration file if found, else uses the current timestamp.
     */
    protected function getMigrationFiles(Filesystem $filesystem): array
    {
        $timestamp = date('Y_m_d');

        $path = $this->path() . '/database/migrations/';
        $files = $filesystem->glob($path . '*.php');

        $output = [];
        foreach ($files as $file) {
            $output[$file] = database_path('migrations') . '/' . $timestamp . '_' . basename($file);
        }

        return $output;
    }
}
