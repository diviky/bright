<?php

declare(strict_types=1);

namespace Diviky\Bright\Util;

use Illuminate\Support\Collection;

class StdClass extends Collection
{
    protected mixed $default = null;

    protected bool $recursive = false;

    protected float $maxDepth;

    protected int $depth = 0;

    /**
     * @param  \Illuminate\Contracts\Support\Arrayable|iterable|array|null  $items
     * @param  mixed  $default
     */
    public function __construct($items = [], $default = null)
    {
        $this->default = $default;

        parent::__construct($items);
    }

    public function __set(string $name, mixed $value): void
    {
        $this->items[$name] = $value;
    }

    public function __get($name): mixed
    {
        if (array_key_exists($name, $this->items)) {
            if ($this->recursive && is_array($this->items[$name]) && $this->depth < $this->maxDepth) {
                return (new self($this->items[$name], $this->default))->recursive($this->maxDepth, $this->depth + 1, $this->recursive);
            }

            return $this->items[$name];
        }

        return $this->default;
    }

    public function recursive(float $maxDepth = INF, int $depth = 0, bool $recursive = true): self
    {
        $this->recursive = $recursive;
        $this->depth = $depth;
        $this->maxDepth = $maxDepth;

        return $this;
    }

    /**
     * Convert the model to its string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toJson();
    }

    public function __serialize(): array
    {
        return $this->toArray();
    }
}
