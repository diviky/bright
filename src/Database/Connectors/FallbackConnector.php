<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Connectors;

trait FallbackConnector
{
    protected array $config = [];

    /**
     * Create a new PDO connection.
     *
     * @param string $dsn
     *
     * @return \PDO
     *
     * @throws \Exception
     */
    public function createConnection($dsn, array $config, array $options)
    {
        $this->config = $config;

        return parent::createConnection($dsn, $config, $options);
    }

    /**
     * Handle an exception that occurred during connect execution.
     *
     * @param string $dsn
     * @param string $username
     * @param string $password
     * @param array  $options
     *
     * @return \PDO
     *
     * @throws \Exception
     */
    protected function tryAgainIfCausedByLostConnection(\Throwable $e, $dsn, $username, $password, $options)
    {
        if ($this->causedByLostConnection($e)) {
            return $this->createPdoConnection($dsn, $username, $password, $options);
        }

        // check got multiple hosts
        if (!empty($this->config['hosts'])) {
            $this->config['hosts'] = explode(',', $this->config['hosts']);

            $config = $this->config;
            foreach ($this->config['hosts'] as $host) {
                $config['host'] = $host;

                try {
                    return $this->createPdoConnection($this->getDsn($config), $username, $password, $options);
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        throw $e;
    }
}
