<?php

namespace Diviky\Bright\Collections;

use Illuminate\Support\Collection;

class ObjectCollection extends Collection
{
    protected mixed $default = null;

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

    public function __isset(string $name)
    {
        return !empty($this->items[$name]);
    }

    #[\Override]
    public function __get($name): mixed
    {
        if (array_key_exists($name, $this->items)) {
            return $this->items[$name];
        }

        return $this->default;
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
