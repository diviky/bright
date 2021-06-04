<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Traits;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Carbon;

trait Filter
{
    /**
     * Add Filters to database query builder.
     *
     * @param array $data
     *
     * @return $this
     */
    public function filter($data = []): self
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
                if (isset($v) && '' != $v[0]) {
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
            $this->filterDateRange($date_range);
        }

        $datetime = isset($data['datetime']) ? $data['datetime'] : null;
        $datetime = $datetime ?: (isset($data['timestamp']) ? $data['timestamp'] : null);

        if (\is_array($datetime)) {
            $this->filterDatetime($datetime);
        }

        $unixtime = isset($data['unix']) ? $data['unix'] : null;
        $unixtime = $unixtime ?: (isset($data['unixtime']) ? $data['unixtime'] : null);

        if (\is_array($unixtime)) {
            $this->filterUnixTime($unixtime);
        }

        $ranges = isset($data['range']) ? $data['range'] : null;
        if (\is_array($ranges)) {
            $this->filterRange($ranges);
        }

        $between = isset($data['between']) ? $data['between'] : null;
        if (\is_array($between)) {
            $this->filterBetween($between);
        }

        return $this;
    }

    public function filterDateRange(array $date_range): self
    {
        foreach ($date_range as $column => $date) {
            if (!\is_array($date)) {
                $date = \explode(' - ', $date);
                $date = [
                    'from' => $date[0] ?? null,
                    'to' => $date[1] ?? null,
                ];
            }

            $from = $this->toTime($date['from'], 'Y-m-d');
            $to = $this->toTime($date['to'], 'Y-m-d');
            $column = $this->cleanField($column);

            if ($from && $to) {
                $this->whereDateBetween($column, [$from, $to]);
            } elseif ($from) {
                $this->whereDate($column, '=', $from);
            }
        }

        return $this;
    }

    public function filterDatetime(array $datetime): self
    {
        foreach ($datetime as $column => $date) {
            if (empty($date)) {
                continue;
            }
            if (!\is_array($date)) {
                $date = \explode(' - ', $date);
                $date = [
                    'from' => $date[0] ?? null,
                    'to' => $date[1] ?? null,
                ];
            }

            $from = \trim($date['from']);
            $to = \trim($date['to']);
            $to = $to ?: $from;

            $column = $this->cleanField($column);

            $from = $this->toTime($from, 'Y-m-d H:i:s', '00:00:00');
            $to = $this->toTime($to, 'Y-m-d H:i:s', '23:59:59');

            $this->whereBetween($column, [$from, $to]);
        }

        return $this;
    }

    public function filterUnixTime(array $unixtime): self
    {
        foreach ($unixtime as $column => $date) {
            if (empty($date)) {
                continue;
            }

            if (!\is_array($date)) {
                $date = \explode(' - ', $date);
                $date = [
                    'from' => $date[0] ?? null,
                    'to' => $date[1] ?? null,
                ];
            }

            $from = \trim($date['from']);
            $to = \trim($date['to']);
            $to = $to ?: $from;
            $column = $this->cleanField($column);

            if (!\is_numeric($from)) {
                $from = $this->toTime($from, null, '00:00:00');
                $from = $from && !is_string($from) ? $from->timestamp : null;
            }

            if (!\is_numeric($to)) {
                $to = $this->toTime($to, null, '23:59:59');
                $to = $to && !is_string($to) ? $to->timestamp : null;
            }

            $this->whereBetween($column, [$from, $to]);
        }

        return $this;
    }

    public function filterRange(array $ranges): self
    {
        foreach ($ranges as $column => $date) {
            if (!\is_array($date)) {
                $date = \explode(' - ', $date);
                $date = [
                    'from' => \trim($date[0]),
                    'to' => \trim($date[1]),
                ];
            }

            $from = $this->toTime($date['from']);
            $to = $this->toTime($date['to']);
            $column = $this->cleanField($column);

            if ($from && $to) {
                $this->whereBetween($column, [$from, $to]);
            } elseif ($from) {
                $this->where($column, $from);
            }
        }

        return $this;
    }

    public function filterBetween(array $between): self
    {
        foreach ($between as $column => $date) {
            if (!\is_array($date)) {
                $date = \explode(' - ', $date);
                $date = [
                    'from' => \trim($date[0]),
                    'to' => \trim($date[1]),
                ];
            }

            $from = $date['from'];
            $to = $date['to'];
            $column = $this->cleanField($column);

            if ($from && $to) {
                $this->whereBetween($column, [$from, $to]);
            } elseif ($from) {
                $this->where($column, $from);
            }
        }

        return $this;
    }

    /**
     * Add where condition for filters.
     *
     * @param string $column
     * @param string $value
     * @param string $condition
     */
    protected function addWhere($column, $value, $condition = '='): self
    {
        if (false !== \strpos($column, '|')) {
            $columns = \explode('|', $column);
            $this->where(function ($query) use ($columns, $value, $condition): void {
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
     * @return string Cleaned String
     */
    protected function cleanField($string)
    {
        if (false !== strpos($string, '.')) {
            list($alias, $column) = explode('.', $string, 2);

            return (string) $this->raw($alias . '.' . $this->wrap($column));
        }

        return (string) $this->raw($this->wrap($string));
    }

    /**
     * Convert time to proper format.
     *
     * @param string     $time
     * @param string     $format
     * @param null|mixed $prefix
     *
     * @return null|\Illuminate\Support\Carbon|string
     */
    protected function toTime($time, $format = null, $prefix = null)
    {
        if (empty($time)) {
            return null;
        }

        if (false !== \strpos($format, ':')) {
            $time = false !== \strpos($time, ':') ? $time : $time . ' ' . $prefix;
        }

        return carbon(\trim($time), $format);
    }
}
