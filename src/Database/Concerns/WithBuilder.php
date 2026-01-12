<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Concerns;

use Diviky\Bright\Database\Concerns\Builder as ConcernsBuilder;

trait WithBuilder
{
    use Async;
    use Build;
    use BuildsQueries;
    use Cachable;
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

        return parent::update($values);
    }
}
