<?php

namespace Diviky\Bright\Database\Eloquent\Concerns;

trait UseTimestamps
{
    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    public function timestamps(bool $use = true): self
    {
        $this->timestamps = $use;

        return $this;
    }

    /**
     * Determine if the builder uses timestamps.
     */
    protected function usesTimestamps(): bool
    {
        return $this->timestamps;
    }

    /**
     * Add the "updated at" column to an array of values.
     *
     * @return array
     */
    protected function addUpdatedAtColumn(array $values)
    {
        if (!$this->usesTimestamps()) {
            return $values;
        }

        return parent::addUpdatedAtColumn($values);
    }

    /**
     * Add timestamps to the inserted values.
     *
     * @return array
     */
    protected function addTimestampsToUpsertValues(array $values)
    {
        if (!$this->usesTimestamps()) {
            return $values;
        }

        return parent::addUpdatedAtColumn($values);
    }

    /**
     * Add the "updated at" column to the updated columns.
     *
     * @return array
     */
    protected function addUpdatedAtToUpsertColumns(array $update)
    {
        if (!$this->usesTimestamps()) {
            return $update;
        }

        return parent::addUpdatedAtToUpsertColumns($update);
    }
}
