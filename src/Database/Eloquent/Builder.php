<?php

namespace Diviky\Bright\Database\Eloquent;

use Diviky\Bright\Database\Traits\BuildsQueries;
use Diviky\Bright\Database\Traits\Paging;
use Illuminate\Database\Eloquent\Builder as BaseBuilder;

class Builder extends BaseBuilder
{
    use BuildsQueries;
    use Paging;
}
