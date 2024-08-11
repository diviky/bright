<?php

declare(strict_types=1);

namespace Diviky\Bright\View\Components;

use Illuminate\View\Component;

class Form extends Component
{
    public array $attribs;

    public function __construct(string $action)
    {
        $attribs = [];
        $attribs['easyrender'] = 'easyrender';
        $attribs['action'] = $action;

        $this->attribs = $attribs;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('bright::components.form');
    }
}
