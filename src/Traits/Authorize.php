<?php

declare(strict_types=1);

namespace Diviky\Bright\Traits;

use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

trait Authorize
{
    /**
     * Check given permission name is allowed.
     *
     * @param null|array $names
     */
    public function isAuthorized($names): bool
    {
        if (\is_null($names)) {
            return false;
        }

        $public = config('permission.public');

        // is public permission
        if (\is_array($public)) {
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

    /**
     * Check authorization view action class.
     *
     * @param Route $route
     */
    public function isActionAuthorized($route): bool
    {
        $names = $this->getRoutesFromRoute($route);

        return $this->isAuthorized($names);
    }

    /**
     * Check authorization via route names
     * should prefix permission with name:.
     *
     * @param Route $route
     */
    public function isRouteAuthorized($route): bool
    {
        $route = $route->getName();

        if (\is_null($route)) {
            return false;
        }

        return $this->isAuthorized(['name:' . $route]);
    }

    /**
     * Check authorization via route prefix.
     *
     * @param Route $route
     */
    public function isPrefixAuthorized($route): bool
    {
        $prefix = $route->getPrefix();

        if (\is_null($prefix)) {
            return false;
        }

        return $this->isAuthorized(['prefix:' . $prefix]);
    }

    /**
     * Check authorization via route uri.
     *
     * @param Route $route
     */
    public function isUriAuthorized($route): bool
    {
        $uri = $route->uri();

        $methods = $route->methods();

        $names = [];
        foreach ($methods as $method) {
            $names[] = 'uri:' . $method . ':' . $uri;
        }

        return $this->isAuthorized($names);
    }

    /**
     * Check authorization via route.
     *
     * @param Route $route
     */
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

    /**
     * Get the route from action.
     *
     * @param Route $route
     */
    protected function getRoutesFromRoute($route): array
    {
        $action = $route->getActionName();
        if (false === \strpos($action, '@')) {
            return [];
        }

        $action = \explode('@', $action);
        $method = $route->getActionMethod();
        $controller = \explode('\\', $action[0]);

        $component = \strtolower($controller[\count($controller) - 2]);
        $method = \strtolower($method);
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
}
