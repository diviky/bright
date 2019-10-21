<?php

namespace Karla\Database\Traits;

use Illuminate\Support\Str;

trait Eventable
{
    protected $eventState   = true;
    protected $eventColumns = [];
    protected $lastId;

    public function eventState($event = false)
    {
        $this->eventState = $event;

        return $this;
    }

    public function setEvent($event)
    {
        if (\is_bool($event)) {
            return $this->eventState($event);
        }

        return $this->eventColumn($event);
    }

    public function eventColumn($name)
    {
        if (\is_array($name)) {
            $this->eventColumns = \array_merge($this->eventColumns, $name);
        } else {
            $this->eventColumns[] = $name;
        }

        return $this;
    }

    /**
     * Get the value of lastId.
     */
    public function getLastId()
    {
        return $this->lastId;
    }

    /**
     * Get the value of event.
     */
    protected function useEvent(): bool
    {
        return $this->eventState;
    }

    protected function setPrimaryKey(array $values, $id = null): array
    {
        if (!isset($values['id'])) {
            $values['id'] = (string) $id;
        }

        $this->lastId = $values['id'];

        return $values;
    }

    protected function setUserId(array $values): array
    {
        if (!isset($values['user_id']) && user('id')) {
            $values['user_id'] = user('id');
        }

        return $values;
    }

    protected function getEventTables($type): array
    {
        $karla = $this->connection->getConfig('karla');
        $karla = $karla['tables'];

        $from = \preg_split('/ as /i', $this->from)[0];

        if ($karla['ignore'] && isset($karla['ignore'][$from])) {
            return [];
        }

        if (\is_array($karla['default'])) {
            $tables = \array_merge($karla['default'], $karla[$type]);
        } else {
            $tables = $karla[$type];
        }

        return isset($tables[$from]) ? $tables[$from] : [];
    }

    protected function insertEvent(array $values): array
    {
        if (!$this->useEvent()) {
            return $values;
        }

        if (!\is_array(\reset($values))) {
            $values = [$values];
        }

        $tables = $this->getEventTables('insert');
        foreach ($tables as $columns) {
            if (!\is_array($columns)) {
                $columns = [$columns => $columns];
            }

            $column = \key($columns);
            $field  = $columns[$column];

            foreach ($values as $key => $value) {
                if (isset($value[$column])) {
                    continue;
                }

                switch ($column) {
                    case 'id':
                        $value = $this->setPrimaryKey($value, Str::uuid());

                        break;
                    case 'uid':
                    case 'uuid':
                        $value = $this->setPrimaryKey($value, Str::orderedUuid());

                        break;
                    case 'user_id':
                        $value = $this->setUserId($value);

                        break;
                    case 'time':
                        $value = $this->setTimeStamps($value, true);

                        break;
                    default:
                        if (app()->has($field)) {
                            $value[$column] = app($field);
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
        $this->atomicEvent('update');

        return $this->setTimeStamp($values);
    }

    protected function atomicEvent($type = 'update')
    {
        if (!$this->useEvent()) {
            return $this;
        }

        $eventColumns = $this->getEventTables($type);
        $eventColumns = \array_merge($eventColumns, $this->eventColumns);
        $from         = \preg_split('/ as /i', $this->from);
        $mainAlias    = (\count($from) > 1) ? last($from) . '.' : '';

        foreach ($eventColumns as $columns) {
            if (!\is_array($columns)) {
                $columns = [$columns => $columns];
            }

            $alias  = $mainAlias;
            $column = \key($columns);
            if (false !== \strpos($column, '.')) {
                list($alias, $column) = \explode('.', $column);

                $alias = $alias . '.';
            }

            $field = isset($columns[$column]) ? $columns[$column] : null;

            switch ($column) {
                case 'user_id':
                    $user_id = user('id');
                    if ($user_id) {
                        $this->where($alias . $column, $user_id);
                    }

                    break;
                case 'parent_id':
                    $parent_id = user('id');
                    if ($parent_id) {
                        $this->where($alias . $column, $parent_id);
                    }

                    break;
                default:
                    if ($field && app()->has($field)) {
                        $this->where($alias . $column, app()->get($field));
                    }

                    break;
            }
        }

        return $this;
    }
}
