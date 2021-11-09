<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Eloquent;

use Diviky\Bright\Database\Concerns\BuildsQueries;
use Diviky\Bright\Database\Concerns\Paging;
use Diviky\Bright\Database\Eloquent\Concerns\Filters;
use Illuminate\Database\Eloquent\Builder as BaseBuilder;

class Builder extends BaseBuilder
{
    use BuildsQueries;
    use Paging;
    use Filters;
}
