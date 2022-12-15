<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Concerns;

use Illuminate\Database\Query\Expression;
use Illuminate\Support\Str;

trait Eventable
{
    /**
     * Event State.
     *
     * @var bool
     */
    protected $eventState = true;

    /**
     * Event column names.
     *
     * @var array
     */
    protected $eventColumns = [];

    /**
     * @var int|string
     */
    protected $lastId;

    /**
     * Event State Executed.
     *
     * @var bool
     */
    protected $executed = false;

    /**
     * Set event state.
     *
     * @param bool $event
     *
     * @return static
     */
    public function eventState($event = false)
    {
        return $this->es($event);
    }

    /**
     * Set event state.
     *
     * @param bool $event
     *
     * @return static
     */
    public function es($event = false)
    {
        $this->eventState = $event;

        return $this;
    }

    /**
     * Set the event.
     *
     * @deprecated 2.0
     *
     * @param bool|string $event
     *
     * @return static
     */
    public function setEvent($event)
    {
        if (\is_bool($event)) {
            return $this->eventState($event);
        }

        return $this->eventColumn($event);
    }

    /**
     * Set the event column.
     *
     * @param array|string $name
     *
     * @return static
     */
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
     *
     * @return int|string
     */
    public function getLastId()
    {
        return $this->lastId;
    }

    /**
     * Run the query in async mode.
     *
     * @param array|string $events
     *
     * @return static
     */
    public function events($events = null)
    {
        $this->connection->events($events);

        return $this;
    }

    public function insertEvent(array $values): array
    {
        if (!$this->useEvent()) {
            return $values;
        }

        if ($this->executed) {
            return $values;
        }

        return $this->addInsertEvent($values);
    }

    /**
     * Update the values.
     *
     * @return array
     */
    public function updateEvent(array $values)
    {
        $this->atomicEvent('update');

        $bright = $this->getConfig();

        if (false !== $bright['timestamps']) {
            $values = $this->setTimeStamp($values);
        }

        return $values;
    }

    /**
     * Get the value of event.
     */
    protected function useEvent(): bool
    {
        $bright = $this->getConfig();

        if (isset($bright['db_events']) && false == $bright['db_events']) {
            return false;
        }

        return $this->eventState;
    }

    protected function setPrimaryKey(array $values, string $id): array
    {
        if (!isset($values['id'])) {
            $values['id'] = $id;
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

    /**
     * Get the event tables.
     *
     * @param string $type
     */
    protected function getEventTables($type): array
    {
        $bright = $this->getConfig();

        $bright = $bright['tables'];

        $from = $this->getTableBaseName();

        if (isset($bright['ignore'], $bright['ignore'][$from])) {
            return [];
        }

        if (\is_array($bright['default'])) {
            $tables = \array_merge($bright['default'], $bright[$type]);
        } else {
            $tables = $bright[$type];
        }

        return isset($tables[$from]) ? array_unique($tables[$from]) : [];
    }

    protected function addInsertEvent(array $values): array
    {
        if (!\is_array(\reset($values))) {
            $values = [$values];
        }

        $tables = $this->getEventTables('insert');

        foreach ($tables as $column => $field) {
            if (\is_numeric($column)) {
                $column = $field;
            }

            foreach ($values as $key => $value) {
                if (isset($value[$column])) {
                    continue;
                }

                switch ($column) {
                    case 'id':
                        $value = $this->setPrimaryKey($value, (string) Str::uuid());

                        break;
                    case 'uid':
                    case 'uuid':
                        $value = $this->setPrimaryKey($value, (string) Str::orderedUuid());

                        break;
                    case 'user_id':
                        $value = $this->setUserId($value);

                        break;
                    case 'time':
                        $value = $this->setTimeStamps($value, true);

                        break;
                    default:
                        if (false !== strpos($field, 'user.')) {
                            $value[$column] = user(ltrim($field, 'user.'));
                        } elseif (app()->has($field)) {
                            $value[$column] = app($field);
                        }

                        break;
                }

                $values[$key] = $value;
            }
        }

        $bright = $this->getConfig();

        if (false !== $bright['timestamps']) {
            foreach ($values as &$value) {
                $value = $this->setTimeStamps($value);
            }
        }

        return $values;
    }

    /**
     * Event.
     *
     * @param string $type
     *
     * @return static
     */
    protected function atomicEvent($type = 'update')
    {
        if (!$this->useEvent()) {
            return $this;
        }

        if ($this->executed) {
            return $this;
        }

        $this->executed = true;

        $eventColumns = $this->getEventTables($type);
        $eventColumns = \array_merge($eventColumns, $this->eventColumns);

        if ($this->from instanceof Expression) {
            $from = \preg_split('/ as /i', $this->from->getValue());
        } else {
            $from = \preg_split('/ as /i', $this->from);
        }

        $mainAlias = (\count($from) > 1) ? last($from) . '.' : '';

        foreach ($eventColumns as $columns) {
            if (!\is_array($columns)) {
                $columns = [$columns => $columns];
            }

            $alias = $mainAlias;
            $column = \key($columns);
            if (false !== \strpos($column, '.')) {
                list($alias, $column) = \explode('.', $column);

                $alias = $alias . '.';
            }

            $field = isset($columns[$column]) ? $columns[$column] : null;

            switch ($column) {
                case 'user_id':
                case 'parent_id':
                    $user_id = user('id');
                    if ($user_id) {
                        $this->where($alias . $column, $user_id);
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

    /**
     * Get the table name without alias.
     */
    protected function getTableBaseName(): string
    {
        if ($this->from instanceof Expression) {
            $from = \preg_split('/ as /i', $this->from->getValue())[0];
        } else {
            $from = \preg_split('/ as /i', $this->from)[0];
        }

        return $from;
    }
}
