<?php

namespace Karla\Database\Query\Grammars;

use Illuminate\Database\Query\Grammars\MySqlGrammar as LarvelMySqlGrammar;
use Karla\Database\Query\Grammars\WrapTrait;

class MySqlGrammar extends LarvelMySqlGrammar
{
    use WrapTrait;
}
