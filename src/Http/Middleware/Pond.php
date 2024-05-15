<?php

declare(strict_types=1);

namespace Diviky\Bright\Http\Middleware;

use Diviky\Bright\Concerns\WithUploads;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class Pond
{
    use WithUploads;

    /**
     * Handle an incoming request.
     *
     * @param  Request  $request
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        if (!$request->isMethod('POST') && !$request->isMethod('PUT')) {
            return $next($request);
        }

        $filepond = $request->post('filepond');

        if ($filepond && is_array($filepond)) {
            $filepond = array_unique($filepond);
            foreach ($filepond as $file) {
                $file = Str::replace(['[', ']'], ['.', ''], $file);
                $file = rtrim($file, '.');

                $files = $request->post($file);

                if (is_array($files)) {
                    $uploads = [];
                    foreach ($files as $value) {
                        $uploads[] = $this->convertToFile($value);
                    }

                    $request->files->add([$file => $uploads]);
                } else {
                    $request->files->add([$file => $this->convertToFile($files)]);
                }
            }

            // $request = $request->createFromBase($request);
        }

        return $next($request);
    }
}
