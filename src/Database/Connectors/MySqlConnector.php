<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Connectors;

use Illuminate\Database\Connectors\MySqlConnector as LaravelMySqlConnector;

class MySqlConnector extends LaravelMySqlConnector
{
    use FallbackConnector;
}
