<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Filters;

use Illuminate\Database\Eloquent\Builder;

interface Filter
{
    /**
     * @param mixed $value
     */
    public function __invoke(Builder $query, $value, string $property): void;
}
