<?php

declare(strict_types=1);

namespace Diviky\Bright\Database\Concerns;

use Carbon\Carbon;
use Diviky\Bright\Database\Filters\FilterRelation;
use Diviky\Bright\Database\Filters\FiltersScope;
use Diviky\Bright\Database\Filters\Ql\Parser;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

trait Filter
{
    /**
     * Filter types.
     *
     * @var array
     */
    protected $types = [];

    /**
     * Alias names for filters.
     *
     * @var array
     */
    protected $aliases = [];

    public function filters(array $types = [], array $aliases = []): self
    {
        $this->types = $types;
        $this->aliases = $aliases;

        return $this;
    }

    /**
     * Add Filters to database query builder.
     *
     * @param  array  $data
     * @return $this
     */
    public function filter($data = []): self
    {
        return $this->filterExact($this->cleanUpFilters($data['filter'] ?? []))
            ->filterParse($this->cleanUpFilters($data['parse'] ?? []))
            ->filterMatch($this->cleanUpFilters($data['dfilter'] ?? []), $data)
            ->filterContains($this->cleanUpFilters($data['lfilter'] ?? []))
            ->filterEndWith($this->cleanUpFilters($data['efilter'] ?? []))
            ->filterStartWith($this->cleanUpFilters($data['sfilter'] ?? []))
            ->filterDate($this->cleanUpFilters($data['date'] ?? []))
            ->filterNull($data['empty'] ?? [])
            ->filterNotNull($data['not_empty'] ?? [])
            ->filterDatetimes($this->cleanUpFilters($data['datetime'] ?? []))
            ->filterDatetimes($this->cleanUpFilters($data['timestamp'] ?? []))
            ->filterUnixTimes($this->cleanUpFilters($data['unix'] ?? []))
            ->filterUnixTimes($this->cleanUpFilters($data['unixtime'] ?? []))
            ->filterRange($this->cleanUpFilters($data['range'] ?? []))
            ->filterParse($this->cleanUpFilters($data['parser'] ?? []))
            ->filterBetween($this->cleanUpFilters($data['between'] ?? []))
            ->filterScopes($this->cleanUpFilters($data['scope'] ?? []));
    }

    /**
     * Set alias names for filters.
     *
     * @return self
     */
    public function addColumnAlias(string $column, string $alias)
    {
        $this->aliases[$column] = $alias;

        return $this;
    }

    /**
     * Set filter type.
     *
     * @return self
     */
    public function addFilterType(string $filter, string $type)
    {
        $this->types[$filter] = $type;

        return $this;
    }

    /**
     * Add where condition for filters.
     *
     * @param  string  $column
     * @param  array|string  $value
     * @param  string  $condition
     */
    protected function addWhere($column, $value, $condition = '='): self
    {
        if ($this->hasModel()) {
            if (Str::startsWith($column, ':')) {
                return $this->filterScopes([substr($column, 1) => $value]);
            }

            if (Str::contains($column, '.')) {
                return $this->filterRelations(explode('|', $column), $value, $condition);
            }
        }

        if (Str::contains($column, '|')) {
            $columns = \explode('|', $column);

            $this->whereFilter(array_map(function ($column) {
                return $this->cleanField($column);
            }, $columns), $value, $condition);
        } elseif (is_array($value) && $condition == '=') {
            $this->whereIn($this->cleanField($column), $value);
        } elseif (is_array($value) && ($condition == '<>' || $condition == '!=')) {
            $this->whereNotIn($this->cleanField($column), $value);
        } elseif (is_array($value)) {
            $this->whereIn($this->cleanField($column), $value);
        } else {
            $this->where($this->cleanField($column), $condition, $value);
        }

        return $this;
    }

    /**
     * Add where condition for filters.
     *
     * @param  string  $column
     * @param  array|string  $value
     * @param  string  $condition
     */
    protected function addNotWhere($column, $value, $condition = '='): self
    {
        if ($this->hasModel()) {
            if (Str::startsWith($column, ':')) {
                return $this->filterScopes([substr($column, 1) => $value]);
            }

            if (Str::contains($column, '.')) {
                return $this->filterRelations(explode('|', $column), $value, $condition);
            }
        }

        if (Str::contains($column, '|')) {
            $columns = \explode('|', $column);

            $this->whereFilter(array_map(function ($column) {
                return $this->cleanField($column);
            }, $columns), $value, $condition);
        } elseif (is_array($value)) {
            $this->whereNotIn($this->cleanField($column), $value);
        } else {
            $this->whereNot($this->cleanField($column), $condition, $value);
        }

        return $this;
    }

    /**
     * Clean the filters.
     *
     * @param  mixed  $filters
     * @return array
     */
    protected function cleanUpFilters($filters)
    {
        if (!isset($filters)) {
            return [];
        }

        if (!is_array($filters) || empty($filters)) {
            return [];
        }

        return array_filter($filters, function ($value) {
            return $value !== null && $value !== false && $value !== '';
        });
    }

    protected function filterExact(array $filters = []): self
    {
        foreach ($filters as $column => $value) {
            if (!empty($column) && isset($value) && $value != '') {
                $condition = '=';
                if (Str::contains($column, '~')) {
                    [$column, $condition] = explode('~', $column);
                }

                $type = $this->types[$column] ?? null;

                if (is_null($type)) {
                    $this->addWhere($column, $value, $condition);

                    continue;
                }

                if ($type == 'scope') {
                    $this->filterScopes([$column => $value]);
                } elseif ($type == 'like') {
                    $this->filterContains([$column => $value]);
                } elseif ($type == 'left') {
                    $this->filterStartWith([$column => $value]);
                } elseif ($type == 'right') {
                    $this->filterEndWith([$column => $value]);
                } elseif ($type == 'empty') {
                    $this->filterNull([$column => $value]);
                } elseif ($type == 'notempty') {
                    $this->filterNotNull([$column => $value]);
                } elseif ($type == 'between') {
                    $this->filterBetween([$column => $value]);
                } elseif ($type == 'range') {
                    $this->filterRange([$column => $value]);
                } elseif ($type == 'unixtime' || $type == 'unix') {
                    $this->filterUnixTimes([$column => $value]);
                } elseif ($type == 'datetime' || $type == 'timestamp') {
                    $this->filterDatetimes([$column => $value]);
                } elseif ($type == 'date') {
                    $this->filterDate([$column => $value]);
                } elseif ($type == 'parser') {
                    $this->filterParse([$column => $value]);
                } else {
                    $this->addWhere($column, $value, $condition);
                }
            }
        }

        return $this;
    }

    protected function filterContains(array $filters = []): self
    {
        foreach ($filters as $column => $value) {
            if (!empty($column) && isset($value) && $value != '') {
                $value = '%' . $value . '%';

                if (Str::startsWith($column, '!')) {
                    $this->addNotWhere(ltrim($column, '!'), $value, 'like');
                } else {
                    $this->addWhere($column, $value, 'like');
                }
            }
        }

        return $this;
    }

    protected function filterNull(array $filters = []): self
    {
        foreach ($filters as $column => $value) {
            $this->whereNull($this->cleanField($column));
        }

        return $this;
    }

    protected function filterNotNull(array $filters = []): self
    {
        foreach ($filters as $column => $value) {
            $this->whereNotNull($this->cleanField($column));
        }

        return $this;
    }

    protected function filterStartWith(array $filters = []): self
    {
        foreach ($filters as $column => $value) {
            if (!empty($column) && isset($value) && $value != '') {
                $value = '%' . $value;
                if (Str::startsWith($column, '!')) {
                    $this->addNotWhere(ltrim($column, '!'), $value, 'like');
                } else {
                    $this->addWhere($column, $value, 'like');
                }
            }
        }

        return $this;
    }

    protected function filterEndWith(array $filters = []): self
    {
        foreach ($filters as $column => $value) {
            if (!empty($column) && isset($value) && $value != '') {
                $value .= '%';
                if (Str::startsWith($column, '!')) {
                    $this->addNotWhere(ltrim($column, '!'), $value, 'like');
                } else {
                    $this->addWhere($column, $value, 'like');
                }
            }
        }

        return $this;
    }

    protected function filterMatch(array $filters = [], array $data = []): self
    {
        foreach ($filters as $value => $column) {
            $value = $data[$value];
            if (!empty($column) && isset($value) && $value != '') {
                if (Str::startsWith('%', $column)) {
                    $value = '%' . $value;

                    $this->addWhere(ltrim($column, '%'), $value, 'like');
                } elseif (Str::endsWith('%', $column)) {
                    $value .= '%';

                    $this->addWhere(rtrim($column, '%'), $value, 'like');
                } else {
                    $value = '%' . $value . '%';

                    $this->addWhere($column, $value, 'like');
                }
            }
        }

        return $this;
    }

    protected function filterScopes(array $scopes): self
    {
        foreach ($scopes as $scope => $values) {
            if (empty($scope)) {
                continue;
            }

            $scope = $this->aliases[$scope] ?? $scope;

            (new FiltersScope)($this->builder, $values, $scope);
        }

        return $this;
    }

    protected function filterRelations(string|array $attributes, ?string $searchTerm, string $condition = '='): self
    {
        $this->builder->where(function (Builder $query) use ($attributes, $searchTerm, $condition) {
            foreach (Arr::wrap($attributes) as $attribute) {
                $query->when(
                    Str::contains($attribute, '.'),
                    function (Builder $query) use ($attribute, $searchTerm, $condition) {
                        (new FilterRelation($condition))($query, $searchTerm, $attribute);
                    },
                    function (Builder $query) use ($attribute, $searchTerm, $condition) {
                        $query->orWhere($attribute, $condition, $searchTerm);
                    }
                );
            }
        });

        return $this;
    }

    protected function filterDate(array $dates): self
    {
        foreach ($dates as $column => $date) {
            if (empty($date)) {
                continue;
            }

            if (!\is_array($date) && is_string($date)) {
                $date = \explode(' - ', $date);
                $date = [
                    'from' => isset($date[0]) ? \trim($date[0]) : null,
                    'to' => isset($date[1]) ? \trim($date[1]) : null,
                    'condition' => isset($date[2]) ? \trim($date[2]) : null,
                ];
            }

            if (!isset($date['from'])) {
                continue;
            }

            $from = $this->toTime($date['from'], 'Y-m-d');
            $to = $this->toTime($date['to'], 'Y-m-d');
            $condition = $date['condition'] ?? '=';
            $column = $this->cleanField($column);

            if ($from && $to) {
                $this->whereDateBetween($column, [$from, $to]);
            } elseif ($from) {
                $this->whereDate($column, $condition, $from);
            }
        }

        return $this;
    }

    protected function filterDatetimes(array $datetime, bool $inverse = false): self
    {
        foreach ($datetime as $column => $date) {
            if (empty($date)) {
                continue;
            }

            if (!\is_array($date)) {
                $date = \explode(' - ', $date);
                $date = [
                    'from' => isset($date[0]) ? \trim($date[0]) : null,
                    'to' => isset($date[1]) ? \trim($date[1]) : null,
                ];
            }

            if (!isset($date['from'])) {
                continue;
            }

            $from = $date['from'];
            $to = $date['to'] ?? null;
            $to = $to ?: $from;

            $from = carbon($this->toTime($from, 'Y-m-d H:i:s', '00:00:00'));
            $to = carbon($this->toTime($to, 'Y-m-d H:i:s', '23:59:59'));

            if ($inverse) {
                $this->whereNotBetween($this->cleanField($column), [$from, $to]);
            } else {
                $this->whereBetween($this->cleanField($column), [$from, $to]);
            }
        }

        return $this;
    }

    protected function filterUnixTimes(array $unixtime, bool $inverse = false): self
    {
        foreach ($unixtime as $column => $date) {
            if (empty($date)) {
                continue;
            }

            if (!\is_array($date)) {
                $date = \explode(' - ', $date);
                $date = [
                    'from' => isset($date[0]) ? \trim($date[0]) : null,
                    'to' => isset($date[1]) ? \trim($date[1]) : null,
                ];
            }

            if (!isset($date['from'])) {
                continue;
            }

            $from = isset($date['from']) ? \trim($date['from']) : null;
            $to = isset($date['to']) ? \trim($date['to']) : null;
            $to = $to ?? $from;

            if (!\is_numeric($from)) {
                $from = $this->toTime($from, null, '00:00:00');
                $from = $from && !is_string($from) ? $from->timestamp : null;
            }

            if (!\is_numeric($to)) {
                $to = $this->toTime($to, null, '23:59:59');
                $to = $to && !is_string($to) ? $to->timestamp : null;
            }

            if ($inverse) {
                $this->whereNotBetween($this->cleanField($column), [$from, $to]);
            } else {
                $this->whereBetween($this->cleanField($column), [$from, $to]);
            }
        }

        return $this;
    }

    protected function filterRange(array $ranges, bool $inverse = false): self
    {
        return $this->filterBetween($ranges, $inverse);
    }

    protected function filterBetween(array $between, bool $inverse = false): self
    {
        foreach ($between as $column => $date) {
            if (empty($date)) {
                continue;
            }

            if (!\is_array($date)) {
                $date = \explode(' - ', $date);
                $date = [
                    'from' => isset($date[0]) ? \trim($date[0]) : null,
                    'to' => isset($date[1]) ? \trim($date[1]) : null,
                    'condition' => isset($date[2]) ? \trim($date[2]) : null,
                ];
            }

            $from = $date['from'];
            $to = $date['to'];
            $condition = $date['condition'] ?? '=';
            $column = $this->cleanField($column);

            if ($inverse) {
                if ($from && $to) {
                    $this->whereNotBetween($column, [$from, $to]);
                } elseif ($from) {
                    $this->addNotWhere($column, $from, $condition);
                }
            } else {
                if ($from && $to) {
                    $this->whereBetween($column, [$from, $to]);
                } elseif ($from) {
                    $this->addWhere($column, $from, $condition);
                }
            }
        }

        return $this;
    }

    protected function filterParse(array $filters): self
    {
        foreach ($filters as $input) {
            if (empty($input)) {
                continue;
            }

            $parseTree = (new Parser)->parse($input);

            if (isset($parseTree)) {
                $this->addParserPredicates($parseTree->getPredicates());
            }
        }

        return $this;
    }

    protected function addParserPredicates(array $predicates): self
    {
        $this->where(function ($query) use ($predicates): void {
            foreach ($predicates as $result) {
                if (is_array($result)) {
                    $this->addParserPredicates($result);
                } else {
                    $combinedBy = $result->getCombinedBy();
                    $predicate = $result->getPredicate();

                    $op = ($predicate->op == '=~') ? 'like' : $predicate->op;

                    if ($op == ':') {
                        $this->filterScopes([(string) $predicate->left => $predicate->right]);
                    } elseif (substr($op, 0, 1) == '.') {
                        $this->filterRelations([$predicate->left], $predicate->right, substr($op, 1));
                    } else {
                        if ($combinedBy === 'OR') {
                            $query->orWhere($this->cleanField((string) $predicate->left), $op, $predicate->right);
                        } else {
                            $query->where($this->cleanField((string) $predicate->left), $op, $predicate->right);
                        }
                    }
                }
            }
        });

        return $this;
    }

    /**
     * Cleanup the give column.
     *
     * @param  string  $string  Database column
     * @return string Cleaned String
     */
    protected function cleanField($string)
    {
        if (Str::contains($string, '.')) {
            [$alias, $column] = explode('.', $string, 2);
            $column = $this->aliases[$column] ?? $column;

            return $this->getExpressionValue($this->raw($alias . '.' . $this->wrap($column)));
        }

        return $this->getExpressionValue($this->raw($this->wrap($string)));
    }

    /**
     * Convert time to proper format.
     *
     * @param  null|\Illuminate\Support\Carbon|string|int  $time
     * @param  string  $format
     * @param  null|mixed  $prefix
     * @return null|\Illuminate\Support\Carbon|string
     */
    protected function toTime($time, $format = null, $prefix = null)
    {
        if (empty($time)) {
            return null;
        }

        if ($time instanceof Carbon) {
            return $time->format($format);
        }

        if (Str::contains($format, ':')) {
            $time = Str::contains($time, ':') ? $time : $time . ' ' . $prefix;
        }

        if (\strtotime($time) === false) {
            $time = now()->toDateTimeString();
        }

        return carbon(\trim($time), $format);
    }
}
