<?php

namespace Karla\View;

use Illuminate\View\Factory as BaseFactory;

class Factory extends BaseFactory
{
    /**
     * Add a location to the array of view locations.
     *
     * @param  string  $location
     * @return void
     */
    public function prependLocation($location)
    {
        $this->finder->prependLocation($location);
    }

}
