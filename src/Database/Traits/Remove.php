<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Traits;

trait Remove
{
    /**
     * Removable keys from where condition.
     *
     * @var mixed
     */
    protected $removeKeys;

    /**
     * Remove keys from where.
     *
     * @param mixed $keys
     */
    public function removeWhere($keys = null): self
    {
        $this->removeKeys = $keys;

        return $this;
    }

    public function removeWheres(): self
    {
        $keys = $this->removeKeys;

        if (\is_null($keys)) {
            return $this;
        }

        if (!\is_array($keys)) {
            $keys = [$keys];
        }

        $bindings = $this->getBindings();
        $bindkey  = 0;

        // Checking all the where items
        foreach ($this->wheres as $key => $value) {
            $count = 1;
            if ($value['values']) {
                $count = \count($value['values']);
            }

            // If the column is part of the autofilter routine
            if (\in_array($value['column'], $keys)) {
                for ($i = 0; $i < $count; ++$i) {
                    unset($bindings[$bindkey + $i]);
                }
                unset($this->wheres[$key]);
            }

            $bindkey = $bindkey + $count;
        }

        // Update the query Builder variables
        $this->wheres = \array_values($this->wheres);
        \array_values($bindings);

        $this->setBindings($bindings);

        return $this;
    }
}
