<?php

declare(strict_types=1);

namespace Diviky\Bright\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Resource
{
    private string $name;

    private string $index;

    private ?string $type;

    public function __construct(string $name, string $index, ?string $type = null)
    {
        $this->name = $name;
        $this->index = $index;
        $this->type = $type;
    }

    public function toResource(array $response): array
    {
        $instance = $this->name;

        if ($this->type == 'collection') {
            $response[$this->index] = $instance::collection($response[$this->index])->response()->getData(true);
        } else {
            $response[$this->index] = new $instance($response[$this->index]);
        }

        return $response;
    }
}
