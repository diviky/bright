<?php

namespace Diviky\Bright\Livewire\Concerns;

use Diviky\Bright\Concerns\Renderer as BrightRenderer;

trait Renderer
{
    use BrightRenderer;

    public function render()
    {
        $view = $this->setUpView()->getViewFrom($this);

        return view('bright::components.livewire', ['livewire' => $view]);
    }
}
