<?php

declare(strict_types=1);

namespace Diviky\Bright\Attributes;

#[\Attribute(\Attribute::TARGET_CLASS)]
class ViewPaths
{
    private array $paths;

    public function __construct(array $paths)
    {
        $this->paths = $paths;
    }

    /**
     * Get the value of paths.
     */
    public function getPaths(): array
    {
        return $this->paths;
    }

    /**
     * Set the value of paths.
     *
     * @return self
     */
    public function setPaths(array $paths)
    {
        $this->paths = $paths;

        return $this;
    }
}
