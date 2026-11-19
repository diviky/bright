<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\BatchBuffer;

use Illuminate\Support\Facades\DB;

class InsertBatcher
{
    public function execute(string $connection, string $table, array $decodedPayloads, int $chunkSize = 1000): int
    {
        $rows = [];

        foreach ($decodedPayloads as $payload) {
            foreach ($payload['rows'] ?? [] as $row) {
                if (is_array($row)) {
                    $rows[] = $row;
                }
            }
        }

        if ($rows === []) {
            return 0;
        }

        $rows = $this->normalizeColumnSets($rows);
        $count = 0;

        foreach (array_chunk($rows, $chunkSize) as $chunk) {
            DB::connection($connection)->table($table)->insert($chunk);
            $count += count($chunk);
        }

        return $count;
    }

    /**
     * @param  array<int, array<string, mixed>>  $rows
     * @return array<int, array<string, mixed>>
     */
    protected function normalizeColumnSets(array $rows): array
    {
        $allKeys = [];

        foreach ($rows as $row) {
            $allKeys = array_unique(array_merge($allKeys, array_keys($row)));
        }

        foreach ($rows as &$row) {
            foreach ($allKeys as $key) {
                if (!array_key_exists($key, $row)) {
                    $row[$key] = null;
                }
            }
        }
        unset($row);

        return $rows;
    }
}
