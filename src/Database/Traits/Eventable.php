<?php

namespace Karla\Database\Traits;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

trait Eventable
{
    protected $event = true;

    protected $lastId;

    public function setEvent($event = false)
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

        if (!is_array(reset($values))) {
            $values = [$values];
        }

        $tables = $this->getEventTables('insert');
        foreach ($tables as $column) {
            foreach ($values as $key => $value) {
                if (isset($value[$column])) {
                    continue;
                }

                switch ($column) {
                    case 'id':
                        $value = $this->setPrimaryKey($value);
                        break;
                    case 'user_id':
                        $value = $this->setUserId($value);
                        break;
                    case 'time':
                        $value = $this->setTimeStamps($value, true);
                        break;
                    default:
                        if (app()->has($column)) {
                            $value[$column] = app($column);
                        }
                        break;
                }

                $values[$key] = $value;
            }
        }

        foreach ($values as &$value) {
            $value = $this->setTimeStamps($value);
        }

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

    /**
     * @{inheritdoc}
     */
    public function insert(array $values)
    {
        $values = $this->insertEvent($values);

        return parent::insert($values);
    }

    /**
     * @{inheritdoc}
     */
    public function insertGetId(array $values, $sequence = null)
    {
        $values = $this->insertEvent($values);

        $id = parent::insertGetId($values, $sequence);

        if (empty($id)) {
            $id = $this->getLastId();
        }

        return $id;
    }
    /**
     * @{inheritdoc}
     */
    public function delete($id = null)
    {
        $this->atomicEvent('delete');
        return parent::delete($id);
    }

    /**
     * @{inheritdoc}
     */
    public function exists()
    {
        $this->atomicEvent('select');
        return parent::exists();
    }

    public function update(array $values)
    {
        if ($this->usesTimestamps()) {
            $values['updated_at'] = $this->freshTimestamp();
        }

        $values = $this->updateEvent($values);

        return parent::update($values);
    }
}
