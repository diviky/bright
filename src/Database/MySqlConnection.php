<?php

declare(strict_types=1);

namespace Diviky\Bright\Database;

use Diviky\Bright\Database\Concerns\Connection;
use Diviky\Bright\Database\Concerns\ProvidesBrightMySqlQueryBuilder;
use Illuminate\Database\MySqlConnection as LaravelMySqlConnection;

class MySqlConnection extends LaravelMySqlConnection
{
    use Connection;
    use ProvidesBrightMySqlQueryBuilder;
}
