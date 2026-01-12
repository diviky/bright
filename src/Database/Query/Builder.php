<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Query;

use Diviky\Bright\Database\Concerns\WithBuilder;
use Illuminate\Database\Query\Builder as LaravelBuilder;

class Builder extends LaravelBuilder
{
    use WithBuilder;
}
