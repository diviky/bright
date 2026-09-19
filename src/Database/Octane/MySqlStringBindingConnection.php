<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Octane;

use Diviky\Bright\Database\Concerns\Connection;
use Diviky\Bright\Database\Concerns\ProvidesBrightMySqlQueryBuilder;
use Laravel\Octane\Swoole\Database\MySqlStringBindingConnection as OctaneMySqlStringBindingConnection;

class MySqlStringBindingConnection extends OctaneMySqlStringBindingConnection
{
    use Connection;
    use ProvidesBrightMySqlQueryBuilder;
}
