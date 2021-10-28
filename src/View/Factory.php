<?php

declare(strict_types=1);

namespace Diviky\Bright\View;

use Illuminate\View\Factory as BaseFactory;

class Factory extends BaseFactory
{
    /**
     * The array of active view paths.
     *
     * @var array
     */
    protected $paths;

    /**
     * Get the active view paths.
     */
    public function setDefaultPaths(): void
    {
        $this->paths = $this->finder->getPaths();
    }

    /**
     * Get the active view paths.
     */
    public function resetDefaultPaths(): void
    {
        if (!empty($this->paths)) {
            $this->finder->setPaths($this->paths);
        }
    }
}
