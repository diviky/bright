<?php

namespace Diviky\Bright\Database\Eloquent;

use Diviky\Bright\Database\Eloquent\Concerns\Cachable;
use Illuminate\Database\Eloquent\Model as BaseModel;

class Model extends BaseModel
{
    use Cachable;
}
