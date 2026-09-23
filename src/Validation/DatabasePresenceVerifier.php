<?php

declare(strict_types=1);

namespace Diviky\Bright\Validation;

use Diviky\Bright\Database\DatabaseManager;
use Diviky\Bright\Database\Octane\DatabaseManager as OctaneDatabaseManager;
use Illuminate\Contracts\Database\Query\Builder;
use Illuminate\Validation\DatabasePresenceVerifier as BaseDatabasePresenceVerifier;

class DatabasePresenceVerifier extends BaseDatabasePresenceVerifier
{
    /**
     * Get a query builder for the given table using Bright connection/database routing.
     *
     * @param  string  $table
     */
    protected function table($table): Builder
    {
        $database = $this->brightDatabaseManager();

        if ($database !== null) {
            return $database->table($table)->useWritePdo();
        }

        return parent::table($table);
    }

    protected function brightDatabaseManager(): DatabaseManager|OctaneDatabaseManager|null
    {
        $database = $this->db;

        if ($database instanceof DatabaseManager || $database instanceof OctaneDatabaseManager) {
            return $database;
        }

        $resolved = app('db');

        if ($resolved instanceof DatabaseManager || $resolved instanceof OctaneDatabaseManager) {
            return $resolved;
        }

        return null;
    }
}
