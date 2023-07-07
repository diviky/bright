<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Eloquent\Concerns;

trait Connection
{
    public function initializeConnection(): void
    {
        $this->guessModelConnection($this->getTable());
    }

    protected function guessModelConnection(string $table): void
    {
        list($connection, $config) = $this->getConnectionDetails($table);

        if (!isset($connection)) {
            $connection = $this->getConnection()->getName();
        }

        $connection = $this->setConnection($connection)->getConnection();
        $connection->getQueryGrammar()->setConfig($config);
    }
}
