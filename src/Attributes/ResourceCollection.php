<?php

declare(strict_types=1);

namespace Diviky\Bright\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD)]
class ResourceCollection extends Resource
{
    public function __construct(string $name, ?string $index = null)
    {
        parent::__construct($name, $index, 'collection');
    }
}
