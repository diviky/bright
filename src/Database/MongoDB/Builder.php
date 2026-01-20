<?php

namespace Diviky\Bright\Database\MongoDB;

use Diviky\Bright\Database\Concerns\WithBuilder;
use MongoDB\Laravel\Query\Builder as QueryBuilder;

class Builder extends QueryBuilder
{
    use WithBuilder;

    #[\Override]
    public function compileWheres(): array
    {
        return parent::compileWheres();
    }

    protected function aliasIdForQuery(array $values): array
    {
        return $this->aliasIdForQuery($values);
    }

    #[\Override]
    public function update(array $values, array $options = [])
    {
        $values = $this->updateEvent($values);

        return parent::update($values, $options);
    }

    protected function getAliasFromTable(string $table): string
    {
        return '';
    }

    public function toSql()
    {
        return $this->toMql();
    }

    public function toRawSql()
    {
        return $this->toMql();
    }
}
