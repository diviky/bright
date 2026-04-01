<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Eloquent\Concerns;

use Diviky\Bright\Database\Concerns\Connector;
use Diviky\Bright\Database\Concerns\PlusMinusWithoutTimestamps;
use Diviky\Bright\Models\Concerns\Eventable;

trait WithModel
{
    use ArrayToObject;
    use Cachable;
    use Connection;
    use Connector;
    use Eloquent;
    use Eventable;
    use HasEvents;
    use HasTimestamps;
    use PlusMinusWithoutTimestamps;
    use Relations;
    use Timezone;
    use TimezoneStorage;
}
