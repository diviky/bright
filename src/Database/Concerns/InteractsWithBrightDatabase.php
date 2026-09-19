<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Concerns;

use Diviky\Bright\Database\QueryGrammarConfigurator;
use Illuminate\Database\Connection;
use Illuminate\Database\Query\Builder;
use Illuminate\Database\Query\Expression;

trait InteractsWithBrightDatabase
{
    use Connector;

    #[\Override]
    protected function makeConnection($name)
    {
        $config = $this->configuration($name);

        if ($config['driver'] === 'mongodb') {
            return $this->factory->make($config, $name);
        }

        if (isset($this->extensions[$name])) {
            return call_user_func($this->extensions[$name], $config, $name);
        }

        if (isset($this->extensions[$driver = $config['driver']])) {
            return call_user_func($this->extensions[$driver], $config, $name);
        }

        return $this->factory->make($config, $name);
    }

    #[\Override]
    public function extend($name, callable $resolver, bool $overwrite = false)
    {
        if (! isset($this->extensions[$name]) || $overwrite) {
            $this->extensions[$name] = $resolver;
        }
    }

    /**
     * @return Builder
     */
    public function table(string|Expression $name)
    {
        if ($name instanceof Expression) {
            return parent::table($name);
        }

        return $this->getConnectionByTable($name)->table($name);
    }

    /**
     * @return Connection
     */
    protected function getConnectionByTable(string $name)
    {
        if (\stripos($name, ' as ') !== false) {
            $segments = \preg_split('/\s+as\s+/i', $name);
            $name = $segments[0];
        }

        [$connection, $config] = $this->getConnectionDetails($name);

        $connection = $this->connection($connection);
        QueryGrammarConfigurator::apply($connection, $config);

        return $connection;
    }

    /**
     * @param  string  $name
     * @return array
     */
    #[\Override]
    protected function configuration($name)
    {
        $config = parent::configuration($name);

        $config['bright'] = $this->getBrightConfig();

        return $config;
    }
}
