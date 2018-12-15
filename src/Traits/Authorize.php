<?php

namespace Karla\Traits;

use Illuminate\Support\Facades\Auth;

trait Authorize
{
    protected function getRouteFromAction($action): string
    {
        $action     = explode('@', $action);
        $method     = end($action);
        $controller = explode('\\', $action[0]);
        $component  = strtolower($controller[count($controller) - 2]);

        return strtolower($component . '.' . $method);
    }

    protected function isAuthorized($action)
    {
        $routeName = $this->getRouteFromAction($action);

        // Check user has permission
        $user = Auth::user();
        if ($user && !$user->can($routeName)) {
            return false;
        }

        return true;
    }
}
