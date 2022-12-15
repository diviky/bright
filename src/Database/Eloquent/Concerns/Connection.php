<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Eloquent\Concerns;

trait Connection
{
    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct($attributes = [])
    {
        $table = $this->getTable();

        list($connection, $config) = $this->getConnectionDetails($table);

        if (isset($connection)) {
            $connection = $this->setConnection($connection)->getConnection();
            $connection->getQueryGrammar()->setConfig($config);
        }

        parent::__construct($attributes);
    }
}
