<?php

namespace Karla\Routing;

use Illuminate\Contracts\Support\Responsable as BaseResponsable;
use Illuminate\Contracts\View\View;
use Karla\Traits\Responsable as ResponsableTrait;
use Karla\Traits\Themable;

class Responsable implements BaseResponsable
{
    use Themable;
    use ResponsableTrait;

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

        if (!$format && $request->expectsJson()) {
            return $response;
        }

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
    /**
     * Get the value of data
     */
    public function getResponse()
    {
        return $this->response;
    }
}
