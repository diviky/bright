<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Filters\Ql;

class Identifier
{
    /**
     * @var string
     */
    public $name;

    /**
     * @var string
     */
    public $field;

    /**
     * @return string
     */
    public function __toString()
    {
        return ($this->name) ? "{$this->name}.{$this->field}" : $this->field;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return $this->__toString();
    }
}
