<?php

declare(strict_types=1);

namespace Diviky\Bright\Models;

use Diviky\Bright\Concerns\Uuids;

class EmailLogs extends Model
{
    use Uuids;

    #[\Override]
    public function getTable()
    {
        return config('bright.table.email_logs', 'email_logs');
    }
}
