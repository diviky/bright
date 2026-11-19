<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\BatchBuffer;

final class BucketKey
{
    public static function make(string $prefix, string $connection, string $table, string $type): string
    {
        return sprintf('%s:%s:%s:%s', $prefix, $connection, $table, $type);
    }

    /**
     * @return null|array{prefix: string, connection: string, table: string, type: string}
     */
    public static function parse(string $key): ?array
    {
        $segments = explode(':', $key);

        if (count($segments) < 4) {
            return null;
        }

        $type = array_pop($segments);
        $table = array_pop($segments);
        $connection = array_pop($segments);
        $prefix = implode(':', $segments);

        return [
            'prefix' => $prefix,
            'connection' => (string) $connection,
            'table' => (string) $table,
            'type' => (string) $type,
        ];
    }
}
