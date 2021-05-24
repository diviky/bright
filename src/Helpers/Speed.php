<?php

declare(strict_types=1);

namespace Diviky\Bright\Helpers;

use Diviky\Bright\Routing\Capsule;
use Illuminate\Support\Facades\DB;

/**
 * @author Sankar <sankar.suda@gmail.com>
 */
class Speed extends Capsule
{
    /**
     * @param array       $save
     * @param null|string $required
     *
     * @return array[]
     *
     * @psalm-return list<array>
     */
    public function formatToSave($save, $required = null): array
    {
        $required = ($required) ? \explode(',', $required) : null;
        $fields   = \array_keys($save);

        $pass = true;
        // check all required keys exits in fields
        if (!empty($required)) {
            foreach ($required as $key) {
                if (!\in_array($key, $fields)) {
                    $pass = false;

                    break;
                }
            }
        }

        if (true !== $pass) {
            return [];
        }

        $total = \count($save[$fields[0]]);
        $data  = [];

        for ($i = 0; $i < $total; ++$i) {
            $row = [];
            $add = true;
            foreach ($fields as $field) {
                $value = $save[$field][$i];

                if (!empty($required)
                    && \in_array($field, $required)
                    && empty($value)
                ) {
                    $add = false;

                    continue;
                }

                $row[$field] = $value;
            }

            if ($add) {
                $data[] = $row;
            }
        }
        unset($save, $required);

        return $data;
    }

    /**
     * Get the next ordering value.
     *
     * @param string $tbl
     * @param array  $where
     *
     * @return int
     */
    public function nextOrder($tbl, $where = [])
    {
        $max = DB::table($tbl)
            ->where($where)
            ->max('ordering');

        return $max + 1;
    }

    /**
     * Re-order the database ordering column.
     *
     * @param string $table
     * @param array  $where
     * @param string $field
     */
    public function reOrder($table, $where = [], $field = 'id'): self
    {
        $rows = DB::table($table)
            ->where($where)
            ->orderBy('ordering', 'asc')
            ->get([$field, 'ordering']);

        // compact the ordering numbers
        $i = 0;
        foreach ($rows as $row) {
            ++$i;
            if ($row->ordering != $i) {
                DB::table($table)
                    ->where($field, $row->{$field})
                    ->timestamps(false)
                    ->update(['ordering' => $i]);
            }
        }

        return $this;
    }

    /**
     * Sort and re-order the ordering column.
     *
     * @param string $table
     * @param string $field
     */
    public function sorting($table, array $values = [], $field = 'id'): self
    {
        if (empty($values)) {
            return $this;
        }

        $i = 0;
        foreach ($values as $id => $value) {
            if (\is_array($value)) {
                $this->sorting($table, $value, $field);
            } else {
                ++$i;
                if ($value != $i) {
                    $update = ['ordering' => $i];
                    DB::table($table)
                        ->where($field, $id)
                        ->timestamps(false)
                        ->update($update);
                }
            }
        }

        return $this;
    }
}
