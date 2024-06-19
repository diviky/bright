<?php

declare(strict_types=1);

namespace Diviky\Bright\Util;

class StdClass extends \stdClass
{
    protected array $attributes = [];

    protected mixed $defaultValue = 0;

    public function __construct(array $attributes = [], mixed $defaultValue = 0)
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
            return $this->attributes[$name];
        }

        return $this->defaultValue;
    }

    public function toArray(): array
    {
        return $this->attributes;
    }
}
