<?php

namespace Karla\Routing;

use Illuminate\Routing\ControllerDispatcher as BaseControllerDispatcher;
use Illuminate\Routing\Route;

class ControllerDispatcher extends BaseControllerDispatcher
{
    /**
     * Dispatch a request to a given controller and method.
     *
     * @param \Illuminate\Routing\Route $route
     * @param mixed                     $controller
     * @param string                    $method
     *
     * @return mixed
     */
    public function dispatch(Route $route, $controller, $method)
    {
        $action   = $route->getActionName();
        $response = parent::dispatch($route, $controller, $method);

        return new Responsable($response, $action, $controller);
    }
}
