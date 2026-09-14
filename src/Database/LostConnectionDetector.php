<?php

declare(strict_types=1);

namespace Diviky\Bright\Database;

use Illuminate\Database\LostConnectionDetector as BaseLostConnectionDetector;
use Illuminate\Support\Str;
use Throwable;

class LostConnectionDetector extends BaseLostConnectionDetector
{
    /**
     * @return array<int, string>
     */
    public static function additionalLostConnectionMessages(): array
    {
        return [
            'Commands out of sync',
            'Cannot execute queries while other unbuffered queries are active',
        ];
    }

    #[\Override]
    public function causedByLostConnection(Throwable $e): bool
    {
        if (parent::causedByLostConnection($e)) {
            return true;
        }

        return Str::contains($e->getMessage(), static::additionalLostConnectionMessages());
    }
}
