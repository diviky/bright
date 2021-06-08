<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Query\Grammars;

use Illuminate\Database\Query\Grammars\SQLiteGrammar as LarvelSQLiteGrammar;

class SQLiteGrammar extends LarvelSQLiteGrammar
{
    use WrapTrait;
}
