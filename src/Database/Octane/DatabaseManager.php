<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Octane;

use Diviky\Bright\Database\Concerns\InteractsWithBrightDatabase;
use Laravel\Octane\Swoole\Database\DatabaseManager as OctaneDatabaseManager;

class DatabaseManager extends OctaneDatabaseManager
{
    use InteractsWithBrightDatabase;
}
