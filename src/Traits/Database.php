<?php

namespace Karla\Traits;

use Illuminate\Support\Facades\DB;

/**
 * Short methods to add default conditions to query.
 *
 * @author Sankar <sankar.suda@gmail.com>
 */
trait Database
{
    /**
     * Add Filters to database query builder.
     *
     * @param array  $data
     * @param string $alias
     *
     * @return array Database conditions
     */
    public function filter($query, $data = [], $alias = null)
    {
        if ($alias) {
            $alias = $alias . '.';
        }

        $filter = isset($data['dfilter']) ? $data['dfilter'] : null;
        if (is_array($filter)) {
            foreach ($filter as $k => $v) {
                if ($data[$k] != '') {
                    $query->where($alias . $this->cleanField($v), 'like', '%' . $data[$k] . '%');
                }
            }
        }

        $filter = isset($data['filter']) ? $data['filter'] : null;
        if (is_array($filter)) {
            foreach ($filter as $k => $v) {
                if ($v[0] != '') {
                    $query->where($alias . $this->cleanField($k), $v);
                }
            }
        }

        $filter = isset($data['lfilter']) ? $data['lfilter'] : null;
        if (is_array($filter)) {
            foreach ($filter as $k => $v) {
                if ($v[0] != '') {
                    $query->where($alias . $this->cleanField($k), 'like', '%' . $v . '%');
                }
            }
        }

        $filter = isset($data['rfilter']) ? $data['rfilter'] : null;
        if (is_array($filter)) {
            foreach ($filter as $k => $v) {
                if ($v[0] != '') {
                    $query->where($alias . $this->cleanField($k), 'like', $v . '%');
                }
            }
        }

        $filter = isset($data['efilter']) ? $data['efilter'] : null;
        if (is_array($filter)) {
            foreach ($filter as $k => $v) {
                if ($v[0] != '') {
                    $query->where($alias . $this->cleanField($k), 'like', '%' . $v);
                }
            }
        }

        $date_range = isset($data['date']) ? $data['date'] : null;
        if (is_array($date_range)) {
            foreach ($date_range as $column => $date) {
                if (!is_array($date)) {
                    $date = explode(' - ', $date);
                    $date = [
                        'from' => $date[0],
                        'to' => $date[1],
                    ];
                }

                $from = $this->toTime($date['from'], 'Y-m-d');
                $to = $this->toTime($date['to'], 'Y-m-d');
                $column = $alias . $this->cleanField($column);

                if ($from && $to) {
                    $query->whereBetween(DB::raw('DATE(' . $column . ')'), [$from, $to]);
                }

                if ($from && empty($to)) {
                    $query->where(DB::raw('DATE(' . $column . ')'), $from);
                }
            }
        }

        $time_range = isset($data['time']) ? $data['time'] : null;
        if (is_array($time_range)) {
            foreach ($time_range as $column => $date) {
                if (!is_array($date)) {
                    $date = explode('-', $date);
                    $date = [
                        'from' => $date[0],
                        'to' => $date[1],
                    ];
                }

                $from = carbon($date['from'], 'Y-m-d');
                $to = carbon($date['to'], 'Y-m-d');
                $column = $alias . $this->cleanField($column);

                if ($from && $to) {
                    $query->whereBetween(DB::raw('DATE(FROM_UNIXTIME(' . $column . '))'), [$from, $to]);
                }

                if ($from && empty($to)) {
                    $query->where(DB::raw('DATE(FROM_UNIXTIME(' . $column . '))'), $from);
                }
            }
        }

        $date_range = isset($data['unix']) ? $data['unix'] : null;
        if (is_array($date_range)) {
            foreach ($date_range as $column => $date) {
                if (!is_array($date)) {
                    $date = explode('-', $date);
                    $date = [
                        'from' => $date[0],
                        'to' => $date[1],
                    ];
                }

                $from = trim($date['from']);
                $to = trim($date['to']);
                $column = $alias . $this->cleanField($column);

                if ($from && empty($to)) {
                    $to = $from;
                }

                if (!is_numeric($from)) {
                    $from = $this->toTime($from . ' 00:00:00')->timestamp();
                }

                if (!is_numeric($to)) {
                    $to = $this->toTime($to . ' 23:59:59')->timestamp();
                }

                $query->whereBetween($column, [$from, $to]);
            }
        }

        $ranges = isset($data['range']) ? $data['range'] : null;
        if (is_array($ranges)) {
            foreach ($ranges as $column => $date) {
                if (!is_array($date)) {
                    $date = explode('-', $date);
                    $date = [
                        'from' => $date[0],
                        'to' => $date[1],
                    ];
                }

                $from = $this->toTime($date['from']);
                $to = $this->toTime($date['to']);
                $column = $alias . $this->cleanField($column);

                if ($from && $to) {
                    $query->whereBetween($column, [$from, $to]);
                }

                if ($from && empty($to)) {
                    $query->where($column, $from);
                }
            }
        }

        return $query;
    }

    /**
     * Add Ordering to query.
     *
     * @param array &$data
     *
     * @return array
     */
    public function ordering($query, $data = [], $ordering = [])
    {
        if (isset($data['sort'])) {
            if (empty($data['order'])) {
                $data['sort'] = implode(' ', explode('|', $data['sort'], 2));
            }
            return $query->orderBy($data['sort'], strtolower($data['order']));
        } else {
            foreach ($ordering as $column => $type) {
                $query->orderBy($column, $type);
            }
        }

        return $query;
    }

    /**
     * Cleanup the give column.
     *
     * @param string $string Database column
     *
     * @return string Cleaned string
     */
    protected function cleanField($string)
    {
        return preg_replace("/[^\w\.\s]/", '', $string);
    }

    /**
     * Convert time to proper format.
     *
     * @param string $time
     * @param string $format
     *
     * @return int|string
     */
    public function toTime($time, $format = 'Y-m-d')
    {
        if (empty($time)) {
            return null;
        }

        return carbon(trim($time), $format);
    }

    public function database($table, $ordering = [])
    {
        $data = $this->all();

        $query = $this->db->table($table);
        $query = $this->conditions($query, $data);

        return $this->ordering($query, $data, $ordering);
    }
}