<?php

declare(strict_types=1);

namespace Diviky\Bright\Routing;

use Illuminate\Routing\ControllerDispatcher as BaseControllerDispatcher;
use Illuminate\Routing\Route;

class ControllerDispatcher extends BaseControllerDispatcher
{
    /**
     * Dispatch a request to a given controller and method.
     *
     * @param mixed  $controller
     * @param string $method
     *
     * @return mixed
     */
    public function dispatch(Route $route, $controller, $method)
    {
        $action = $route->getActionName();
        $response = parent::dispatch($route, $controller, $method);

        return new Responsable($response, $action, $controller);
    }
}
