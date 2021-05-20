<?php

namespace Diviky\Bright\Routing;

use Diviky\Bright\Traits\Themable;
use Illuminate\Contracts\Support\Responsable as BaseResponsable;
use Illuminate\Contracts\View\View;

class Responsable implements BaseResponsable
{
    use Themable;

    /**
     * @var mixed
     */
    protected $response;

    /**
     * @var string
     */
    protected $action;

    /**
     * Undocumented variable.
     *
     * @var mixed
     */
    protected $controller;

    /**
     * @param mixed  $response
     * @param string $action
     * @param mixed  $controller
     */
    public function __construct($response, $action, $controller)
    {
        $this->response   = $response;
        $this->action     = $action;
        $this->controller = $controller;
    }

    /**
     * Create an HTTP response that represents the object.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed|\Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request)
    {
        $response = $this->getResponse();
        $format   = $request->input('_request') ?: $request->input('format');

        if (!$format && $request->expectsJson()) {
            return $response;
        }

        $route = $this->getRoute($this->action);

        if (!\is_array($response) && $response instanceof View) {
            $this->setUpTheme($route);

            return $response;
        }

        if (!\is_array($response)) {
            return $response;
        }

        $requestType = $request->input('_request');
        if ('iframe' == $requestType) {
            $html = '<textarea>';
            $html .= \json_encode($response);
            $html .= '</textarea>';

            return $html;
        }

        $ajax = $request->ajax();

        if (!$ajax && isset($response['next'])) {
            return $this->getNextRedirect($response, 'next');
        }

        if ($ajax && isset($response['redirect'])) {
            if ('/' !== \substr($response['redirect'], 0, 1)
            && 'http' !== \substr($response['redirect'], 0, 4)
            ) {
                $redirect = $this->getNextRedirect($response, 'redirect');
                if (isset($redirect)) {
                    $response['redirect'] = $redirect->getTargetUrl();
                }
            }
        }

        if ($ajax && isset($response['route'])) {
            $redirect             = $this->getNextRedirect($response, 'route');
            if (isset($redirect)) {
                $response['redirect'] = $redirect->getTargetUrl();
            }
        }

        if ('json' == $format) {
            return $response;
        }

        if (isset($response['_format']) && 'json' == $response['_format']) {
            unset($response['_format']);

            return $response;
        }

        if ($request->pjax()) {
            $format = 'html';
        }

        list($component, $view) = \explode('.', $route);

        $path   = $this->getViewsFrom($this->controller, $this->action);
        $theme  = $this->setUpTheme($route, $component, $path);
        $layout = 'html' == $format ? 'html' : $theme['layout'];

        return $this->getView($view, $response, $layout);
    }

    /**
     * Get the value of data.
     *
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }
}
