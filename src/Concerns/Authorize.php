<?php

declare(strict_types=1);

namespace Diviky\Bright\Concerns;

use Illuminate\Routing\Route;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait Authorize
{
    /**
     * Check given permission name is allowed.
     *
     * @param  null|array  $names
     */
    public function isAuthorizationRevoked($names): bool
    {
        if (\is_null($names)) {
            return false;
        }

        // Check user has permission
        foreach ($names as $route) {
            if ($this->isForbid($route)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check given permission name is allowed.
     *
     * @param  null|array  $names
     */
    public function isAuthorized($names): bool
    {
        if (\is_null($names)) {
            return false;
        }

        $names = Arr::wrap($names);
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
        foreach ($names as $route) {
            if ($this->can($route)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check authorization view action class.
     *
     * @param  Route  $route
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
     * @param  Route  $route
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
     * @param  Route  $route
     */
    public function isPrefixAuthorized($route): bool
    {
        $prefix = $route->getPrefix();

        if (empty($prefix)) {
            return false;
        }

        return $this->isAuthorized(['prefix:' . str_replace('/', '.', trim($prefix, '/'))]);
    }

    /**
     * Check authorization via route uri.
     *
     * @param  Route  $route
     */
    public function isUriAuthorized($route): bool
    {
        $uri = $route->uri();
        $uri = str_replace('/', '.', $uri);

        $methods = $route->methods();

        $names = [];
        foreach ($methods as $method) {
            $names[] = 'uri:' . $method . ':' . $uri;
        }

        return $this->isAuthorized($names);
    }

    /**
     * Check authorization view action class.
     *
     * @param  Route  $route
     */
    public function isActionAuthorizeRevoked($route): bool
    {
        $names = $this->getRoutesFromRoute($route);

        return $this->isAuthorizationRevoked($names);
    }

    /**
     * Check authorization via route names
     * should prefix permission with name:.
     *
     * @param  Route  $route
     */
    public function isRouteAuthorizeRevoked($route): bool
    {
        $route = $route->getName();

        if (\is_null($route)) {
            return false;
        }

        return $this->isAuthorizationRevoked(['name:' . $route]);
    }

    /**
     * Check authorization via route prefix.
     *
     * @param  Route  $route
     */
    public function isPrefixAuthorizeRevoked($route): bool
    {
        $prefix = $route->getPrefix();

        if (empty($prefix)) {
            return false;
        }

        return $this->isAuthorizationRevoked(['prefix:' . str_replace('/', '.', trim($prefix, '/'))]);
    }

    /**
     * Check authorization via route uri.
     *
     * @param  Route  $route
     */
    public function isUriAuthorizeRevoked($route): bool
    {
        $uri = $route->uri();
        $uri = str_replace('/', '.', $uri);

        $methods = $route->methods();

        $names = [];
        foreach ($methods as $method) {
            $names[] = 'uri:' . $method . ':' . $uri;
        }

        return $this->isAuthorizationRevoked($names);
    }

    /**
     * Check authorization via route.
     *
     * @param  Route  $route
     */
    public function isAuthorizedAny($route): bool
    {
        if ($this->isAuthorizeRevokedAny($route)) {
            return false;
        }

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
     * Check authorization via route.
     *
     * @param  Route  $route
     */
    public function isAuthorizeRevokedAny($route): bool
    {
        if ($this->isActionAuthorizeRevoked($route)) {
            return true;
        }

        if ($this->isRouteAuthorizeRevoked($route)) {
            return true;
        }

        if ($this->isPrefixAuthorizeRevoked($route)) {
            return true;
        }

        if ($this->isUriAuthorizeRevoked($route)) {
            return true;
        }

        return false;
    }

    /**
     * Get the route from action.
     *
     * @param  Route  $route
     */
    protected function getRoutesFromRoute($route): array
    {
        $action = $route->getActionName();
        if (\strpos($action, '@') === false) {
            return [];
        }

        $action = \explode('@', $action);
        $method = $route->getActionMethod();
        $controller = \explode('\\', $action[0]);
        $controllersCount = count($controller);

        $method = \strtolower($method);
        $component = \strtolower($controller[$controllersCount - 2] ?? '');
        $namespace = \strtolower($controller[$controllersCount - 5] ?? '');

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
