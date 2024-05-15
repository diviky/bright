<?php

declare(strict_types=1);

namespace Diviky\Bright\Util;

class StdClass extends \stdClass
{
    protected array $attributes = [];

    public function __construct(array $attributes)
    {
        $this->attributes = $attributes;
    }

    public function __set(string $name, mixed $value): void
    {
        $this->attributes[$name] = $value;
    }

    public function __get($name): mixed
    {
        if (array_key_exists($name, $this->attributes)) {
            return $this->attributes[$name];
        }

        return 0;
    }

    public function toArray(): array
    {
        return $this->attributes;
    }
}
