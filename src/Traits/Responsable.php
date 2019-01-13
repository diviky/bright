<?php

namespace Karla\Traits;

use Closure;
use Illuminate\Support\Arr;

trait Responsable
{
    protected function getView($route, $data, $layout = null)
    {
        $layout            = $layout ?: 'index';
        $data['component'] = $route;

        return view('layouts.' . $layout, $data);
    }

    protected function getRoute($action): string
    {
        $method    = $this->getMethod($action);
        $component = $this->getNamespace($action);

        return strtolower($component . '.' . $method);
    }

    /**
     * Get the method name of the route action.
     *
     * @return string
     */
    protected function getMethod($action): string
    {
        return Arr::last(explode('@', $action));
    }

    protected function getNamespace($action): string
    {
        $action     = explode('@', $action);
        $controller = explode('\\', $action[0]);
        $controller = strtolower($controller[count($controller) - 2]);

        return $controller;
    }

    protected function getViewPath($action): string
    {
        $action = explode('@', $action);
        $action = explode('\\', $action[0]);
        array_pop($action);
        $path = implode(DIRECTORY_SEPARATOR, array_slice($action, 1));

        return app_path($path . '/views');
    }

    protected function getNextRedirect($response = [], $keyword = 'next')
    {
        $next = $response[$keyword];
        if (!isset($next)) {
            return $response;
        }

        unset($response[$keyword]);

        if (is_string($next)) {
            if ('/' == substr($next, 0, 1)) {
                $redirect = redirect($next);
            } elseif ('back' == $next) {
                $redirect = redirect()->back();
            } else {
                $redirect = redirect()->route($next);
            }
        } elseif (is_array($next)) {
            if ($next['back']) {
                $redirect = redirect()->back();
            } elseif ($next['path']) {
                $redirect = redirect($next['path']);
            } elseif ($next['next']) {
                $redirect = redirect()->route($next['route']);
            }
        } elseif ($next instanceof Closure) {
            $redirect = $next();
        }

        foreach ($response as $key => $value) {
            $redirect = $redirect->with($key, $value);
        }

        return $redirect;
    }

    protected function getViewsFrom($controller, $action = null)
    {
        if (method_exists($controller, 'getViewsFrom')) {
            $paths = $controller->getViewsFrom();
            $paths = !is_array($paths) ? [$paths] : $paths;
            foreach ($paths as $key => $path) {
                $paths[$key] = $path . '/views/';
            }

            return $paths;
        }

        if ($action) {
            return $this->getViewPath($action);
        }

        return null;
    }
}
