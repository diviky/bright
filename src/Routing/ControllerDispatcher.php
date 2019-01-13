<?php

namespace Karla\Routing;

use Illuminate\Routing\ControllerDispatcher as BaseControllerDispatcher;
use Illuminate\Routing\Route;
use Karla\Traits\Authorize;
use Karla\Traits\Themable;

class ControllerDispatcher extends BaseControllerDispatcher
{
    use Authorize;
    use Themable;

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
        $action = $route->getActionName();

        if (!app()->has('is_api_request') && !$this->isAuthorized($action)) {
            $this->setUpThemeFromAction($action);
            abort(403, 'Access denied');
        }

        $response = parent::dispatch($route, $controller, $method);

        return new Responsable($response, $action, $controller);
    }
}
