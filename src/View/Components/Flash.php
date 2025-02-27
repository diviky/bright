<?php

declare(strict_types=1);

namespace Diviky\Bright\View\Components;

use Illuminate\View\Component;

class Flash extends Component
{
    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    #[\Override]
    public function render()
    {
        return view('bright::components.flash');
    }
}
