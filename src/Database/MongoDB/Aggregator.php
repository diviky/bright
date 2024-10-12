<?php

namespace Diviky\Bright\Database\MongoDB;

trait Aggregator
{
    public function aggregator()
    {
        $columns = $this->columns ?? [];

        // Drop all columns if * is present, MongoDB does not work this way.
        if (in_array('*', $columns)) {
            $columns = [];
        }

        $wheres = $this->compileWheres();

        if ($this->groups || $this->aggregate) {
            $group = [];
            $unwinds = [];

            // Add grouping columns to the $group part of the aggregation pipeline.
            if ($this->groups) {
                foreach ($this->groups as $key => $column) {
                    if (!is_numeric($key)) {
                        $group['_id'][$key] = $column;
                        $column = $key;
                    } else {
                        $group['_id'][$column] = '$' . $column;
                    }

                    // When grouping, also add the $last operator to each grouped field,
                    // this mimics SQL's behaviour a bit.
                    $group[$column] = ['$last' => '$' . $column];
                }

                // Do the same for other columns that are selected.
                foreach ($columns as $column) {
                    $key = str_replace('.', '_', $column);

                    $group[$key] = ['$last' => '$' . $column];
                }
            }

            // Add aggregation functions to the $group part of the aggregation pipeline,
            // these may override previous aggregations.
            if ($this->aggregate) {
                $function = $this->aggregate['function'];

                foreach ($this->aggregate['columns'] as $column) {
                    // Add unwind if a subdocument array should be aggregated
                    // column: subarray.price => {$unwind: '$subarray'}
                    $splitColumns = explode('.*.', $column);
                    if (count($splitColumns) === 2) {
                        $unwinds[] = $splitColumns[0];
                        $column = implode('.', $splitColumns);
                    }

                    if ($function === 'count') {
                        // Translate count into sum.
                        $group[$column] = ['$sum' => 1];
                    } else {
                        $group[$column] = ['$' . $function => '$' . $column];
                    }
                }
            }

            // The _id field is mandatory when using grouping.
            if ($group && empty($group['_id'])) {
                $group['_id'] = null;
            }

            // Build the aggregation pipeline.
            $pipeline = [];
            if ($wheres) {
                $pipeline[] = ['$match' => $wheres];
            }

            // apply unwinds for subdocument array aggregation
            foreach ($unwinds as $unwind) {
                $pipeline[] = ['$unwind' => '$' . $unwind];
            }

            if ($group) {
                $pipeline[] = ['$group' => $group];
            }

            // Apply order and limit
            if ($this->orders) {
                $pipeline[] = ['$sort' => $this->orders];
            }

            if ($this->offset) {
                $pipeline[] = ['$skip' => $this->offset];
            }

            if ($this->limit) {
                $pipeline[] = ['$limit' => $this->limit];
            }

            if ($this->projections) {
                $pipeline[] = ['$project' => $this->projections];
            }

            $options = [
                'typeMap' => ['root' => 'array', 'document' => 'array'],
            ];

            // Add custom query options
            if (count($this->options)) {
                $options = array_merge($options, $this->options);
            }

            $options = $this->inheritConnectionOptions($options);

            $aggregator = new AggregationBuilder($this->collection, $this->options);

            foreach ($pipeline as $operators) {
                foreach ($operators as $operator => $value) {
                    $aggregator->addRawStage($operator, $value);
                }
            }

            return $aggregator;
        }

        return $this;
    }

    public function addAggregate(string $function, array $columns = ['*'])
    {
        $this->aggregate = [
            'function' => $function,
            'columns' => $columns,
        ];

        return $this;
    }

    /**
     * Apply the connection's session to options if it's not already specified.
     */
    private function inheritConnectionOptions(array $options = []): array
    {
        if (!isset($options['session'])) {
            $session = $this->connection->getSession();
            if ($session) {
                $options['session'] = $session;
            }
        }

        return $options;
    }
}
