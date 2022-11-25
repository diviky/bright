<?php

declare(strict_types=1);

namespace Diviky\Bright\Helpers\Iterator;

use Traversable;

/**
 * Pulls out chunks from an inner iterator and yields the chunks as arrays.
 */
class ChunkedIterator extends \IteratorIterator
{
    /** @var int Size of each chunk */
    protected $chunkSize;

    /** @var array Current chunk */
    protected $chunk;

    /**
     * @param \Traversable $iterator  Traversable iterator
     * @param int          $chunkSize Size to make each chunk
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(\Traversable $iterator, $chunkSize)
    {
        if ($chunkSize < 0) {
            throw new \InvalidArgumentException("The chunk size must be equal or greater than zero; {$chunkSize} given");
        }

        parent::__construct($iterator);
        $this->chunkSize = $chunkSize;
    }

    /**
     * {@inheritDoc}
     */
    public function rewind(): void
    {
        parent::rewind();
        $this->next();
    }

    /**
     * {@inheritDoc}
     */
    public function next(): void
    {
        $this->chunk = [];
        for ($i = 0; $i < $this->chunkSize && parent::valid(); ++$i) {
            $this->chunk[] = parent::current();
            parent::next();
        }
    }

    /**
     * {@inheritDoc}
     */
    public function current()
    {
        return $this->chunk;
    }

    /**
     * {@inheritDoc}
     */
    public function valid(): bool
    {
        return (bool) $this->chunk;
    }
}
