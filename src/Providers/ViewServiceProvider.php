<?php

declare(strict_types=1);

namespace Diviky\Bright\Providers;

use Diviky\Bright\View\Components\Flash;
use Diviky\Bright\View\Components\Form;
use Diviky\Bright\View\Components\Link;
use Diviky\Bright\View\Factory;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ViewServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap your package's services.
     */
    public function boot(): void
    {
        Blade::component('flash', Flash::class);
        Blade::component('link', Link::class);
        Blade::component('bright-form', Form::class);
        Blade::componentNamespace('Diviky\\Bright\\View\\Components', 'bright');

        Blade::directive('datetime', function ($expression) {
            return "<?php echo ({$expression})->format('d/m/Y H:i'); ?>";
        });
    }

    protected function path(): string
    {
        return __DIR__ . '/../..';
    }

    /**
     * Register the application services.
     */
    public function register(): void
    {
        $this->registerFactory();
    }

    public function registerFactory(): void
    {
        $this->app->singleton('view', function ($app) {
            // Next we need to grab the engine resolver instance that will be used by the
            // environment. The resolver will be used by an environment to get each of
            // the various engine implementations such as plain PHP or Blade engine.
            $resolver = $app['view.engine.resolver'];

            $finder = $app['view.finder'];

            $factory = $this->createFactory($resolver, $finder, $app['events']);

            // We will also set the container instance on this view environment since the
            // view composers may be classes registered in the container, which allows
            // for great testable, flexible composers for the application developer.
            $factory->setContainer($app);

            $factory->share('app', $app);
            $factory->setDefaultPaths();

            return $factory;
        });
    }

    /**
     * Create a new Factory Instance.
     *
     * @param  \Illuminate\View\Engines\EngineResolver  $resolver
     * @param  \Illuminate\View\ViewFinderInterface  $finder
     * @param  \Illuminate\Contracts\Events\Dispatcher  $events
     * @return \Illuminate\View\Factory
     */
    protected function createFactory($resolver, $finder, $events)
    {
        return new Factory($resolver, $finder, $events);
    }
}
