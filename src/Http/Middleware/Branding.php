<?php

declare(strict_types=1);

namespace Diviky\Bright\Http\Middleware;

use Diviky\Bright\Models\Models;
use Illuminate\Support\Facades\View;

class Branding
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function handle($request, \Closure $next)
    {
        $domain = $request->getHost();

        $row = Models::branding()::where('domain', $domain)
            ->es(false)
            ->first();

        if (\is_null($row)) {
            return $next($request);
        }

        if (isset(optional($row)->is_ssl) && $row->is_ssl && !$request->secure()) {
            return redirect()->secure($request->getRequestUri());
        }

        app()->owner = optional($row)->user_id;
        app()->name = optional($row)->name;
        app()->domain_id = optional($row)->id;

        $row = $this->format($row);

        View::share('branding', $row);

        $route = $request->route();

        if (isset($route) && !is_string($route)) {
            $route = $route->getName();

            if ($route == 'register' && $row->options['register'] != 1) {
                abort(401, 'Registrations are disabled');
            }
        }

        return $next($request);
    }

    /**
     * Format the branding details.
     *
     * @param  object  $row
     * @return object
     */
    protected function format($row)
    {
        $row->logo = disk($row->logo, 's3');
        $row->favico = disk($row->favico, 's3');
        $row->icon = $row->icon ? disk($row->icon, 's3') : $row->logo;

        if (!is_array($row->options)) {
            $row->options = \json_decode($row->options, true);
        }

        $row->style = isset($row->options['style']) ? $row->options['style'] : 'app';

        if ($row->name) {
            config(['mail.from.name' => $row->name]);
            config(['app.name' => $row->name]);
        }

        return $row;
    }
}
