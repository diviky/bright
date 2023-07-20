<?php

declare(strict_types=1);

namespace Diviky\Bright\View\Components;

use Illuminate\View\Component;

class Link extends Component
{
    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('bright::components.link');
    }
}
