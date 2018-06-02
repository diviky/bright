<?php

namespace Karla\Database\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

trait Events
{
    protected $event = true;

    protected $lastId;

    public function setEvent($event = true)
    {
        $this->setEvent = $event;

        return $this;
    }

    /**
     * Get the value of event
     */
    protected function useEvent()
    {
        return $this->event;
    }

    protected function setPrimaryKey(array $values)
    {
        if (!isset($values['id'])) {
            $values['id'] = Str::uuid();
        }

        $this->lastId = $values['id'];

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

    protected function setTimeStamp(array $values, $force = false)
    {
        if ($this->usesTimestamps() || $force) {
            $time = $this->freshTimestamp();
            $values['updated_at'] = $time;
        }

        return $values;
    }

    protected function getEventTables($type)
    {
        $tables = config('karla.tables');
        $tables = array_merge($tables['default'], $tables[$type]);
        $tables = isset($tables[$this->from]) ? $tables[$this->from] : [];

        return $tables;
    }

    protected function insertEvent($values)
    {
        if (!$this->useEvent()) {
            return $values;
        }

        $tables = $this->getEventTables('insert');
        foreach ($tables as $column) {
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
                default:
                    if (app()->has($column)) {
                        $values[$column] = app($column);
                    }
                    break;
            }
        }

        $values = $this->setTimeStamps($values);

        return $values;
    }

    protected function updateEvent($values)
    {
        if (!$this->useEvent()) {
            return $values;
        }

        $this->atomicEvent('update');
        $values = $this->setTimeStamp($values);

        return $values;
    }

    protected function atomicEvent($type = 'update')
    {
        $tables = $this->getEventTables($type);

        foreach ($tables as $column) {
            switch ($column) {
                case 'user_id':
                    $this->where($this->from . '.' . $column, Auth::user()->id);
                    break;
                default:
                    if (app()->has($column)) {
                        $this->where($this->from . '.' . $column, app($column));
                    }
                    break;
            }
        }

    }

    /**
     * Get the value of lastId
     */
    public function getLastId()
    {
        return $this->lastId;
    }
}
