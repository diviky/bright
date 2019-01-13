<?php

namespace Karla\View;

use Karla\Traits\Themable;

class View
{
    use Themable;

    public function make($controller, $view, $data = [], $mergeData = [])
    {
        $action = get_class($controller);
        $route  = $this->getRoute($action);

        list($component, $view) = explode('.', $route);

        $paths  = $this->getViewsFrom($controller, $action);
        $theme  = $this->setUpTheme($route, $component, $paths);

        return view($view, $data, $mergeData);
    }
}
