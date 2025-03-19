<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Concerns;

use Illuminate\Support\Facades\Context;
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
     * @return static
     */
    public function eventState(bool $event = false)
    {
        return $this->es($event);
    }

    /**
     * Set event state.
     *
     * @return static
     */
    public function es(bool $event = false)
    {
        $this->eventState = $event;

        return $this;
    }

    /**
     * Set the event column.
     *
     * @param  array|string  $name
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
     * @param  array|string  $events
     * @return static
     */
    public function events($events = null)
    {
        $this->connection->events($events);

        return $this;
    }

    public function insertEvent(array $values): array
    {
        if (!\is_array(\reset($values))) {
            $values = [$values];
        }

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

        if ($bright['timestamps'] !== false) {
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

        if (isset($bright['db_events']) && $bright['db_events'] == false) {
            return false;
        }

        return $this->eventState;
    }

    /**
     * Get the event tables.
     *
     * @param  string  $type
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

        return isset($tables[$from]) ? $tables[$from] : [];
    }

    protected function addInsertEvent(array $values): array
    {
        $tables = $this->getEventTables('insert');

        foreach ($tables as $column => $field) {
            if (\is_numeric($column)) {
                $column = $field;
            }

            foreach ($values as $key => $value) {
                if (isset($value[$column])) {
                    continue;
                }

                switch ($field) {
                    case 'id':
                        $value[$column] = (string) Str::uuid();

                        break;
                    case 'uid':
                    case 'uuid':
                        $value[$column] = (string) Str::orderedUuid();

                        break;
                    case 'user_id':
                        $value[$column] = user('id');

                        break;
                    case 'time':
                        $time = $this->freshTimestamp();

                        $values['updated_at'] = $time;
                        $values['created_at'] = $time;

                        break;
                    default:
                        if (strpos($field, 'user.') !== false) {
                            $value[$column] = user(ltrim($field, 'user.'));
                        } elseif (Context::has($field)) {
                            $value[$column] = Context::get($field);
                        }

                        break;
                }

                $values[$key] = $value;
            }
        }

        $bright = $this->getConfig();

        if ($bright['timestamps'] !== false) {
            foreach ($values as &$value) {
                $value = $this->setTimeStamps($value);
            }
        }

        return $values;
    }

    /**
     * Event.
     *
     * @param  string  $type
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

        $from = \preg_split('/ as /i', $this->getExpressionValue($this->from));

        $mainAlias = (\count($from) > 1) ? last($from) . '.' : $from[0] . '.';

        foreach ($eventColumns as $column => $value) {
            if (\is_numeric($column)) {
                $column = $value;
            }

            $alias = $mainAlias;
            if (\strpos($column, '.') !== false) {
                [$alias, $column] = \explode('.', $column);

                $alias .= '.';
            }

            switch ($column) {
                case 'user_id':
                    $user_id = user('id');
                    if ($user_id) {
                        $this->where($alias . $column, $user_id);
                    }

                    break;
                default:
                    if ($value && Context::has($value)) {
                        $this->where($alias . $column, Context::get($value));
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
        $table = $this->getExpressionValue($this->from);

        $from = \preg_split('/ as /i', $table);

        return $from[0] ?? $table;
    }
}
