<?php

namespace Diviky\Bright\Database\Eloquent\Concerns;

trait HasTimestamps
{
    public function timestamps(bool $use = true): self
    {
        $this->timestamps = $use;

        return $this;
    }
}
