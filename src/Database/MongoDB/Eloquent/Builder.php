<?php

namespace Diviky\Bright\Database\MongoDB\Eloquent;

use Diviky\Bright\Database\Concerns\BuildsQueries;
use Diviky\Bright\Database\Concerns\Paging;
use Diviky\Bright\Database\Eloquent\Concerns\Async;
use Diviky\Bright\Database\Eloquent\Concerns\Batch;
use Diviky\Bright\Database\Eloquent\Concerns\BuildsQueries as ConcernsBuildsQueries;
use Diviky\Bright\Database\Eloquent\Concerns\Eventable;
use Diviky\Bright\Database\Eloquent\Concerns\Filters;
use MongoDB\Laravel\Eloquent\Builder as BaseBuilder;

class Builder extends BaseBuilder
{
    use Async;
    use Batch;
    use BuildsQueries;
    use ConcernsBuildsQueries;
    use Eventable;
    use Filters;
    use Paging;

    public function toSql()
    {
        return $this->toMql();
    }

    public function toRawSql()
    {
        return $this->toMql();
    }
}
