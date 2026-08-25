<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Query\Grammars;

use Illuminate\Database\Query\Builder;
use MongoDB\Laravel\Query\Grammar as LarvelMongoGrammar;

class MongoDBGrammar extends LarvelMongoGrammar
{
    use CompilesComments;
    use WrapTrait;

    /**
     * MongoDB does not support SQL block comments — no-op.
     */
    public function compileQueryComments(Builder $query): string
    {
        return '';
    }
}
