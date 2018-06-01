<?php

namespace Karla\Routing;

use Karla\Traits\Authorize;
use Illuminate\Routing\ControllerDispatcher as BaseControllerDispatcher;
use Illuminate\Routing\Route;

class ControllerDispatcher extends BaseControllerDispatcher
{
    use Authorize;

    /**
     * Dispatch a request to a given controller and method.
     *
     * @param  \Illuminate\Routing\Route  $route
     * @param  mixed  $controller
     * @param  string  $method
     * @return mixed
     */
    public function dispatch(Route $route, $controller, $method)
    {
        $action = $route->getActionName();

        // if (!$this->isAuthorized($action)) {
        //     abort(401, 'Access denied');
        // }

        $parameters = $this->resolveClassMethodDependencies(
            $route->parametersWithoutNulls(),
            $controller,
            $method
        );

        if (method_exists($controller, 'callAction')) {
            $response = $controller->callAction($method, $parameters);
        } else {
            $response = $controller->{$method}(...array_values($parameters));
        }

        return new Responsable($response, $action);
    }
}
