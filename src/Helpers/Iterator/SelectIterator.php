<?php

namespace Karla\Helpers\Iterator;

use Iterator;

/**
 * @author Sankar <sankar.suda@gmail.com>
 */
class SelectIterator implements Iterator
{
    protected $position = 0;
    protected $totalPosition = 0;
    protected $next = null;
    protected $results = null;
    protected $builder;
    protected $page = 1;
    protected $callback;

    /** @var int Size of each chunk */
    protected $chunkSize;

    /** @var array Current chunk */
    protected $chunk;

    public function __construct($builder, $chunkSize, $callback = null)
    {
        $chunkSize = (int) $chunkSize;
        if ($chunkSize < 0) {
            throw new \InvalidArgumentException("The chunk size must be equal or greater than zero; $chunkSize given");
        }
        $this->chunkSize = $chunkSize;
        $this->builder = $builder;
        $this->callback = $callback;
    }

    protected function reset()
    {
        $this->position = 0;
        $this->totalPosition = 0;
        $this->next = null;
        $this->page = 1;
    }

    protected function query()
    {
        $rows = $this->builder
            ->forPage($this->page, $this->chunkSize)
            ->get();

        if ($rows->count() > 0) {
            $this->next = true;
        }

        if ($this->callback) {
            $rows->transform($this->callback);
        }

        $this->page += 1;
        $this->position = 0;
        $this->results = $rows->toArray();

        unset($rows);
    }

    public function rewind()
    {
        $this->reset();
        $this->query();
    }

    public function current()
    {
        return $this->results[$this->position];
    }

    public function key()
    {
        return $this->totalPosition;
    }

    public function next()
    {
        $this->position++;
        $this->totalPosition++;

        if (!isset($this->results[$this->position]) && $this->next) {
            $this->query();
        }
    }

    public function valid()
    {
        if (!is_array($this->results)) {
            return false;
        }

        return isset($this->results[$this->position]) && $this->next;
    }
}
