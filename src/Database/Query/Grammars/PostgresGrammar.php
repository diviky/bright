<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Query\Grammars;

use Illuminate\Database\Query\Grammars\PostgresGrammar as LaravelPostgresGrammar;

class PostgresGrammar extends LaravelPostgresGrammar
{
    use CompilesComments;
    use WrapTrait;
}
