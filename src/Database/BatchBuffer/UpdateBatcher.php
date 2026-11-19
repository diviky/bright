<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\BatchBuffer;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;

class UpdateBatcher
{
    public function execute(string $connection, string $table, array $decodedPayloads): int
    {
        if ($decodedPayloads === []) {
            return 0;
        }

        if ($this->canBulkCaseWhen($decodedPayloads)) {
            return $this->executeCaseWhen($connection, $table, $decodedPayloads);
        }

        return $this->executeIndividual($connection, $table, $decodedPayloads);
    }

    protected function canBulkCaseWhen(array $payloads): bool
    {
        $whereColumn = null;

        foreach ($payloads as $payload) {
            if (empty($payload['where_column']) || !array_key_exists('where_value', $payload)) {
                return false;
            }

            if ($whereColumn === null) {
                $whereColumn = $payload['where_column'];
            } elseif ($whereColumn !== $payload['where_column']) {
                return false;
            }

            if (isset($payload['wheres'])) {
                return false;
            }
        }

        return $whereColumn !== null;
    }

    protected function executeCaseWhen(string $connection, string $table, array $payloads): int
    {
        $whereColumn = (string) $payloads[0]['where_column'];
        $valueColumns = [];
        $ids = [];

        foreach ($payloads as $payload) {
            $ids[] = $payload['where_value'];
            foreach ($payload['values'] ?? [] as $column => $value) {
                $valueColumns[$column][] = [
                    'id' => $payload['where_value'],
                    'value' => $value,
                ];
            }
        }

        if ($valueColumns === []) {
            return 0;
        }

        $grammar = DB::connection($connection)->getQueryGrammar();
        $wrappedTable = $grammar->wrapTable($table);
        $wrappedWhereColumn = $grammar->wrap($whereColumn);

        $setParts = [];
        $bindings = [];

        foreach ($valueColumns as $column => $cases) {
            $whenParts = [];
            foreach ($cases as $case) {
                $whenParts[] = 'WHEN ? THEN ?';
                $bindings[] = $case['id'];
                $bindings[] = $case['value'];
            }
            $setParts[] = $grammar->wrap($column) . ' = CASE ' . $wrappedWhereColumn . ' ' . implode(' ', $whenParts) . ' END';
        }

        $idPlaceholders = implode(', ', array_fill(0, count($ids), '?'));
        foreach ($ids as $id) {
            $bindings[] = $id;
        }

        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s IN (%s)',
            $wrappedTable,
            implode(', ', $setParts),
            $wrappedWhereColumn,
            $idPlaceholders
        );

        DB::connection($connection)->update($sql, $bindings);

        return count($payloads);
    }

    protected function executeIndividual(string $connection, string $table, array $payloads): int
    {
        $count = 0;

        foreach ($payloads as $payload) {
            $values = $payload['values'] ?? [];

            if ($values === []) {
                continue;
            }

            $query = DB::connection($connection)->table($table);

            if (!empty($payload['where_column']) && array_key_exists('where_value', $payload)) {
                $query->where($payload['where_column'], $payload['where_value']);
            } elseif (!empty($payload['wheres']) && is_array($payload['wheres'])) {
                $this->applyStoredWheres($query, $payload);
            } else {
                continue;
            }

            $count += $query->update($values);
        }

        return $count;
    }

    /**
     * @param  Builder  $query
     */
    protected function applyStoredWheres($query, array $payload): void
    {
        $query->wheres = $payload['wheres'];

        if (!empty($payload['where_bindings']) && is_array($payload['where_bindings'])) {
            $query->setBindings(array_values($payload['where_bindings']), 'where');
        } elseif (!empty($payload['bindings']) && is_array($payload['bindings'])) {
            $query->bindings['where'] = array_values($payload['bindings']);
        }
    }
}
