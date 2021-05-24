<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Traits;

use Illuminate\Support\Str;

trait Eventable
{
    /**
     * Event State.
     *
     * @var bool
     */
    protected $eventState   = true;

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
     * Set event state.
     *
     * @param bool $event
     */
    public function eventState($event = false): self
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
     */
    public function setEvent($event): self
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
     */
    public function eventColumn($name): self
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
     * Get the value of event.
     */
    protected function useEvent(): bool
    {
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
        $bright = $this->getBrightConfig();
        $bright = $bright['tables'];

        $from = \preg_split('/ as /i', $this->from)[0];

        if ($bright['ignore'] && isset($bright['ignore'][$from])) {
            return [];
        }

        if (\is_array($bright['default'])) {
            $tables = \array_merge($bright['default'], $bright[$type]);
        } else {
            $tables = $bright[$type];
        }

        return isset($tables[$from]) ? array_unique($tables[$from]) : [];
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
                        if (app()->has($field)) {
                            $value[$column] = app($field);
                        }

                        break;
                }

                $values[$key] = $value;
            }
        }

        $bright = $this->getBrightConfig();

        if (false !== $bright['timestamps']) {
            foreach ($values as &$value) {
                $value = $this->setTimeStamps($value);
            }
        }

        return $values;
    }

    /**
     * Update the values.
     *
     * @return array
     */
    protected function updateEvent(array $values)
    {
        $this->atomicEvent('update');

        $bright = $this->getBrightConfig();

        if (false !== $bright['timestamps']) {
            $values = $this->setTimeStamp($values);
        }

        return $values;
    }

    /**
     * Event.
     *
     * @param string $type
     */
    protected function atomicEvent($type = 'update'): self
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

    /**
     * Get the configuration.
     *
     * @return \Illuminate\Config\Repository|mixed
     */
    protected function getBrightConfig()
    {
        return config('bright');
    }
}
