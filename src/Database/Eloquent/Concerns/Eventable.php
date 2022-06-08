<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Eloquent\Concerns;

trait Eventable
{
    /**
     * Set event state.
     *
     * @param bool $event
     *
     * @return static
     */
    public function eventState($event = false)
    {
        return $this->es($event);
    }

    /**
     * Set event state.
     *
     * @param bool $event
     *
     * @return static
     */
    public function es($event = false)
    {
        $this->query->setEloquent($this)->es($event);

        return $this;
    }

    /**
     * Set the event column.
     *
     * @param array|string $name
     *
     * @return static
     */
    public function eventColumn($name)
    {
        $this->query->setEloquent($this)->eventColumn($name);

        return $this;
    }

    /**
     * Run the query in async mode.
     *
     * @param array|string $events
     *
     * @return static
     */
    public function events($events = null)
    {
        $this->query->setEloquent($this)->events($events);

        return $this;
    }
}
