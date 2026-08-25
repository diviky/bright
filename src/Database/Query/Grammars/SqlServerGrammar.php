<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Query\Grammars;

use Illuminate\Database\Query\Grammars\SqlServerGrammar as LaravelSqlServerGrammar;

class SqlServerGrammar extends LaravelSqlServerGrammar
{
    use CompilesComments;
    use WrapTrait;
}
