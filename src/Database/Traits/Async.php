<?php

namespace Karla\Database\Traits;

trait Async
{
    protected $async = false;

    public function async($async = true)
    {
        $this->async = $async;
        return $this;
    }

    protected function isAsync()
    {
        return $this->async;
    }

    protected function doAsync()
    {

    }
}
