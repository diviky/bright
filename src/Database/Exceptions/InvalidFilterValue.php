<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Exceptions;

class InvalidFilterValue extends \Exception
{
    /**
     * @param  string  $value
     * @return mixed
     */
    public static function make($value)
    {
        return new static("Filter value `{$value}` is invalid.");
    }
}
