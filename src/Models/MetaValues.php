<?php

declare(strict_types=1);

namespace Diviky\Bright\Models;

class MetaValues extends Model
{
    #[\Override]
    public function getTable()
    {
        return 'app_meta_values';
    }
}
