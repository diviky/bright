<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Filters\Ql;

class Comparison implements PredicateInterface
{
    /**
     * @var Identifier|string
     */
    public $left;

    /**
     * @var Identifier|string
     */
    public $op;

    /**
     * @var Identifier|string
     */
    public $right;

    /**
     * @var string
     */
    public $leftType = self::TYPE_IDENTIFIER;

    /**
     * @var string
     */
    public $rightType = self::TYPE_VALUE;
}
