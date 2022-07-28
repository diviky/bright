<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Eloquent\Concerns;

trait HasEvents
{
    /**
     * Fire the given event for the model.
     *
     * @param string $event
     * @param bool   $halt
     *
     * @return mixed
     */
    public function fireEvent($event, $halt = true)
    {
        return $this->fireModelEvent($event, $halt);
    }

    public function getRawAttributes(): array
    {
        return $this->attributes;
    }
}
