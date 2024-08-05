<?php

declare(strict_types=1);

namespace Diviky\Bright\Util;

class StdClass extends \stdClass
{
    protected array $attributes = [];

    protected mixed $defaultValue = 0;

    protected bool $recursive = false;

    protected float $maxDepth;

    protected int $depth = 0;

    public function __construct(array $attributes = [], mixed $defaultValue = null)
    {
        $this->attributes = $attributes;
        $this->defaultValue = $defaultValue;
    }

    public function __set(string $name, mixed $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function __get(string $name): mixed
    {
        if (array_key_exists($name, $this->attributes)) {
            if ($this->recursive && is_array($this->attributes[$name]) && $this->depth < $this->maxDepth) {
                return (new static($this->attributes[$name], $this->defaultValue))->recursive($this->maxDepth, $this->depth + 1, $this->recursive);
            }

            return $this->attributes[$name];
        }

        return $this->defaultValue;
    }

    public function toArray(): array
    {
        return $this->attributes;
    }

    public function recursive(float $maxDepth = INF, int $depth = 0, bool $recursive = true): self
    {
        $this->recursive = $recursive;
        $this->depth = $depth;
        $this->maxDepth = $maxDepth;

        return $this;
    }
}
