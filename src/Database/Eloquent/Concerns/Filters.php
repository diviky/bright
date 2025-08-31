<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Eloquent\Concerns;

use Illuminate\Database\Eloquent\Builder;

trait Filters
{
    public function scopeFilter(Builder $query, array $data): self
    {
        return $query->filter($data);
    }

    public function scopeFilters(Builder $query, array $types = [], array $aliases = []): self
    {
        return $query->filters($types, $aliases);
    }
}
