<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Eloquent;

use Diviky\Bright\Database\Eloquent\Concerns\WithBuilder;
use Illuminate\Database\Eloquent\Builder as BaseBuilder;

class Builder extends BaseBuilder
{
    use WithBuilder;
}
