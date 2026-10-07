<?php

declare(strict_types=1);

namespace Diviky\Bright\Database;

use Diviky\Bright\Database\Concerns\InteractsWithBrightDatabase;
use Illuminate\Database\ConnectionResolverInterface;
use Illuminate\Database\DatabaseManager as LaravelDatabaseManager;

class DatabaseManager extends LaravelDatabaseManager implements ConnectionResolverInterface
{
    use InteractsWithBrightDatabase;
}
