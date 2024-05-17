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
            if (empty($data['order'])) {
                if (Str::contains($data['sort'], ':')) {
                    $sort = \explode(':', $data['sort'], 2);
                } else {
                    $sort = \explode('|', $data['sort'], 2);
                }

                $data['sort'] = $sort[0];
                $data['order'] = $sort[1] ?? 'asc';
            }

            if (Str::contains($data['sort'], '.')) {
                return $this->orderByPowerJoins($data['sort'], \strtolower($data['order']));
            } else {
                return $this->orderBy($data['sort'], \strtolower($data['order']));
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
