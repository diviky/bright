<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Eloquent\Concerns;

use Diviky\Bright\Util\StdClass;

trait ArrayToObject
{
    public function toObject()
    {
        return new StdClass($this->toArray());
    }
}
