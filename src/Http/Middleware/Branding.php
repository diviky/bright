<?php

namespace Karla\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\View;
use Karla\Models\Models;
use stdClass;

class Branding
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure                 $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $domain = $request->getHost();

        $row = Models::branding()::where('domain', $domain)
            ->eventState(false)
            ->first();

        if (is_null($row)) {
            $row = new stdClass();
        }

        if (isset(optional($row)->is_ssl) && !$request->secure()) {
            return redirect()->secure($request->getRequestUri());
        }

        if (!is_null($row)) {
            app()->owner     = $row->user_id;
            app()->name      = $row->name;
            app()->domain_id = $row->id;
        }

        $row = $this->format($row);

        View::share('branding', $row);

        $route = $request->route()->getName();

        if ($route == 'register' && $row->options['register'] != 1) {
            abort(401, "Registrations are disabled");
        }

        return $next($request);
    }

    protected function format($row)
    {
        $row->logo    = disk($row->logo, 's3');
        $row->favico  = disk($row->favico, 's3');
        $row->icon    = $row->icon ? disk($row->icon, 's3') : $row->logo;
        $row->options = json_decode($row->options, true);
        $row->style   = isset($row->options['style']) ? $row->options['style'] : 'app';

        if ($row->name) {
            config(['mail.from.name' => $row->name]);
            config(['app.name' => $row->name]);
        }

        return $row;
    }
}
