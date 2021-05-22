<?php

namespace Diviky\Bright\Database\Eloquent;

use Diviky\Bright\Database\Eloquent\Concerns\Cachable;
use Diviky\Bright\Database\Eloquent\Concerns\HasTimestamps;
use Illuminate\Database\Eloquent\Model as BaseModel;

class Model extends BaseModel
{
    use Cachable;
    use HasTimestamps;
}
