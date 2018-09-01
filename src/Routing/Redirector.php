<?php

namespace Karla\Routing;

use Illuminate\Routing\Redirector as BaseRedirector;

class Redirector extends BaseRedirector
{
    /**
     * Create a new redirect response.
     *
     * @param string $path
     * @param int    $status
     * @param array  $headers
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function createRedirect($path, $status, $headers)
    {
        $request = $this->generator->getRequest();
        if ($request->ajax()) {
            return tap(new RedirectResponse($path, 200, $headers), function ($redirect) {
                if (isset($this->session)) {
                    $redirect->setSession($this->session);
                }

                $redirect->setRequest($this->generator->getRequest());
            });
        } else {
            return parent::createRedirect($path, $status, $headers);
        }
    }
}
