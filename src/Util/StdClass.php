<?php

declare(strict_types=1);

namespace Diviky\Bright\Util;

class StdClass extends \stdClass
{
    protected array $items = [];

    protected mixed $defalut = null;

    protected bool $recursive = false;

    protected float $maxDepth;

    protected int $depth = 0;

    public function __construct(array $items = [], mixed $defalut = null)
    {
        $this->items = $items;
        $this->defalut = $defalut;
    }

    public function __set(string $name, mixed $value): void
    {
        $this->items[$name] = $value;
    }

    public function __get(string $name): mixed
    {
        if (array_key_exists($name, $this->items)) {
            if ($this->recursive && is_array($this->items[$name]) && $this->depth < $this->maxDepth) {
                return (new self($this->items[$name], $this->defalut))->recursive($this->maxDepth, $this->depth + 1, $this->recursive);
            }

            return $this->items[$name];
        }

        return $this->defalut;
    }

    public function toArray(): array
    {
        return $this->items;
    }

    public function toJson(): string
    {
        return json_encode($this->items);
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
