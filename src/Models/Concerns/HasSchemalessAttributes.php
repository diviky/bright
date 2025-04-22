<?php

declare(strict_types=1);

namespace Diviky\Bright\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Spatie\SchemalessAttributes\Casts\SchemalessAttributes;

trait HasSchemalessAttributes
{
    public function initializeHasSchemalessAttributes(): void
    {
        $this->casts['fields'] = SchemalessAttributes::class;
    }

    public function scopeWithFields(): Builder
    {
        return $this->fields->modelScope();
    }
}
