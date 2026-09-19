<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Concerns;

use Diviky\Bright\Database\Query\Builder as QueryBuilder;
use Diviky\Bright\Database\Query\Grammars\MySqlGrammar as QueryGrammar;

trait ProvidesBrightMySqlQueryBuilder
{
    /**
     * @return QueryBuilder
     */
    #[\Override]
    public function query()
    {
        $builder = new QueryBuilder(
            $this,
            $this->getQueryGrammar(),
            $this->getPostProcessor()
        );

        $builder->setConfig($this->config['bright'] ?? []);

        return $builder;
    }

    /**
     * @return QueryGrammar
     */
    #[\Override]
    protected function getDefaultQueryGrammar()
    {
        $grammar = new QueryGrammar($this);
        $grammar->setConfig($this->config['bright'] ?? []);

        return $grammar;
    }
}
