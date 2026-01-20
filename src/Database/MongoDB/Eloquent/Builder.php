<?php

namespace Diviky\Bright\Database\MongoDB\Eloquent;

use Diviky\Bright\Database\Eloquent\Concerns\WithBuilder;
use MongoDB\Laravel\Eloquent\Builder as BaseBuilder;

class Builder extends BaseBuilder
{
    use WithBuilder;

    public function toSql()
    {
        return $this->toMql();
    }

    public function toRawSql()
    {
        return $this->toMql();
    }
}
