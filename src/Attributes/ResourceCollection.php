<?php

declare(strict_types=1);

namespace Diviky\Bright\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_CLASS)]
class ResourceCollection extends Resource
{
    public function __construct(string $name, ?string $index = null)
    {
        parent::__construct($name, $index, 'collection');
    }
}
