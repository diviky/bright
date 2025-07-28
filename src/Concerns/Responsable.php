<?php

declare(strict_types=1);

namespace Diviky\Bright\Concerns;

use Diviky\Bright\Attributes\ViewPaths;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Str;

trait Responsable
{
    /**
     * Get the view.
     *
     * @param  string  $route
     * @param  mixed  $data
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    protected function getViewLayout($content, array $data = [], ?string $layout = null)
    {
        $layout = $layout ?: 'layouts.index';
        $data['slot'] = $content;

        return $this->getView($layout, $data);
    }

    protected function getViewContent($view, array $data = [])
    {
        return $this->getView($view, $data)->render();
    }

    protected function getView($view, array $data = [])
    {
        $factory = app(ViewFactory::class);

        return $factory->make($view, $data);
    }

    /**
     * Get the route name from action.
     */
    protected function getRouteFromAction(string $action): string
    {
        $method = $this->getMethod($action);
        $component = $this->getComponent($action);

        return Str::lower($component . '.' . Str::kebab($method));
    }

    /**
     * Get the method name of the route action.
     *
     * @param  mixed  $action
     */
    protected function getMethod(string $action): string
    {
        return Arr::last(\explode('@', $action));
    }

    /**
     * Get the namespace of the action.
     */
    protected function getComponent(string $action): ?string
    {
        if (\strpos($action, '@') === false) {
            return null;
        }

        $action = \explode('@', $action);
        $controller = \explode('\\', $action[0]);
        $controller = $controller[\count($controller) - 2];

        return \strtolower($controller);
    }

    /**
     * Get the view path.
     */
    protected function getViewPath(string $action): ?string
    {
        if (\strpos($action, '@') === false) {
            return null;
        }

        $action = \explode('@', $action);
        $action = \explode('\\', $action[0]);
        $action = \array_filter($action);
        \array_pop($action);
        $path = \implode(DIRECTORY_SEPARATOR, \array_slice($action, 1));

        return app_path($path . '/views');
    }

    /**
     * Get redirect route from response.
     *
     * @return null|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    protected function getNextRedirect(array $response = [], string $keyword = 'next')
    {
        $next = $response[$keyword];
        if (!isset($next)) {
            return $response;
        }

        unset($response[$keyword]);

        $redirect = null;
        if (\is_string($next)) {
            if (\substr($next, 0, 1) == '/') {
                $redirect = Redirect::to($next);
            } elseif ($next == 'back') {
                $redirect = Redirect::back();
            } elseif ($next == 'intended') {
                $redirect = Redirect::intended('/');
            } else {
                $redirect = Redirect::route($next);
            }
        } elseif (\is_array($next)) {
            if ($next['back']) {
                $redirect = Redirect::back();
            } elseif ($next['path']) {
                $redirect = Redirect::to($next['path']);
            } elseif ($next['next']) {
                $redirect = Redirect::route($next['route']);
            } elseif ($next['intended']) {
                $redirect = Redirect::intended($next['intended']);
            }
        } elseif ($next instanceof \Closure) {
            $redirect = $next();
        }

        if (isset($redirect)) {
            foreach ($response as $key => $value) {
                $redirect = $redirect->with($key, $value);
            }
        }

        return $redirect;
    }

    /**
     * Get the view locations from controller.
     */
    protected function getViewPathsFrom(object $controller, ?string $action = null): array
    {
        $paths = [];
        if (\method_exists($controller, 'loadViewsFrom')) {
            $paths = $controller->loadViewsFrom();
            $paths = !\is_array($paths) ? [$paths] : $paths;

            foreach ($paths as $key => $path) {
                $paths[$key] = $path . '/views/';
            }
        }

        $reflection = new \ReflectionClass($controller);

        $attributes = $reflection->getAttributes(ViewPaths::class);

        foreach ($attributes as $attribute) {
            $instance = $attribute->newInstance();
            $paths = array_merge($paths, $instance->getPaths());
        }

        if ($action) {
            $paths = array_merge($paths, [$this->getViewPath($action)]);
        }

        return array_filter($paths);
    }
}
