<?php

namespace Karla\Database\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

trait Events
{
    protected function setPrimaryKey(array $values)
    {
        if (!isset($values['id'])) {
            $values['id'] = Str::uuid();
        }

        return $values;
    }

    protected function setUserId(array $values)
    {
        if (!isset($values['user_id'])) {
            $values['user_id'] = Auth::user()->id;
        }

        return $values;
    }

    protected function setTimeStamps(array $values, $force = false)
    {
        if ($this->usesTimestamps() || $force) {
            $time = $this->freshTimestamp();
            $values['updated_at'] = $time;
            $values['created_at'] = $time;
        }

        return $values;
    }

    protected function insertEvent($values)
    {
        $tables = [
            'smart_links' => ['user_id'],
            'smart_link_visits' => ['user_id', 'id'],
            'smart_link_visit_adv' => ['id'],
        ];

        if (isset($tables[$this->from])) {
            foreach ($tables[$this->from] as $column) {
                if (isset($values[$column])) {
                    continue;
                }

                switch ($column) {
                    case 'id':
                        $values = $this->setPrimaryKey($values);
                        break;
                    case 'user_id':
                        $values = $this->setUserId($values);
                        break;
                    case 'time':
                        $values = $this->setTimeStamps($values, true);
                        break;
                }
            }
        }

        $values = $this->setTimeStamps($values);

        return $values;
    }
}
