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

        $component = strtolower($controller[count($controller) - 2]);
        $method    = strtolower($method);
        $namespace = strtolower($controller[count($controller) - 5]);

        $mappings = config('permission.grouping');

        if ($mappings && is_array($mappings[$namespace])) {
            $mapping = $mappings[$namespace][$component];

            return is_array($mapping) ? $mapping[0] : $mapping;
        }

        if ($mappings && isset($mappings[$component])) {
            $mapping = $mappings[$namespace];

            return is_array($mapping) ? $mapping[0] : $mapping;
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
