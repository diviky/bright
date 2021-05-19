<?php

namespace Diviky\Bright\Models;

use Diviky\Bright\Traits\Uuids;

class EmailLogs extends Model
{
    use Uuids;

    /**
     * {@inheritDoc}
     */
    public function getTable()
    {
        return config('bright.table.email_logs', 'email_logs');
    }
}
