<?php

declare(strict_types=1);

namespace Diviky\Bright\View\Components;

use Illuminate\View\Component;

class Form extends Component
{
    public array $attribs;

    /**
     * Form method spoofing to support PUT, PATCH and DELETE actions.
     * https://laravel.com/docs/master/routing#form-method-spoofing
     */
    public bool $spoofMethod = false;

    public function __construct(
        ?string $action = null,
        ?string $route = null,
        public ?string $method = 'POST',
        public ?string $style = null,
        public bool $hasFiles = false,
        public bool $spellcheck = false,
    ) {

        if ($route) {
            $action = route($route);
        }

        $attribs = [];
        $attribs['action'] = $action;

        $this->attribs = $attribs;
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    #[\Override]
    public function render()
    {
        return view('bright::components.form');
    }
}
