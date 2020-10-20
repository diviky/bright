<?php

namespace Diviky\Bright\Database\Query\Grammars;

use Illuminate\Database\Query\Grammars\MySqlGrammar as LarvelMySqlGrammar;

class MySqlGrammar extends LarvelMySqlGrammar
{
    use WrapTrait;
}
