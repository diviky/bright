<?php

declare(strict_types=1);

namespace Diviky\Bright\Helpers\Iterator;

use Closure;
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
     * @param Traversable   $iterator Traversable iterator
     * @param array|Closure $callback Callback used for iterating
     *
     * @throws InvalidArgumentException if the callback if not callable
     */
    public function __construct(Traversable $iterator, $callback)
    {
        if (!\is_callable($callback)) {
            throw new InvalidArgumentException('The callback must be callable');
        }

        parent::__construct($iterator);
        $this->callback = $callback;
    }

    public function current()
    {
        $callback = $this->callback;

        return $callback(parent::current());
    }
}
