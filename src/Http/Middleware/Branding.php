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

        if ($request->secure()) {
            config(['security-headers.enable' => true]);
            config(['session.secure' => true]);
        }

        $row = Models::branding()::where('domain', $domain)
            ->remember(10 * 60, 'domain:' . $domain)
            ->es(false)
            ->first();

        View::share('branding', $row);

        if (\is_null($row)) {
            return $next($request);
        }

        if (isset($row, optional($row)->is_ssl) && $row->is_ssl && !$request->secure()) {
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
        if (!isset($row->options)) {
            $row->options = [];
        } elseif (!is_array($row->options)) {
            $row->options = \json_decode($row->options, true);
        }

        $disk = isset($row->options['disk']) ? $row->options['disk'] : 's3';
        $row->logo = disk($row->logo, $disk, 60);
        $row->favico = disk($row->favico, $disk, 60);
        $row->icon = $row->icon ? disk($row->icon, $disk, 60) : $row->logo;

        $row->style = isset($row->options['style']) ? $row->options['style'] : 'app';

        if ($row->name) {
            config(['mail.from.name' => $row->name]);
            config(['app.name' => $row->name]);
        }

        $configs = $row->options['config'] ?? [];

        if (is_array($configs)) {
            foreach ($configs as $key => $value) {
                config([$key => $value]);
            }
        }

        return $row;
    }
}
