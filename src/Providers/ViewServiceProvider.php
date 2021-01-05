<?php

namespace Diviky\Bright\Providers;

use Diviky\Bright\View\Components\Flash;
use Diviky\Bright\View\Factory;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ViewServiceProvider extends BaseServiceProvider
{
    /**
     * Bootstrap your package's services.
     */
    public function boot()
    {
        Blade::component('flash', Flash::class);
        Blade::componentNamespace('Diviky\\Bright\\View\\Components', 'bright');

        Blade::directive('datetime', function ($expression) {
            return "<?php echo ({$expression})->format('d/m/Y H:i'); ?>";
        });
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->registerFactory();
    }

    public function registerFactory()
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

            return $factory;
        });
    }

    /**
     * Create a new Factory Instance.
     *
     * @param \Illuminate\View\Engines\EngineResolver $resolver
     * @param \Illuminate\View\ViewFinderInterface    $finder
     * @param \Illuminate\Contracts\Events\Dispatcher $events
     *
     * @return \Illuminate\View\Factory
     */
    protected function createFactory($resolver, $finder, $events)
    {
        return new Factory($resolver, $finder, $events);
    }
}
