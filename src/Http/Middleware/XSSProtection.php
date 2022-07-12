<?php

declare(strict_types=1);

namespace Diviky\Bright\Http\Middleware;

use Closure;

class XSSProtection
{
    /**
     * The following method loops through all request input and strips out all tags from
     * the request. This to ensure that users are unable to set ANY HTML within the form
     * submissions, but also cleans up input.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!in_array(strtolower($request->method()), ['put', 'post'])) {
            return $next($request);
        }

        $input = $request->all();

        array_walk_recursive($input, function (&$input): void {
            if (isset($input)) {
                $input = strip_tags($input);
            }
        });

        $request->merge($input);

        return $next($request);
    }
}
