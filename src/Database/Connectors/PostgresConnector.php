<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Connectors;

use Illuminate\Database\Connectors\PostgresConnector as LaravelPostgresConnector;

class PostgresConnector extends LaravelPostgresConnector
{
    use FallbackConnector;
}
