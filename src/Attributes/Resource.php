<?php

declare(strict_types=1);

namespace Diviky\Bright\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_CLASS)]
class Resource
{
    private string $name;

    private ?string $index;

    private ?string $type;

    public function __construct(string $name, ?string $index = null, ?string $type = null)
    {
        $this->name = $name;
        $this->index = $index;
        $this->type = $type;
    }

    public function toResource(array $response): array
    {
        $instance = $this->name;

        if ($this->type == 'collection') {
            if ($this->index) {
                $response[$this->index] = $instance::collection($response[$this->index])->response()->getData(true);
            } else {
                $response = $instance::collection($response)->response()->getData(true);
            }
        } else {
            if ($this->index) {
                $response[$this->index] = new $instance($response[$this->index]);
            } else {
                $response = new $instance($response);
            }
        }

        return $response;
    }
}
