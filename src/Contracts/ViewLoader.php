<?php

namespace Diviky\Bright\Contracts;

interface ViewLoader
{
    /**
     * Set the view locations.
     *
     * @return string[]
     */
    public function loadViewsFrom(): array;
}
