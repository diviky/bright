<?php

namespace Diviky\Bright\Database\Traits;

trait Filter
{
    /**
     * Add Filters to database query builder.
     *
     * @param array  $data
     * @param string $alias
     *
     * @return array Database conditions
     */
    public function filter($data = [])
    {
        $filter = isset($data['dfilter']) ? $data['dfilter'] : null;
        if (\is_array($filter)) {
            foreach ($filter as $value => $column) {
                $value = $data[$value];
                if ('' != $value) {
                    $value = '%' . $value . '%';

                    $this->addWhere($column, $value, 'like');
                }
            }
        }

        $filter = isset($data['filter']) ? $data['filter'] : null;
        if (\is_array($filter)) {
            foreach ($filter as $k => $v) {
                if ('' != $v[0]) {
                    $this->addWhere($k, $v);
                }
            }
        }

        $filter = isset($data['lfilter']) ? $data['lfilter'] : null;
        if (\is_array($filter)) {
            foreach ($filter as $column => $value) {
                if ('' != $value) {
                    $value = '%' . $value . '%';

                    $this->addWhere($column, $value, 'like');
                }
            }
        }

        $filter = isset($data['rfilter']) ? $data['rfilter'] : null;
        if (\is_array($filter)) {
            foreach ($filter as $column => $value) {
                if ('' != $value) {
                    $value = $value . '%';

                    $this->addWhere($column, $value, 'like');
                }
            }
        }

        $filter = isset($data['efilter']) ? $data['efilter'] : null;
        if (\is_array($filter)) {
            foreach ($filter as $column => $value) {
                if ('' != $value) {
                    $value = '%' . $value;

                    $this->addWhere($column, $value, 'like');
                }
            }
        }

        $date_range = isset($data['date']) ? $data['date'] : null;
        if (\is_array($date_range)) {
            foreach ($date_range as $column => $date) {
                if (!\is_array($date)) {
                    $date = \explode(' - ', $date);
                    $date = [
                        'from' => $date[0],
                        'to'   => $date[1],
                    ];
                }

                $from   = $this->toTime($date['from'], 'Y-m-d');
                $to     = $this->toTime($date['to'], 'Y-m-d');
                $column = $this->cleanField($column);

                if ($from && $to) {
                    $this->whereDateBetween($column, [$from, $to]);
                } elseif ($from) {
                    $this->whereDate($column, '=', $from);
                }
            }
        }

        $datetime = isset($data['datetime']) ? $data['datetime'] : null;
        $datetime = $datetime ?: (isset($data['timestamp']) ? $data['timestamp'] : null);

        if (\is_array($datetime)) {
            foreach ($datetime as $column => $date) {
                if (empty($date)) {
                    continue;
                }
                if (!\is_array($date)) {
                    $date = \explode(' - ', $date);
                    $date = [
                        'from' => $date[0],
                        'to'   => $date[1],
                    ];
                }

                $from = \trim($date['from']);
                $to   = \trim($date['to']);
                $to   = $to ?: $from;

                $column = $this->cleanField($column);

                $from = $this->toTime($from, 'Y-m-d H:i:s', '00:00:00');
                $to   = $this->toTime($to, 'Y-m-d H:i:s', '23:59:59');

                $this->whereBetween($column, [$from, $to]);
            }
        }

        $unixtime = isset($data['unix']) ? $data['unix'] : null;
        $unixtime = $unixtime ?: (isset($data['unixtime']) ? $data['unixtime'] : null);

        if (\is_array($unixtime)) {
            foreach ($unixtime as $column => $date) {
                if (empty($date)) {
                    continue;
                }

                if (!\is_array($date)) {
                    $date = \explode(' - ', $date);
                    $date = [
                        'from' => $date[0],
                        'to'   => $date[1],
                    ];
                }

                $from   = \trim($date['from']);
                $to     = \trim($date['to']);
                $to     = $to ?: $from;
                $column = $this->cleanField($column);

                if (!\is_numeric($from)) {
                    $from = $this->toTime($from, 'Y-m-d H:i:s', '00:00:00')->timestamp();
                }

                if (!\is_numeric($to)) {
                    $to = $this->toTime($to, 'Y-m-d H:i:s', '23:59:59')->timestamp();
                }

                $this->whereBetween($column, [$from, $to]);
            }
        }

        $ranges = isset($data['range']) ? $data['range'] : null;
        if (\is_array($ranges)) {
            foreach ($ranges as $column => $date) {
                if (!\is_array($date)) {
                    $date = \explode(' - ', $date);
                    $date = [
                        'from' => \trim($date[0]),
                        'to'   => \trim($date[1]),
                    ];
                }

                $from   = $this->toTime($date['from']);
                $to     = $this->toTime($date['to']);
                $column = $this->cleanField($column);

                if ($from && $to) {
                    $this->whereBetween($column, [$from, $to]);
                } elseif ($from) {
                    $this->where($column, $from);
                }
            }
        }

        $between = isset($data['between']) ? $data['between'] : null;
        if (\is_array($between)) {
            foreach ($between as $column => $date) {
                if (!\is_array($date)) {
                    $date = \explode(' - ', $date);
                    $date = [
                        'from' => \trim($date[0]),
                        'to'   => \trim($date[1]),
                    ];
                }

                $from   = $date['from'];
                $to     = $date['to'];
                $column = $this->cleanField($column);

                if ($from && $to) {
                    $this->whereBetween($column, [$from, $to]);
                } elseif ($from) {
                    $this->where($column, $from);
                }
            }
        }

        return $this;
    }

    protected function addWhere($column, $value, $condition = '=')
    {
        if (false !== \strpos($column, '|')) {
            $columns = \explode('|', $column);
            $this->where(function ($query) use ($columns, $value, $condition) {
                foreach ($columns as $column) {
                    $query->orWhere($this->cleanField($column), $condition, $value);
                }
            });
        } else {
            $this->where($this->cleanField($column), $condition, $value);
        }

        return $this;
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
        if (\is_string($string)) {
            return $this->raw($this->wrap($string));
        }

        return $string;
    }

    /**
     * Convert time to proper format.
     *
     * @param string     $time
     * @param string     $format
     * @param null|mixed $prefix
     *
     * @return int|string
     */
    protected function toTime($time, $format = null, $prefix = null)
    {
        if (empty($time)) {
            return;
        }
        if (false !== \strpos($format, ':')) {
            $time = false !== \strpos($time, ':') ? $time : $time . ' ' . $prefix;
        }

        return carbon(\trim($time), $format);
    }
}
