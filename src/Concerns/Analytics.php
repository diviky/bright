<?php

declare(strict_types=1);

namespace Diviky\Bright\Concerns;

trait Analytics
{
    /**
     * Get the sql group and date formats.
     *
     * @param string $column
     * @param string $time
     *
     * @return array
     */
    public function getFormats($column, $time = 'daily')
    {
        switch ($time) {
            case 'weekly':
                $group = ['YEAR(' . $column . ')', 'WEEKOFYEAR(' . $column . ')'];
                $format = 'dS M';

                break;
            case 'monthly':
                $group = ['EXTRACT(YEAR_MONTH FROM ' . $column . ')'];
                $format = 'M Y';

                break;
            case 'hourly':
                $group = ['DATE_FORMAT(' . $column . ', \'%h:%p \')'];
                $format = 'h A';

                break;
            case 'daily':
                $group = ['DATE(' . $column . ')'];
                $format = 'dS M';

                break;
            default:
                list($start, $end, $day) = \array_pad(\explode(' - ', $time ?? ''), 3, null);

                if ($day) {
                    return $this->getFormats($column, $day);
                }

                $start = carbon($start);
                $end = $end ? carbon($end) : $start;

                $diff = $start->diffInDays($end);

                if ($diff < 1) {
                    return $this->getFormats($column, 'hourly');
                }

                if ($diff <= 13) {
                    return $this->getFormats($column, 'daily');
                }

                if ($diff <= 60) {
                    return $this->getFormats($column, 'weekly');
                }

                if ($diff <= 360) {
                    return $this->getFormats($column, 'monthly');
                }

                return $this->getFormats($column, 'yearly');

                break;
        }

        return [$format, $group, $time];
    }

    /**
     * Get the sql group and date formats.
     *
     * @param string $time
     * @param string $interval
     *
     * @return array
     */
    public function getRange($interval = 'auto', $time = '1d')
    {
        list($start, $end) = $this->getTimeRange($time);
        $minutes = 0;

        switch ($interval) {
            case '1m':
                $group = ['created_at'];
                $format = 'dS M h:i A';
                $minutes = 1;

                break;
            case '5m':
                $group = ['created_at'];
                $format = 'dS M h:i A';
                $minutes = 5;

                break;
            case '10m':
                $group = ['created_at'];
                $format = 'dS M h:i A';
                $minutes = 10;

                break;
            case '15m':
                $group = ['created_at'];
                $format = 'dS M h:i A';
                $minutes = 15;

                break;
            case '30m':
                $group = ['created_at'];
                $format = 'dS M h:i A';
                $minutes = 30;

                break;
            case '1h':
                $group = ['created_time'];
                $format = 'dS M h A';
                $minutes = 60;

                break;
            case '1d':
                $group = ['created_date'];
                $format = 'dS M';
                $minutes = 24 * 60;

                break;
            case '1w':
                $group = ['created_wk'];
                $format = 'dS M';
                $minutes = 7 * 24 * 60;

                break;
            case '1m':
                $group = ['created_ym'];
                $format = 'M Y';
                $minutes = 30 * 24 * 60;

                break;
            default:
                $diff = $start->diffInMinutes($end);

                return $this->getCustomRange($diff, $time);

                break;
        }

        return [$format, $group, $start, $end, $minutes, $interval];
    }

    /**
     * @psalm-pure
     *
     * @param float|int|string $num
     *
     * @return int|string
     */
    public function shortFormat($num)
    {
        $num = (float) $num;

        $n_format = '';
        $suffix = '';

        $num = intval($num);
        if ($num >= 0 && $num < 1000) {
            // 1 - 999
            $n_format = floor($num);
            $suffix = '';
        } elseif ($num >= 1000 && $num < 1000000) {
            // 1k-999k
            $n_format = floor($num / 1000);
            $suffix = 'K+';
        } elseif ($num >= 1000000 && $num < 1000000000) {
            // 1m-999m
            $n_format = floor($num / 1000000);
            $suffix = 'M+';
        } elseif ($num >= 1000000000 && $num < 1000000000000) {
            // 1b-999b
            $n_format = floor($num / 1000000000);
            $suffix = 'B+';
        } elseif ($num >= 1000000000000) {
            // 1t+
            $n_format = floor($num / 1000000000000);
            $suffix = 'T+';
        }

        return !empty($n_format . $suffix) ? $n_format . $suffix : 0;
    }

    /**
     * @param int    $diff
     * @param string $time
     */
    protected function getCustomRange($diff, $time): array
    {
        if ($diff <= 30) {
            return $this->getRange('1m', $time);
        }

        if ($diff <= 60) {
            return $this->getRange('5m', $time);
        }

        if ($diff <= 3 * 60) {
            return $this->getRange('10m', $time);
        }

        if ($diff <= 6 * 60) {
            return $this->getRange('15m', $time);
        }

        if ($diff <= 12 * 60) {
            return $this->getRange('30m', $time);
        }

        if ($diff <= 24 * 60) {
            return $this->getRange('1h', $time);
        }

        if ($diff <= 30 * 24 * 60) {
            return $this->getRange('1d', $time);
        }

        if ($diff <= 60 * 24 * 60) {
            return $this->getRange('1w', $time);
        }

        if ($diff <= 360 * 24 * 60) {
            return $this->getRange('1m', $time);
        }

        return $this->getRange('1d', $time);
    }

    /**
     * @param string $time
     *
     * @return array
     */
    protected function getTimeRange($time = '1d')
    {
        $now = now();
        if ('1h' == $time) {
            return [$now->copy()->subHours(1), $now];
        }

        if ('3h' == $time) {
            return [$now->copy()->subHours(3), $now];
        }

        if ('6h' == $time) {
            return [$now->copy()->subHours(6), $now];
        }

        if ('12h' == $time) {
            return [$now->copy()->subHours(12), $now];
        }

        if ('1d' == $time) {
            return [$now->copy()->subDays(1), $now];
        }

        if ('3d' == $time) {
            return [$now->copy()->subDays(3), $now];
        }

        if ('1w' == $time) {
            return [$now->copy()->subWeeks(1), $now];
        }

        if ('1m' == $time) {
            return [$now->copy()->subMonths(1), $now];
        }

        if ('3m' == $time) {
            return [$now->copy()->subMonths(3), $now];
        }

        if ('6m' == $time) {
            return [$now->copy()->subMonths(6), $now];
        }

        list($start, $end) = \array_pad(\explode(' - ', $time ?? ''), 3, null);
        $start = carbon($start);
        $end = $end ? carbon($end) : $start;

        return [$start, $end];
    }

    /**
     * Generate the time ranges.
     *
     * @param \Illuminate\Support\Carbon $start
     * @param \Illuminate\Support\Carbon $end
     * @param int                        $interval
     * @param string                     $format
     */
    protected function getDateLables($start, $end, $interval = 15, $format = 'h:i A'): array
    {
        $next = true;
        $i = 0;
        $lables = [];
        do {
            if ($start->gte($end) || $i > 30) {
                $next = false;
            }

            $timestamp = intval(round($start->timestamp / ($interval * 60)) * ($interval * 60));

            $lables[] = date($format, $timestamp);

            $start->addMinutes($interval);
            ++$i;
        } while ($next);

        return $lables;
    }
}
