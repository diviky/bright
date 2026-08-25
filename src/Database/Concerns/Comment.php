<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Concerns;

trait Comment
{
    /**
     * SQL query comments to prepend when compiling SQL.
     *
     * @var array<int, string>
     */
    protected array $queryComments = [];

    /**
     * Add a comment annotation to the query for logging/debugging.
     */
    public function comment(string $comment): self
    {
        $this->queryComments[] = $comment;

        return $this;
    }

    /**
     * Get the query comments.
     *
     * @return array<int, string>
     */
    public function getQueryComments(): array
    {
        return $this->queryComments;
    }

    /**
     * Clear all query comments from the builder.
     */
    public function withoutComments(): self
    {
        $this->queryComments = [];

        return $this;
    }
}
