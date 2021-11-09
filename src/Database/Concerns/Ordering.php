<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Concerns;

trait Ordering
{
    /**
     * Add Ordering to query.
     *
     * @param array &$data
     * @param mixed $ordering
     */
    public function ordering($data = [], $ordering = []): self
    {
        if (isset($data['sort'])) {
            if (empty($data['order'])) {
                $sort = \explode('|', $data['sort'], 2);

                $data['sort'] = $sort[0];
                $data['order'] = $sort[1] ?? 'asc';
            }

            return $this->orderBy($data['sort'], \strtolower($data['order']));
        }

        if (\is_array($ordering)) {
            foreach ($ordering as $column => $type) {
                $this->orderBy($column, $type);
            }
        }

        return $this;
    }

    /**
     * Add Ordering to query.
     *
     * @param array &$data
     * @param mixed $ordering
     */
    public function sorting($data = [], $ordering = []): self
    {
        return $this->ordering($data, $ordering);
    }
}
