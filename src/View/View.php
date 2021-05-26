<?php

declare(strict_types=1);

namespace Diviky\Bright\View;

use Diviky\Bright\Traits\Themable;
use Illuminate\Routing\Controller;

class View
{
    use Themable;

    /**
     * Make the view.
     *
     * @param Controller|string $controller
     * @param string            $view
     * @param array             $data
     * @param array             $mergeData
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function make($controller, $view, $data = [], $mergeData = [])
    {
        $action = !\is_string($controller) ? \get_class($controller) : $controller;
        $route = $this->getRoute($action);

        if (\is_string($controller)) {
            $controller = new $controller();
        }

        $component = \explode('.', $route, 2)[0];

        $paths = $this->getViewsFrom($controller, $action);
        $this->setUpTheme($route, $component, $paths);

        return view($view, $data, $mergeData);
    }
}
