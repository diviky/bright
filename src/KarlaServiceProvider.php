<?php

namespace Karla;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Karla\Extensions\TokenUserProvider;
use Karla\Listeners\EmailLogger;
use Karla\Listeners\SuccessLogin;
use Karla\Routing\Redirector;
use Karla\Routing\Resolver;
use Karla\Services\Auth\AccessTokenGuard;
use Karla\Traits\Provider;

class KarlaServiceProvider extends ServiceProvider
{
    use Provider;

    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'Illuminate\Mail\Events\MessageSending' => [
            EmailLogger::class,
        ],
        'Illuminate\Auth\Events\Login'          => [
            SuccessLogin::class,
        ],
    ];

    public function boot()
    {
        parent::boot();

        $this->publishes([
            __DIR__ . '/../config/permission.php' => config_path('permission.php'),
            __DIR__ . '/../config/karla.php'      => config_path('karla.php'),
            __DIR__ . '/../config/theme.php'      => config_path('theme.php'),
            __DIR__ . '/../config/auth.php'       => config_path('auth.php'),
            __DIR__ . '/../config/app.php'        => config_path('app.php'),
        ], 'karla-config');

        $this->publishes([
            __DIR__ . '/../resources/assets/js' => resource_path('assets/js'),
            __DIR__ . '/../webpack.mix.js'      => base_path('webpack.mix.js'),
            __DIR__ . '/../bower.json'          => base_path('bower.json'),
        ], 'karla-assets');

        $this->publishes([
            __DIR__ . '/../resources/views' => resource_path('views'),
        ], 'karla-views');

        $this->loadViewsFrom(__DIR__ . '/../resources/views/', 'karla');

        Schema::defaultStringLength(191);
        $this->directive();
        $this->macros();
        $this->validates();
    }

    public function register()
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/permission.php', 'permission');
        $this->mergeConfigFrom(__DIR__ . '/../config/karla.php', 'karla');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations/');

        $this->redirect();
        $this->auth();
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

    protected function auth()
    {
        Auth::extend('access_token', function ($app, $name, array $config) {
            // automatically build the DI, put it as reference
            $userProvider = app(TokenUserProvider::class);
            $request      = app('request');
            return new AccessTokenGuard($userProvider, $request, $config);
        });
    }
}
