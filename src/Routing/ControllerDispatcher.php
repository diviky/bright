<?php

declare(strict_types=1);

namespace Diviky\Bright\Routing;

use Diviky\Bright\Services\Resolver;
use Illuminate\Routing\ControllerDispatcher as BaseControllerDispatcher;
use Illuminate\Routing\Route;

class ControllerDispatcher extends BaseControllerDispatcher
{
    /**
     * Dispatch a request to a given controller and method.
     *
     * @param  mixed  $controller
     * @param  string  $method
     * @return mixed
     */
    #[\Override]
    public function dispatch(Route $route, $controller, $method)
    {
        $response = parent::dispatch($route, $controller, $method);

        return Resolver::dispatch($route, $response, $controller, $method);
    }
}
