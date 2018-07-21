<?php

namespace Karla\Database\Traits;

use Illuminate\Support\Carbon;

trait Timestamps
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    protected $timestamps = true;

    /**
     * {@inheritdoc}
     */
    protected function setTimeStamps(array $values, $force = false)
    {
        if ($this->usesTimestamps() || $force) {
            $time = $this->freshTimestamp();

            $values['updated_at'] = $time;
            $values['created_at'] = $time;
        }

        return $values;
    }

    protected function setTimeStamp(array $values, $force = false)
    {
        if ($this->usesTimestamps() || $force) {
            $time                 = $this->freshTimestamp();
            $values['updated_at'] = $time;
        }

        return $values;
    }

    /**
     * Get a fresh timestamp for the model.
     *
     * @return \Illuminate\Support\Carbon
     */
    public function freshTimestamp()
    {
        return new Carbon();
    }

    /**
     * Determine if the builder uses timestamps.
     *
     * @return bool
     */
    protected function usesTimestamps()
    {
        return $this->timestamps;
    }

    public function timestamps($allow = true)
    {
        $this->timestamps = $allow;

        return $this;
    }
}
