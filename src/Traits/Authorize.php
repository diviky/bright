<?php

namespace Karla\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

trait Authorize
{
    protected function getRouteFromAction($route): array
    {
        $action = $route->getActionName();
        if (strpos($action, '@') === false) {
            return [];
        }

        $action     = \explode('@', $action);
        $method     = $route->getActionMethod();
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

    public function isAuthorized($names): bool
    {
        if (is_null($names)) {
            return false;
        }

        if (!\is_array($names)) {
            $names = [$names];
        }

        $public = config('permission.public');

        // is public permission
        if (is_array($public)) {
            foreach ($names as $route) {
                foreach ($public as $permission) {
                    if (Str::is($permission, $route)) {
                        return true;
                    }
                }
            }
        }

        // Check user has permission
        $user = Auth::user();
        if ($user) {
            foreach ($names as $route) {
                if ($user->can($route)) {
                    return true;
                }
            }

            return false;
        }

        return true;
    }

    public function isActionAuthorized($route): bool
    {
        $names = $this->getRouteFromAction($route);

        return $this->isAuthorized($names);
    }

    public function isRouteAuthorized($route): bool
    {
        $name = $route->getName();
        if (is_null($name)) {
            return false;
        }

        return $this->isAuthorized('name:' . $name);
    }

    public function isPrefixAuthorized($route): bool
    {
        $prefix = $route->getPrefix();

        if (is_null($prefix)) {
            return false;
        }

        return $this->isAuthorized('prefix:' . $prefix);
    }

    public function isUriAuthorized($route): bool
    {
        $uri = $route->uri();

        if (is_null($uri)) {
            return false;
        }

        $methods = $route->methods();

        $names = [];
        foreach ($methods as $method) {
            $names[] = 'uri:' . $method . ':' . $uri;
        }

        return $this->isAuthorized($names);
    }

    public function isAuthorizedAny($route): bool
    {
        if ($this->isActionAuthorized($route)) {
            return true;
        }

        if ($this->isRouteAuthorized($route)) {
            return true;
        }

        if ($this->isPrefixAuthorized($route)) {
            return true;
        }

        if ($this->isUriAuthorized($route)) {
            return true;
        }

        return false;
    }
}
