<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Eloquent\Concerns;

trait Filters
{
    public function filter(array $data): self
    {
        $this->query->setEloquent($this)->filter($data);

        return $this;
    }
}
