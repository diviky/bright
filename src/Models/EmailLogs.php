<?php

declare(strict_types=1);

namespace Diviky\Bright\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;

class EmailLogs extends Model
{
    use HasUuids;

    #[\Override]
    public function getTable()
    {
        return config('bright.table.email_logs', 'email_logs');
    }
}
