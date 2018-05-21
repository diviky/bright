<?php

namespace Karla\Routing;

use Karla\Traits\Themable;
use Closure;
use Illuminate\Contracts\Support\Responsable as BaseResponsable;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Arr;

class Responsable implements BaseResponsable
{
    use Themable;

    protected $response;
    protected $action;

    public function __construct($response, $action)
    {
        $this->response = $response;
        $this->action = $action;
    }
    /**
     * Create an HTTP response that represents the object.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function toResponse($request)
    {
        $response = $this->getResponse();
        $format = $request->input('format');
        $route = $this->getRoute($this->action);

        if (!is_array($response) && $response instanceof View) {
            $this->setUpTheme($route);
            return $response;
        }

        if (!is_array($response)) {
            return $response;
        }

        $requestType = $request->input('_request');
        if ($requestType == 'iframe') {
            $html = '<textarea>';
            $html .= json_encode($response);
            $html .= '</textarea>';

            return $html;
        }

        $ajax = $request->ajax();

        if (!$ajax && isset($response['next'])) {
            return $this->getNextRedirect($response, 'next');
        } elseif ($ajax && isset($response['redirect'])) {
            if (substr($response['redirect'], 0, 1) !== '/') {
                $redirect = $this->getNextRedirect($response, 'redirect');
                $response['redirect'] = $redirect->getTargetUrl();
            }
        }

        if ($format == 'json') {
            return $response;
        }

        list($component, $view) = explode('.', $route);
        $path = $this->getViewPath($this->action);
        $theme = $this->setUpTheme($route, $component, $path);
        $layout = $format == 'html' ? 'html' : $theme['layout'];

        return $this->getView($view, $response, $layout);
    }

    protected function getView($route, $data, $layout = null)
    {
        $layout = $layout ?: 'index';
        $data['component'] = $route;

        return view('layouts.' . $layout, $data);
    }

    protected function getRoute($action): string
    {
        $method = $this->getMethod($action);
        $component = $this->getNamespace($action);

        return strtolower($component . '.' . $method);
    }

    /**
     * Get the method name of the route action.
     *
     * @return string
     */
    protected function getMethod($action): string
    {
        return Arr::last(explode('@', $action));
    }

    protected function getNamespace($action): string
    {
        $action = explode('@', $action);
        $controller = explode('\\', $action[0]);
        $controller = strtolower($controller[count($controller) - 2]);

        return $controller;
    }

    protected function getViewPath($action): string
    {
        $action = explode('@', $action);
        $action = explode('\\', $action[0]);
        array_pop($action);
        $path = implode(DIRECTORY_SEPARATOR, array_slice($action, 1));

        return app_path($path . '/views');
    }

    protected function getNextRedirect($response = [], $keyword = 'next')
    {
        $next = $response[$keyword];
        if (!isset($next)) {
            return $response;
        }

        unset($response[$keyword]);

        if (is_string($next)) {
            if (substr($next, 0, 1) == '/') {
                $redirect = redirect($next);
            } elseif ($next == 'back') {
                $redirect = redirect()->back();
            } else {
                $redirect = redirect()->route($next);
            }
        } elseif (is_array($next)) {
            if ($next['back']) {
                $redirect = redirect()->back();
            } elseif ($next['path']) {
                $redirect = redirect($next['path']);
            } elseif ($next['next']) {
                $redirect = redirect()->route($next['route']);
            }
        } elseif ($next instanceof Closure) {
            $redirect = $next();
        }

        foreach ($response as $key => $value) {
            $redirect = $redirect->with($key, $value);
        }

        return $redirect;
    }

    /**
     * Get the value of data
     */
    public function getResponse()
    {
        return $this->response;
    }
}
