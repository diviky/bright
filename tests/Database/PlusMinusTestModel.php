<?php

declare(strict_types=1);

namespace Diviky\Bright\Tests\Database;

use Diviky\Bright\Database\Eloquent\Model;

class PlusMinusTestModel extends Model
{
    protected $table = 'test_plus_minus_counters';

    protected $fillable = ['value'];

    public static int $updatedEventCount = 0;

    protected static function booted(): void
    {
        static::updated(function () {
            static::$updatedEventCount++;
        });
    }
}
