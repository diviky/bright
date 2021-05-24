<?php

declare(strict_types=1);

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
