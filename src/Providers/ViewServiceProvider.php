<?php

declare(strict_types=1);

namespace Diviky\Bright\Providers;

use Diviky\Bright\View\Compilers\BladeCompiler;
use Diviky\Bright\View\Compilers\ComponentTagCompiler;
use Diviky\Bright\View\Components\Flash;
use Diviky\Bright\View\Components\Form;
use Diviky\Bright\View\Components\Link;
use Diviky\Bright\View\DynamicComponent;
use Diviky\Bright\View\Factory;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider as BaseServiceProvider;
use Illuminate\View\Compilers\ComponentTagCompiler as BaseComponentTagCompiler;

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

        Blade::directive('page', function ($paginator) {
            return "<?php echo ({$paginator}->currentPage() - 1) * {$paginator}->perPage() + \$loop->index + 1; ?>";
        });
    }

    protected function path(): string
    {
        return __DIR__ . '/../..';
    }

    /**
     * Register the application services.
     */
    #[\Override]
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

        $this->app->extend(BaseComponentTagCompiler::class, function ($compiler, $app) {
            return new ComponentTagCompiler(
                Blade::getClassComponentAliases()
            );
        });

        $this->registerBladeCompiler();
    }

    /**
     * Register the Blade compiler implementation.
     *
     * @return void
     */
    public function registerBladeCompiler()
    {
        $this->app->singleton('blade.compiler', function ($app) {
            return tap(new BladeCompiler(
                $app['files'],
                $app['config']['view.compiled'],
                $app['config']->get('view.relative_hash', false) ? $app->basePath() : '',
                $app['config']->get('view.cache', true),
                $app['config']->get('view.compiled_extension', 'php'),
            ), function ($blade) {
                $blade->component('dynamic-component', DynamicComponent::class);
            });
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
