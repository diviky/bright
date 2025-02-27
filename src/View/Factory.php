<?php

declare(strict_types=1);

namespace Diviky\Bright\View;

use Illuminate\Support\Arr;
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

    /**
     * Add a piece of shared data to the environment.
     *
     * @param  array|string  $key
     * @param  null|mixed  $value
     * @return mixed
     */
    #[\Override]
    public function share($key, $value = null)
    {
        $keys = is_array($key) ? $key : [$key => $value];

        foreach ($keys as $key => $value) {
            Arr::set($this->shared, $key, $value);
        }

        return $value;
    }
}
