<?php

namespace Karla\Traits;

use Illuminate\Support\Facades\Auth;

trait Authorize
{
    protected function getRouteFromAction($action): array
    {
        $action     = \explode('@', $action);
        $method     = \end($action);
        $controller = \explode('\\', $action[0]);

        $component = \strtolower($controller[\count($controller) - 2]);
        $method    = \strtolower($method);
        $namespace = \strtolower($controller[\count($controller) - 5]);

        $mappings = config('permission.grouping');

        if ($mappings && isset($mappings[$namespace]) && \is_array($mappings[$namespace]) && isset($mappings[$namespace][$component])) {
            $mapping = $mappings[$namespace][$component];

            return \is_array($mapping) ? \array_unique($mapping) : [$mapping];
        }

        if ($mappings && isset($mappings[$component]) && !\is_array($mappings[$component])) {
            $mapping = $mappings[$component];

            return \is_array($mapping) ? \array_unique($mapping) : [$mapping];
        }

        return [$component . '.' . $method];
    }

    protected function isAuthorized($action): bool
    {
        $route_names = $this->getRouteFromAction($action);

        if (!\is_array($route_names)) {
            $route_names = [$route_names];
        }

        // Check user has permission
        $user = Auth::user();
        if ($user) {
            foreach ($route_names as $route) {
                if ($user->can($route)) {
                    return true;
                }
            }

            return false;
        }

        return true;
    }
}
