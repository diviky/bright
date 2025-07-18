<?php

namespace Diviky\Bright\Database\MongoDB\Eloquent;

use Diviky\Bright\Database\Concerns\Connector;
use Diviky\Bright\Database\Eloquent\Concerns\ArrayToObject;
use Diviky\Bright\Database\Eloquent\Concerns\Cachable;
use Diviky\Bright\Database\Eloquent\Concerns\Connection;
use Diviky\Bright\Database\Eloquent\Concerns\Eloquent;
use Diviky\Bright\Database\Eloquent\Concerns\HasEvents;
use Diviky\Bright\Database\Eloquent\Concerns\HasTimestamps;
use Diviky\Bright\Database\Eloquent\Concerns\Relations;
use Diviky\Bright\Database\Eloquent\Concerns\Timezone;
use Diviky\Bright\Database\MongoDB\Builder as QueryBuilder;
use Diviky\Bright\Models\Concerns\Eventable;
use MongoDB\Laravel\Eloquent\Model as BaseModel;

class Model extends BaseModel
{
    use ArrayToObject;
    use Cachable;
    use Connection;
    use Connector;
    use Eloquent;
    use Eventable;
    use HasEvents;
    use HasTimestamps;
    use Relations;
    use Timezone;

    /**
     * @return \Diviky\Bright\Database\MongoDB\Eloquent\Builder
     */
    #[\Override]
    public function newEloquentBuilder($query)
    {
        return new Builder($query);
    }

    /** {@inheritdoc} */
    #[\Override]
    public function qualifyColumn($column)
    {
        return $column;
    }

    /** {@inheritdoc} */
    #[\Override]
    protected function newBaseQueryBuilder()
    {
        $connection = $this->getConnection();

        return new QueryBuilder($connection, $connection->getQueryGrammar(), $connection->getPostProcessor());
    }
}
