<?php

declare(strict_types=1);

namespace Diviky\Bright\Models;

class Activation extends Model
{
    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function getTable()
    {
        return config('bright.table.activations', 'activations');
    }
}
