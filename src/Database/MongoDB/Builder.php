<?php

namespace Diviky\Bright\Database\MongoDB;

use Diviky\Bright\Database\Concerns\Async;
use Diviky\Bright\Database\Concerns\Build;
use Diviky\Bright\Database\Concerns\Builder as ConcernsBuilder;
use Diviky\Bright\Database\Concerns\BuildsQueries;
use Diviky\Bright\Database\Concerns\Cachable;
use Diviky\Bright\Database\Concerns\Config;
use Diviky\Bright\Database\Concerns\Eloquent;
use Diviky\Bright\Database\Concerns\Eventable;
use Diviky\Bright\Database\Concerns\Filter;
use Diviky\Bright\Database\Concerns\Ordering;
use Diviky\Bright\Database\Concerns\Outfile;
use Diviky\Bright\Database\Concerns\Paging;
use Diviky\Bright\Database\Concerns\Raw;
use Diviky\Bright\Database\Concerns\Remove;
use Diviky\Bright\Database\Concerns\SoftDeletes;
use Diviky\Bright\Database\Concerns\Timestamps;
use MongoDB\Laravel\Query\Builder as QueryBuilder;

class Builder extends QueryBuilder
{
    use Aggregator;
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
    public function compileWheres(): array
    {
        return parent::compileWheres();
    }

    protected function aliasIdForQuery(array $values): array
    {
        return $this->aliasIdForQuery($values);
    }

    #[\Override]
    public function update(array $values, array $options = [])
    {
        $values = $this->updateEvent($values);

        return parent::update($values, $options);
    }
}
