<?php

declare(strict_types=1);

namespace Diviky\Bright\Attributes;

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_CLASS)]
class ViewPaths
{
    private array $paths;

    private bool $append = true;

    public function __construct(array $paths, bool $append = true)
    {
        $this->paths = $paths;
        $this->append = $append;
    }

    /**
     * Get the value of paths.
     */
    public function getPaths(): array
    {
        $paths = $this->paths;
        if ($this->append) {
            foreach ($paths as $key => $path) {
                $paths[$key] = $path . '/views/';
            }
        }

        return $paths;
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
