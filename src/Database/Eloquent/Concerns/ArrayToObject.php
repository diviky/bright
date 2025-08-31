<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Eloquent\Concerns;

use Diviky\Bright\Support\Collection;

trait ArrayToObject
{
    public function toObject()
    {
        return new Collection($this->toArray());
    }
}
