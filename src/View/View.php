<?php

namespace Diviky\Bright\View;

use Diviky\Bright\Traits\Themable;

class View
{
    use Themable;

    public function make($controller, $view, $data = [], $mergeData = [])
    {
        $action = !\is_string($controller) ? \get_class($controller) : $controller;
        $route  = $this->getRoute($action);

        list($component, $v) = \explode('.', $route);

        if (\is_string($controller)) {
            $controller = new $controller();
        }

        $paths = $this->getViewsFrom($controller, $action);
        $theme = $this->setUpTheme($route, $component, $paths);

        return view($view, $data, $mergeData);
    }
}
