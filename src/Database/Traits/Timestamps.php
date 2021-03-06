<?php

namespace Diviky\Bright\Database\Traits;

trait Timestamps
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    protected $timestamps = true;

    public function timestamps($allow = true): self
    {
        $this->timestamps = $allow;

        return $this;
    }

    /**
     * Get a fresh timestamp for the model.
     *
     * @return \Illuminate\Support\Carbon
     */
    protected function freshTimestamp()
    {
        return utcTime();
    }

    /**
     * {@inheritdoc}
     */
    protected function setTimeStamps(array $values, $force = false): array
    {
        if ($this->usesTimestamps() || $force) {
            $time = $this->freshTimestamp();

            $values['updated_at'] = $time;
            $values['created_at'] = $time;
        }

        return $values;
    }

    protected function setTimeStamp(array $values, $force = false): array
    {
        if ($this->usesTimestamps() || $force) {
            $values['updated_at'] = $this->freshTimestamp();
        }

        return $values;
    }

    /**
     * Determine if the builder uses timestamps.
     */
    protected function usesTimestamps(): bool
    {
        return $this->timestamps;
    }
}
