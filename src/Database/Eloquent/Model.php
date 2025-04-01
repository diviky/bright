<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Eloquent;

use Diviky\Bright\Database\Concerns\Connector;
use Diviky\Bright\Database\Eloquent\Concerns\ArrayToObject;
use Diviky\Bright\Database\Eloquent\Concerns\Cachable;
use Diviky\Bright\Database\Eloquent\Concerns\Connection;
use Diviky\Bright\Database\Eloquent\Concerns\Eloquent;
use Diviky\Bright\Database\Eloquent\Concerns\HasEvents;
use Diviky\Bright\Database\Eloquent\Concerns\HasTimestamps;
use Diviky\Bright\Database\Eloquent\Concerns\Relations;
use Diviky\Bright\Models\Concerns\Eventable;
use Illuminate\Database\Eloquent\Model as BaseModel;

class Model extends BaseModel
{
    use ArrayToObject;
    use Cachable;
    use Connection;
    use Connector;
    use Eloquent;
    use Eventable;
    use HasEvents;
    use HasTimestamps;
    use Relations;
}
