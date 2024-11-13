<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Concerns;

use Illuminate\Support\Str;

trait Ordering
{
    /**
     * Add Ordering to query.
     *
     * @param  array  &$data
     * @param  mixed  $defaults
     */
    public function ordering($data = [], $defaults = []): self
    {
        if (isset($data['sort'])) {
            if (is_array($data['sort'])) {
                $sorted = $data['sort'];

                foreach ($sorted as $column => $direction) {
                    if (!is_string($column)) {
                        continue;
                    }

                    if (empty($direction)) {
                        if (Str::contains($column, ':')) {
                            $sort = \explode(':', $column, 2);
                        } else {
                            $sort = \explode('|', $column, 2);
                        }

                        $column = $sort[0];
                        $direction = $sort[1] ?? 'asc';
                    }

                    if (Str::contains($column, '.')) {
                        $this->builder->orderByPowerJoins($column, \strtolower($direction));

                        return $this;
                    } else {
                        return $this->orderBy($column, \strtolower($direction));
                    }
                }
            } else {
                $column = $data['sort'];
                $direction = $data['order'];

                if (empty($direction)) {
                    if (Str::contains($column, ':')) {
                        $sort = \explode(':', $column, 2);
                    } else {
                        $sort = \explode('|', $column, 2);
                    }

                    $column = $sort[0];
                    $direction = $sort[1] ?? 'asc';
                }

                if (Str::contains($column, '.')) {
                    $this->builder->orderByPowerJoins($column, \strtolower($direction));

                    return $this;
                } else {
                    return $this->orderBy($column, \strtolower($direction));
                }
            }
        }

        if (\is_array($defaults)) {
            foreach ($defaults as $column => $type) {
                $this->orderBy($column, $type);
            }
        }

        return $this;
    }

    /**
     * Add Ordering to query.
     *
     * @param  array  &$data
     * @param  mixed  $defaults
     */
    public function sorting($data = [], $defaults = []): self
    {
        return $this->ordering($data, $defaults);
    }
}
