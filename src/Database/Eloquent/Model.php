<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Eloquent;

use Diviky\Bright\Database\Eloquent\Concerns\WithModel;
use Illuminate\Database\Eloquent\Model as BaseModel;

class Model extends BaseModel
{
    use WithModel;
}
