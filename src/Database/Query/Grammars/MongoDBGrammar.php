<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Query\Grammars;

use MongoDB\Laravel\Query\Grammar as LarvelMongoGrammar;

class MongoDBGrammar extends LarvelMongoGrammar
{
    use WrapTrait;
}
