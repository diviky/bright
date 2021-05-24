<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Eloquent\Concerns;

trait HasTimestamps
{
    /**
     * Enable timestamps.
     *
     * @return static
     */
    public function timestamps(bool $use = true)
    {
        $this->timestamps = $use;

        return $this;
    }
}
