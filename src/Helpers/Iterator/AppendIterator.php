<?php

declare(strict_types=1);

namespace Diviky\Bright\Helpers\Iterator;

use Iterator;

/**
 * AppendIterator that is not affected by https://bugs.php.net/bug.php?id=49104.
 */
class AppendIterator extends \AppendIterator
{
    /**
     * Works around the bug in which PHP calls rewind() and next() when appending.
     *
     * @param  \Iterator  $iterator  Iterator to append
     */
    #[\Override]
    public function append(Iterator $iterator): void
    {
        $this->getArrayIterator()->append($iterator);
    }
}
