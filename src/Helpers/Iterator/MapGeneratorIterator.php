<?php

namespace Karla\Helpers\Iterator;

use Generator;
use InvalidArgumentException;
use Traversable;

/**
 * Maps values before yielding.
 */
class MapGeneratorIterator extends Generator
{
    /** @var mixed Callback */
    protected $callback;

    /**
     * @param \Traversable   $iterator Traversable iterator
     * @param array|\Closure $callback Callback used for iterating
     *
     * @throws InvalidArgumentException if the callback if not callable
     */
    public function __construct(Traversable $iterator, $callback)
    {
        parent::__construct($iterator);
        if (!\is_callable($callback)) {
            throw new InvalidArgumentException('The callback must be callable');
        }
        $this->callback = $callback;
    }

    public function current()
    {
        $callback = $this->callback;

        return $callback(parent::current());
    }
}
