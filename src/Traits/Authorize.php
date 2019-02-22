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
        //echo $controller = array_filter($controller);

        $component = strtolower($controller[count($controller) - 2]);
        $method    = strtolower($method);

        $mappings = config('permission.grouping');

        if ($mappings && isset($mappings[$component])) {
            return $mappings[$component];
        }

        return $component . '.' . $method;
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
