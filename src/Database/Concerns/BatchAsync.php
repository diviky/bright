<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Concerns;

use Diviky\Bright\Database\BatchBuffer\RedisBatchBuffer;
use Illuminate\Contracts\Database\Query\Expression;

trait BatchAsync
{
    protected function isBatchAsyncEnabled(): bool
    {
        $async = $this->getAsync();

        return \is_array($async) && !empty($async[3]);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function pushBatchRows(string $type, array $payload): bool
    {
        $buffer = app(RedisBatchBuffer::class);
        $connectionName = $this->connection->getName();
        $table = $this->getTableBaseName();
        $async = $this->getAsync();

        return $buffer->pushRow($table, $type, $payload, $connectionName, $async);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function normalizeInsertRows(array $values): array
    {
        if (isset($values[0]) && \is_array($values[0])) {
            return $values;
        }

        return [$values];
    }

    /**
     * @return null|array{column: string, value: mixed}
     */
    protected function extractSimpleWhereIdentity(): ?array
    {
        if (\count($this->wheres) !== 1) {
            return null;
        }

        $where = $this->wheres[0];

        if (($where['type'] ?? '') !== 'Basic' || ($where['operator'] ?? '=') !== '=') {
            return null;
        }

        $column = $where['column'] ?? null;

        if ($column instanceof Expression) {
            return null;
        }

        if (!\is_string($column) || $column === '') {
            return null;
        }

        return [
            'column' => $column,
            'value' => $where['value'] ?? null,
        ];
    }

    /**
     * @return array{where_column?: string, where_value?: mixed, wheres?: array<int, mixed>, where_bindings?: array<int, mixed>}
     */
    protected function buildUpdateBatchPayload(array $values): array
    {
        $payload = ['values' => $values];
        $where = $this->extractSimpleWhereIdentity();

        if ($where !== null) {
            $payload['where_column'] = $where['column'];
            $payload['where_value'] = $where['value'];

            return $payload;
        }

        $payload['wheres'] = $this->wheres;
        $payload['where_bindings'] = $this->cleanBindings($this->getRawBindings()['where'] ?? []);

        return $payload;
    }
}
