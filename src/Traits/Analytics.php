<?php

namespace Karla\Traits;

trait Analytics
{
    public function getFormats($column, $time = 'daily')
    {
        switch ($time) {
            case 'weekly':
                $group  = ['YEAR(' . $column . ')', 'WEEKOFYEAR(' . $column . ')'];
                $format = 'dS M';

                break;
            case 'monthly':
                $group  = ['EXTRACT(YEAR_MONTH FROM ' . $column . ')'];
                $format = 'M Y';

                break;
            case 'hourly':
                $group  = ['DATE_FORMAT(' . $column . ', \'%h:%p \')'];
                $format = 'h A';

                break;
            case 'daily':
                $group  = ['DATE(' . $column . ')'];
                $format = 'dS M';

                break;
            default:
                list($start, $end, $day) = array_pad(\explode(' - ', $time), 3, null);

                if ($day) {
                    return $this->getFormats($column, $day);
                }

                $start = carbon($start);
                $end   = $end ? carbon($end) : $start;

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
}
