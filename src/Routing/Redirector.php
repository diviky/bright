<?php

declare(strict_types=1);

namespace Diviky\Bright\Routing;

use Illuminate\Http\Request;
use Illuminate\Routing\Redirector as BaseRedirector;

class Redirector extends BaseRedirector
{
    /**
     * Create a new redirect response.
     *
     * @param  string  $path
     * @param  int  $status
     * @param  array  $headers
     * @return \Illuminate\Http\RedirectResponse
     */
    #[\Override]
    protected function createRedirect($path, $status, $headers)
    {
        $request = $this->generator->getRequest();
        if ($this->shouldReturnJsonRedirect($request)) {
            return tap(new RedirectResponse($path, 200, $headers), function ($redirect): void {
                if (isset($this->session)) {
                    $redirect->setSession($this->session);
                }

                $redirect->setRequest($this->generator->getRequest());
            });
        } else {
            return parent::createRedirect($path, $status, $headers);
        }
    }

    protected function shouldReturnJsonRedirect(Request $request): bool
    {
        if ($request->pjax() || $request->header('X-Inertia')) {
            return false;
        }

        if ($request->ajax()) {
            return true;
        }

        return $request->input('format') === 'json';
    }
}
