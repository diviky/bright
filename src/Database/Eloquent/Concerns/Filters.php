<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Eloquent\Concerns;

trait Filters
{
    public function scopeFilter(array $data): self
    {
        $this->query->filter($data);

        return $this;
    }

    public function scopeFilters(array $types = [], array $aliases = []): self
    {
        $this->query->filters($types, $aliases);

        return $this;
    }

    public function filter(array $data): self
    {
        $this->query->filter($data);

        return $this;
    }

    public function filters(array $types = [], array $aliases = []): self
    {
        $this->query->filters($types, $aliases);

        return $this;
    }
}
