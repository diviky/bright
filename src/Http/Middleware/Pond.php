<?php

declare(strict_types=1);

namespace Diviky\Bright\Http\Middleware;

use Diviky\Bright\Concerns\WithUploads;

class Pond
{
    use WithUploads;

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        if (!$request->isMethod('POST') && !$request->isMethod('PUT')) {
            return $next($request);
        }

        $filepond = $request->input('filepond');

        if ($filepond && is_array($filepond)) {
            $filepond = array_unique($filepond);
            foreach ($filepond as $file) {
                if ($request->input($file)) {
                    $request->files->add([$file => $this->convertToFile($request->input($file))]);
                }
            }

            // $request = $request->createFromBase($request);
        }

        return $next($request);
    }
}
