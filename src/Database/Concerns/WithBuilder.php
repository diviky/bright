<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Concerns;

use Diviky\Bright\Database\Concerns\Builder as ConcernsBuilder;

trait WithBuilder
{
    use Async;
    use BatchAsync;
    use Build;
    use BuildsQueries;
    use Cachable;
    use Comment;
    use ConcernsBuilder;
    use Config;
    use Eloquent;
    use Eventable;
    use Filter;
    use Ordering;
    use Outfile;
    use Paging;
    use Raw;
    use Remove;
    use SoftDeletes;
    use Timestamps;

    #[\Override]
    public function update(array $values)
    {
        $values = $this->updateEvent($values);

        if ($this->isBatchAsyncEnabled()) {
            return $this->pushBatchRows('update', $this->buildUpdateBatchPayload($values)) ? 1 : 0;
        }

        return parent::update($values);
    }
}
