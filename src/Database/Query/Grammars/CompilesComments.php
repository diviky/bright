<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Query\Grammars;

use Illuminate\Database\Query\Builder;

trait CompilesComments
{
    public function compileSelect(Builder $query)
    {
        return $this->prependQueryComments($query, parent::compileSelect($query));
    }

    public function compileExists(Builder $query)
    {
        return $this->prependQueryComments($query, parent::compileExists($query));
    }

    public function compileInsert(Builder $query, array $values)
    {
        return $this->prependQueryComments($query, parent::compileInsert($query, $values));
    }

    public function compileInsertGetId(Builder $query, $values, $sequence)
    {
        return $this->prependQueryComments($query, parent::compileInsertGetId($query, $values, $sequence));
    }

    public function compileInsertOrIgnore(Builder $query, array $values)
    {
        return $this->prependQueryComments($query, parent::compileInsertOrIgnore($query, $values));
    }

    public function compileUpsert(Builder $query, array $values, array $uniqueBy, array $update)
    {
        return $this->prependQueryComments($query, parent::compileUpsert($query, $values, $uniqueBy, $update));
    }

    public function compileUpdate(Builder $query, array $values)
    {
        return $this->prependQueryComments($query, parent::compileUpdate($query, $values));
    }

    public function compileDelete(Builder $query)
    {
        return $this->prependQueryComments($query, parent::compileDelete($query));
    }

    /**
     * Compile query comments into a SQL block comment prefix.
     */
    public function compileQueryComments(Builder $query): string
    {
        if (!method_exists($query, 'getQueryComments')) {
            return '';
        }

        $comments = $query->getQueryComments();

        if ($comments === []) {
            return '';
        }

        $sanitized = array_map($this->sanitizeComment(...), $comments);
        $sanitized = array_filter($sanitized, fn (string $comment) => $comment !== '');

        if ($sanitized === []) {
            return '';
        }

        return '/* ' . implode(' ', $sanitized) . ' */ ';
    }

    protected function prependQueryComments(Builder $query, string $sql): string
    {
        $comments = $this->compileQueryComments($query);

        if ($comments === '') {
            return $sql;
        }

        return $comments . $sql;
    }

    protected function sanitizeComment(string $comment): string
    {
        $comment = preg_replace('/[\x00-\x1f\x7f]/', ' ', $comment) ?? $comment;
        $comment = str_replace(['/*', '*/'], '', $comment);

        return trim($comment);
    }
}
