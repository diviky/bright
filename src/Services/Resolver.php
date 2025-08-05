<?php

namespace Diviky\Bright\Services;

use Illuminate\Routing\Route;

class Resolver
{
    protected static $responsableResolver;

    protected static $themeResolver;

    protected static $themeFromActionResolver;

    /**
     * The timezone resolver callback.
     *
     * @var callable|null
     */
    protected static $timezoneResolver;

    protected static $viewResolver;

    protected static $dispatchResolver;

    public static function resolveDispatch(callable $callback)
    {
        static::$dispatchResolver = $callback;
    }

    public static function getDispatchResolver()
    {
        return static::$dispatchResolver;
    }

    public static function dispatch(Route $route, $response, $controller, $method)
    {
        if (static::$dispatchResolver) {
            return call_user_func(static::$dispatchResolver, $route, $response, $controller, $method);
        }

        return $response;
    }

    public static function resolveResponsable(callable $callback)
    {
        static::$responsableResolver = $callback;
    }

    public static function getResponsableResolver()
    {
        return static::$responsableResolver;
    }

    public static function responsable($request, $response, $action, $controller, $method)
    {
        if (static::$responsableResolver) {
            return call_user_func(static::$responsableResolver, $request, $response, $action, $controller, $method);
        }

        return $response;
    }

    public static function theme($request, ?string $component = null, array $paths = [])
    {
        if (static::$themeResolver) {
            return call_user_func(static::$themeResolver, $request, $component, $paths);
        }

        return [];
    }

    public static function resolveThemeFromAction(callable $callback)
    {
        static::$themeFromActionResolver = $callback;
    }

    public static function resolveTheme(callable $callback)
    {
        static::$themeResolver = $callback;
    }

    public static function themeFromAction($controller)
    {
        if (static::$themeFromActionResolver) {
            return call_user_func(static::$themeFromActionResolver, $controller);
        }

        return [];
    }

    public static function getThemeResolver()
    {
        return static::$themeResolver;
    }

    public static function view($request, $response, $config)
    {
        if (static::$viewResolver) {
            return call_user_func(static::$viewResolver, $request, $response, $config);
        }

        return $response;
    }

    public static function getViewResolver()
    {
        return static::$viewResolver;
    }

    public static function resolveView(callable $callback)
    {
        static::$viewResolver = $callback;
    }

    /**
     * Set the callback that should be used to resolve timezones.
     *
     * @return void
     */
    public static function resolveTimezone(callable $callback)
    {
        static::$timezoneResolver = $callback;
    }

    /**
     * Resolve the timezone for the given input.
     *
     * @param  string|null  $input
     * @return string|null
     */
    public static function getTimezone($input = null)
    {
        if (static::$timezoneResolver) {
            return call_user_func(static::$timezoneResolver, $input);
        }

        return $input ?? static::getDefaultTimezone();
    }

    public static function timezone($input = null)
    {
        return static::getTimezone($input);
    }

    /**
     * Get the timezone resolver callback.
     *
     * @return callable|null
     */
    public static function getTimezoneResolver()
    {
        return static::$timezoneResolver;
    }

    /**
     * Get default timezone.
     */
    protected static function getDefaultTimezone(): string
    {
        return config('app.timezone');
    }
}
