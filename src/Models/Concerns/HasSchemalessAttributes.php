<?php

declare(strict_types=1);

namespace Diviky\Bright\Models\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Spatie\SchemalessAttributes\Casts\SchemalessAttributes;

trait HasSchemalessAttributes
{
    public function initializeHasSchemalessAttributes(): void
    {
        $this->casts['meta'] = SchemalessAttributes::class;
        $this->casts['options'] = SchemalessAttributes::class;
    }

    public function scopeWithMeta(): Builder
    {
        return $this->meta->modelScope();
    }

    public function scopeWithOptions(): Builder
    {
        return $this->options->modelScope();
    }
}
