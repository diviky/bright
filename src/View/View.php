<?php

declare(strict_types=1);

namespace Diviky\Bright\View;

use Diviky\Bright\Services\Resolver;
use Illuminate\Routing\Controller;

class View
{
    /**
     * Make the view.
     *
     * @param  Controller|string  $controller
     * @param  string  $view
     * @param  array  $data
     * @param  array  $mergeData
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function make($controller, $view, $data = [], $mergeData = [])
    {
        Resolver::themeFromAction($controller);

        return view($view, $data, $mergeData);
    }
}
