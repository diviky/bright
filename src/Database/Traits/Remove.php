<?php

namespace Diviky\Bright\Database\Traits;

trait Remove
{
    protected $removeKeys;

    public function removeWhere($keys = null)
    {
        $this->removeKeys = $keys;

        return $this;
    }

    public function removeWheres()
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
        foreach ((array) $this->wheres as $key => $value) {
            $type  = \strtolower($value['type']);
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
