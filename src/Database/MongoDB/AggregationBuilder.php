<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\MongoDB;

use Illuminate\Support\Collection as LaravelCollection;
use Illuminate\Support\LazyCollection;
use InvalidArgumentException;
use Iterator;
use MongoDB\Collection;
use MongoDB\Driver\CursorInterface;

use function array_replace;
use function collect;
use function sprintf;
use function str_starts_with;

class AggregationBuilder
{
    protected array $pipeline = [];

    public function __construct(
        private Collection $collection,
        private readonly array $options = [],
    ) {}

    /**
     * Add a stage without using the builder. Necessary if the stage is built
     * outside the builder, or it is not yet supported by the library.
     */
    public function addRawStage(string $operator, mixed $value): static
    {
        if (!str_starts_with($operator, '$')) {
            throw new InvalidArgumentException(sprintf('The stage name "%s" is invalid. It must start with a "$" sign.', $operator));
        }

        $this->pipeline[] = [$operator => $value];

        return $this;
    }

    /**
     * Execute the aggregation pipeline and return the results.
     */
    public function get(array $options = []): LaravelCollection|LazyCollection
    {
        $cursor = $this->execute($options);

        return collect($cursor->toArray());
    }

    /**
     * Execute the aggregation pipeline and return the results in a lazy collection.
     */
    public function cursor(array $options = []): LazyCollection
    {
        $cursor = $this->execute($options);

        return LazyCollection::make(function () use ($cursor) {
            foreach ($cursor as $item) {
                yield $item;
            }
        });
    }

    /**
     * Execute the aggregation pipeline and return MongoDB cursor.
     */
    protected function execute(array $options): CursorInterface&Iterator
    {
        $options = array_replace(
            ['typeMap' => ['root' => 'array', 'document' => 'array']],
            $this->options,
            $options,
        );

        return $this->collection->aggregate($this->pipeline, $options);
    }
}
